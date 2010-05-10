<?php
require_once 'Fluid/Fns.php';
require_once 'Fluid/Bus.php';
class Fluid_BusinessException extends Exception {};
class Fluid_StateChangeException extends Fluid_BusinessException {};
class Fluid_NoDataFoundException extends Exception {};


class Fluid {


	private $connection;
	private $user_id;
	private $startTransaction;
	private $cacheStore;


	private $testingModeYn;
	private $test_log;
	public $deliverStateChanges;
	public $deliverDomainEvents;


	private $cache_fetched_objects;
	private $fetched_object_list;


	private $cache_list;


	public $pathDomainObject;
	public $pathBuilder;
	public $pathDao;
	public $pathFetchingStrategy;
	public $pathStateChangeHandler;
	public $pathCache;
	public $pathDomainEventHandler;
	public $pathSaga;
	public $pathMessageHandler;
	public $pathAjaxHandler;


	function __construct( $connection, $user_id, $startTransaction ) {
		$this->connection = $connection;
		$this->user_id = $user_id;
		$this->startTransaction = $startTransaction;
		if ( $startTransaction )
			$connection->startTransaction();
		$this->testingModeYn = 'N';
		$this->test_log = array();
		$this->deliverStateChanges = true;
		$this->deliverDomainEvents = true;


		$this->cache_fetched_objects=true;//This holds with a CQRS system, as we are either reading or writing, not both.
		$this->fetched_object_list=array();
		$this->cacheStore=array();
		$this->cache_list=array();


		$this->pathDomainObject = "DomainObject";
		$this->pathBuilder = "Builder";
		$this->pathFetchingStrategy = "FetchingStrategy";
		$this->pathDao = "Dao";
		$this->pathStateChangeHandler = "StateChangeHandler";
		$this->pathCache = "Cache";
		$this->pathDomainEventHandler = "DomainEventHandler";
		$this->pathSaga = "Saga";
		$this->pathMessageHandler = "MessageHandler";
		$this->pathAjaxHandler = "AjaxHandler";


	}


	function isInTestingMode() {
		return ( $this->testingModeYn == 'Y' );
	}
	function putInTestingMode() {
		$this->testingModeYn = 'Y';
		$this->test_log = array();
	}
	private function logForTest( $type, $handler_name, $params=null, $returnValue=null ) {
		if ( $this->isInTestingMode() ) {
			$this->test_log[] = array( "type"=>$type, "handler"=>$handler_name, "params"=>$params, "returnValue"=>$returnValue );
		}
	}
	function getTestLog() {
		return $this->test_log;
	}
	function turnOnLogging() {
		$GLOBALS['logging'] = 1;
	}
	function turnOffFetchedObjectCaching() {
		$this->cache_fetched_objects=false;
	}
	function turnOffDeliverys() {
		$this->deliverStateChanges = false;
		$this->deliverDomainEvents = false;
	}


	function __destruct() {
		if ( $this->startTransaction )
			$this->connection->commitTransaction();

	}


	function __get( $name ) {
		switch( $name ) {
			case 'connection':
				return $this->connection;
			case 'user_id':
				return $this->user_id;
			case 'loggedInUser':
				return $this->User( $this->user_id );
			case 'cacheStore':
				return $this->cacheStore;


			default:
				throw new Exception( "Property: $name, not found." );
		}
	}


	private function localBuild( $class_name, $data ) {
		$obj = new $class_name();
		$obj->init( $data );

		return $obj;
	}

	function Build( $class_name, $data ) {
		fluid_log( "Build: $class_name" );

		$path_from_class_name = str_replace( "_", "/", $class_name );
		require_once "{$this->pathDomainObject}/$path_from_class_name.php";

		if ( is_file( "{$this->pathBuilder}/$path_from_class_name.php" ) ) {
			require_once "{$this->pathBuilder}/$path_from_class_name.php";
			$builder_class_name = "{$this->pathBuilder}_$class_name";
			$builder = new $builder_class_name();
			$obj = $builder->build( $data );
		} else {
			$obj = $this->localBuild( $class_name, $data );
		}


		fluid_log( "Build: $class_name. Finished. " );
		return $obj;
	}


	function getData( $name, $arguments ) {
		fluid_log( "getData: $name" );
		$dao = $this->Dao( $name );

		if ( count( $arguments ) == 0 ) {
			$param = strtolower( $name ) . "_id";
			$arguments = a( p($param) );
		}
		$data = call_user_func_array(array($dao, "get"), $arguments );

		fluid_log( "getData: $name. Finished. " );
		return $data;
	}


