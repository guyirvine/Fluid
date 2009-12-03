<?php
require_once 'Fluid/Fns.php';
class StateChangeException extends Exception {};


class Fluid {


	private $connection;
	private $user_id;


	function __construct( $connection, $user_id ) {
		$this->connection = $connection;
		$this->user_id = $user_id;


	}


	function __get( $name ) {
		switch( $name ) {
			case 'connection':
				return $this->connection;
			case 'user_id':
				return $this->user_id;
			case 'loggedInUser':
				return $this->User( $this->user_id );

			default:
				throw new Exception();
		}
	}


	private function localBuild( $class_name, $data ) {
		$obj = new $class_name();
		$obj->init( $data );

		return $obj;
	}

	function Build( $class_name, $data ) {
		require_once "DomainObject/$class_name.php";

		if ( is_file( "Builder/$class_name.php" ) ) {
			require_once "Builder/$class_name.php";
			$builder_class_name = "Builder_$class_name";
			$builder = new $builder_class_name();
			$obj = $builder->build( $data );
		} else {
			$obj = $this->localBuild( $class_name, $data );
		}
		
		return $obj;
	}

	function __call( $name, $arguments ) {
		require_once "Dao/$name.php";


		$dao_class_name = "Dao_$name";
		$dao = new $dao_class_name();
		$params[] = $this->connection;
		$params[] = $this->user_id;
		if ( count( $arguments ) == 0 ) {
			$param = strtolower( $name ) . "_id";
			if ( isset( $_POST[$param] ) ) {
				$params[] = $_POST[$param];
			}
		} else {
			$params = array_merge( $params, $arguments );
		}

		$data = call_user_func_array(array($dao, "get"), $params);


		return $this->build( $name, $data );
	}


	function LookupList( $name ) {
		return $this->connection->queryForResultSet( 
				"SELECT n.id, n.name FROM " . $name . "_tbl n ORDER BY n.name ", 
				array() );
	}

	function State() {
		$params = func_get_args();
		$name = array_shift( $params );


		require_once "StateChangeHandler/$name.php";


		$handler_name = "StateChangeHandler_$name";
		$handler = new $handler_name( $this );

		try {
			$data = call_user_func_array(array($handler, "ChangeState"), $params);
		} catch ( Fluid_ConnectionException $e ) {
			throw new StateChangeException( $e->getMessage() );
		}



		return $data;
	}


	function Raise() {
		$params = func_get_args();
		$name = array_shift( $params );


		if ( is_file( "DomainEventHandler/$name.php" ) ) {
			require_once "DomainEventHandler/$name.php";

			$handler_name = "DomainEventHandler_$name";
			$handler = new $handler_name( $this );
			call_user_func_array(array($handler, "handle"), $params);
		} elseif ( is_dir( "DomainEventHandler/$name/" ) ) {
			$list = glob( "DomainEventHandler/$name/*" );
			foreach( $list as $filename ) {
				$info = pathinfo( $filename );
				require_once "DomainEventHandler/$name/" . $info['filename'] . ".php";

				$handler_name = "DomainEventHandler_$name" . "_" . $info['filename'];
				$handler = new $handler_name( $this );
				call_user_func_array(array($handler, "handle"), $params);
				

			}
		}
		

	}


	function Ajax( $name=null ) {
		if ( is_null( $name ) ) {
			$parts = pathinfo( $_SERVER['REQUEST_URI'] );
			$name = ucfirst( $parts['filename'] );
		}
		require_once "AjaxHandler/$name.php";


		$handler_name = "AjaxHandler_$name";
		$handler = new $handler_name( $this, $name );


		return $handler;
	}


}


function f( $connection=null, $user_id=null, $startTransaction=true ) {
	static $fluid=null;
	if ( is_null( $fluid ) ) {
		if ( is_null( $connection ) )
			throw new Exception();

		if ( is_null( $user_id ) )
			throw new Exception();

		
		if ( $startTransaction )
			$connection->startTransaction();


		$fluid = new Fluid( $connection, $user_id );
	}

	return $fluid;
}