	function Get( $name, $arguments ) {
		foreach( $arguments as $key=>$value ) {
			if ( is_numeric( $value ) ) {
				$arguments[$key]=(string)$value;
			}
		}
		$object_key =  $name . "_" . serialize( $arguments );
		fluid_log( "Get: $object_key" );

		if ( isset( $this->fetched_object_list[$object_key] ) ) {
			fluid_log( "Get: $name already fetched. Finished" );
			return $this->fetched_object_list[$object_key];
		}

		$path_from_name = str_replace( "_", "/", $name );
		if ( is_file( "{$this->pathFetchingStrategy}/$path_from_name.php" ) ) {
			require_once "{$this->pathFetchingStrategy}/$path_from_name.php";
			$fetchingstrategy_class_name = "{$this->pathFetchingStrategy}_$name";
			$fetchingStrategy = new $fetchingstrategy_class_name( $this );
			$obj = call_user_func_array(array($fetchingStrategy, "get"), $arguments);
		} else {
			$data = $this->getData( $name, $arguments );

			$obj = $this->build( $name, $data );
		}


		fluid_log( "Get: $object_key. Finished" );
		if ( $this->cache_fetched_objects )
			$this->fetched_object_list[$object_key] = $obj;

		return $obj;
	}


	function __call( $name, $arguments ) {
		return $this->Get( $name, $arguments );
	}


	function LookupList( $name ) {
		return $this->connection->queryForResultSet( 
				"SELECT n.id, n.name FROM " . $name . "_tbl n ORDER BY n.name ", 
				array() );
	}


	function State() {
		$params = func_get_args();
		$name = array_shift( $params );
		fluid_log( "State: $name" );


		require_once "{$this->pathStateChangeHandler}/$name.php";


		$handler_name = "{$this->pathStateChangeHandler}_$name";
		$handler = new $handler_name( $this );


		try {
			if ( $this->deliverStateChanges ) {
				$data = call_user_func_array(array($handler, "ChangeState"), $params);
			} else {
				$data = null;
			}
			$this->logForTest( "State", $handler_name, $params, $data );
		} catch ( Fluid_ConnectionException $e ) {
			$this->logForTest( "State", $handler_name, $params, null );
			throw new Fluid_StateChangeException( $e->getMessage() );
		}


		fluid_log( "State: $name. Finished" );
		return $data;
	}


	function Cache( $name ) {
		if ( isset( $this->cache_list[$name] ) )
			return $this->cache_list[$name];


		fluid_log( "Cache: $name" );
		require_once "{$this->pathCache}/$name.php";


		$handler_name = "{$this->pathCache}_$name";
		fluid_log( "$handler_name" );
		$handler = new $handler_name( $this );


		$this->cache_list[$name] = $handler;
		return $handler;
	}


	function Dao( $name ) {
		fluid_log( "Dao: $name" );
		require_once "{$this->pathDao}/$name.php";


		$handler_name = "{$this->pathDao}_$name";
		fluid_log( "Dao: $name. $handler_name" );
		$handler = new $handler_name( $this );
		$this->logForTest( "Dao", $handler_name, null, null );


		return $handler;
	}


	function Raise() {
		$params = func_get_args();
		$name = array_shift( $params );
		fluid_log( "Raise: $name" );


		if ( is_file( "{$this->pathDomainEventHandler}/$name.php" ) ) {
			require_once "{$this->pathDomainEventHandler}/$name.php";

			$handler_name = "{$this->pathDomainEventHandler}_$name";
			fluid_log( "$handler_name: " . print_r( $params, true ) );
			$handler = new $handler_name( $this );
			if ( $this->deliverDomainEvents )
				call_user_func_array(array($handler, "handle"), $params);
			$this->logForTest( "Raise", $handler_name, $params, null );
		} elseif ( is_dir( "{$this->pathDomainEventHandler}/$name/" ) ) {
			$list = glob( "{$this->pathDomainEventHandler}/$name/*" );
			foreach( $list as $filename ) {
				$info = pathinfo( $filename );
				require_once "{$this->pathDomainEventHandler}/$name/" . $info['filename'] . ".php";

				$handler_name = "{$this->pathDomainEventHandler}_$name" . "_" . $info['filename'];
				fluid_log( "$handler_name: " . print_r( $params, true ) );
				$handler = new $handler_name( $this );
				if ( $this->deliverDomainEvents )
					call_user_func_array(array($handler, "handle"), $params);
				$this->logForTest( "Raise", $handler_name, $params, null );
				

			}
		}


	}


	function Handle( Fluid_Bus $bus, $msg ) {
		$name = (string)$msg->getName();
		fluid_log( "Handle: $name" );


		if ( !is_null( $bus->sagaId ) && 
			is_file( "/tmp/" . $bus->appName . "-" . $bus->sagaId . ".dat" ) ) {
			fluid_log( "Handle. $name. Existing Saga: " . $bus->sagaId );
			$data = unserialize( file_get_contents( "/tmp/" . $bus->appName . "-" . $bus->sagaId . ".dat" ) );
			$handler_name = $data["_handler_name"];
			require_once $data["_file_name"];

			$handler = new $handler_name( $this, $bus, $data );
			$handler->$name( $msg );
			if ( !$handler->isComplete() ) {
				file_put_contents( "/tmp/" . $bus->appName . "-" . $bus->sagaId . ".dat", serialize( $handler->getData() ) );
			} else {
				unlink( "/tmp/" . $bus->appName . "-" . $bus->sagaId . ".dat" );
			}

		} elseif ( is_file( "{$this->pathSaga}/$name.php" ) ) {
			$file_name = "{$this->pathSaga}/$name.php";
			$handler_name = "{$this->pathSaga}_$name";
			fluid_log( "Handle. $name. Created: $handler_name" );
			require_once $file_name;

			$data = array( '_sagaId' => uniqid(),
							'_complete' => false,
							'_file_name' => $file_name,
							'_handler_name'=> $handler_name );

			$handler = new $handler_name( $this, $bus, $data );
			$bus->sagaId = $data['_sagaId'];
			$handler->Handle( $msg );
			if ( !$handler->isComplete() )
				file_put_contents( "/tmp/" . $bus->appName . "-" . $data['_sagaId'] . ".dat", serialize( $handler->getData() ) );


		} elseif ( is_file( "{$this->pathMessageHandler}/$name.php" ) ) {
			require_once "{$this->pathMessageHandler}/$name.php";

			$handler_name = "{$this->pathMessageHandler}_$name";
			fluid_log( "Handle. $handler_name" );
			$handler = new $handler_name( $this, $bus );
			$handler->Handle( $msg );
		} elseif ( is_dir( "{$this->pathMessageHandler}/$name/" ) ) {
			$list = glob( "{$this->pathMessageHandler}/$name/*" );
			foreach( $list as $filename ) {
				$info = pathinfo( $filename );
				require_once "{$this->pathMessageHandler}/$name/" . $info['filename'] . ".php";

				$handler_name = "{$this->pathMessageHandler}_$name" . "_" . $info['filename'];
				fluid_log( "Handle. $handler_name" );
				$handler = new $handler_name( $this, $bus );
				$handler->Handle( $msg );
				

			}
		}
		

	}


	function Ajax( $name=null ) {
		fluid_log( "Ajax: $name" );
		if ( is_null( $name ) ) {
			$parts = pathinfo( $_SERVER['REQUEST_URI'] );
			$name = ucfirst( $parts['filename'] );
		}
		require_once "{$this->pathAjaxHandler}/$name.php";


		$handler_name = "{$this->pathAjaxHandler}_$name";
		fluid_log( "$handler_name: " );
		$handler = new $handler_name( $this, $name );


		return $handler;
	}


	function addCacheStore( Fluid_ICacheStore $cacheStore ) {
		$this->cacheStore[] = $cacheStore;
	}


	function Publish( $xml ) {
		Fluid_Bus::get()->Publish( $xml );
	}


	function Send( $xml ) {
		Fluid_Bus::get()->Send( $xml );
	}


	function Reply( $xml ) {
		Fluid_Bus::get()->Reply( $xml );
	}
}


function f( $connection=null, $user_id=null, $startTransaction=true, $class_name="Fluid"  ) {
	static $fluid=null;
	if ( is_null( $fluid ) ) {
		if ( is_null( $connection ) )
			throw new Exception();

		if ( is_null( $user_id ) )
			throw new Exception();


		$fluid = new $class_name( $connection, $user_id, $startTransaction );
	}

	return $fluid;
}
