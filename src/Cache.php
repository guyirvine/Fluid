<?php
require_once 'Fluid/Abstract.php';


//$value: contains serialized $data

abstract class Fluid_Cache
	extends Fluid_Abstract {


	private $dependency_list;


	function __construct( Fluid $fluid ) {
		$this->dependency_list = array();
		parent::__construct( $fluid );
	}


	function __get( $name ) {
		switch( $name ) {
			case 'cacheStore':
				return $this->fluid->cacheStore;
			default:
				return parent::__get( $name );
		}
	}


	function getTtl() {
		return 43200;//6 Hours
	}


	function _Put( $key, $data ) {
		$this->_Expire( $key );


		foreach( $this->cacheStore as $cacheStore ) {
			$cacheStore->put( $key, $data );
		}


	}


	function _Expire( $key ) {

		foreach( $this->cacheStore as $cacheStore ) {
			if ( $this instanceof Fluid_ICache_InMemoryLocal &&
					$cacheStore instanceof Fluid_ICacheStore_InMemoryLocal ) {
				fluid_log( "Cache _Expire: ( $key ). " . get_class( $cacheStore ) );
				$cacheStore->delete( $key );
			} elseif ( $this instanceof Fluid_ICache_InMemoryDistributed &&
					$cacheStore instanceof Fluid_ICacheStore_InMemoryDistributed ) {
				fluid_log( "Cache _Expire: ( $key ). " . get_class( $cacheStore ) );
				$cacheStore->delete( $key );
			} elseif ( $this instanceof Fluid_ICache_Persistant &&
					$cacheStore instanceof Fluid_ICacheStore_Persistant ) {

				$dependency_list = $cacheStore->getDependencyList( $key );
				$cacheStore->removeDependencyList( $key );
				foreach( $dependency_list as $dep_key ) {
					$this->_Expire( $dep_key );
				}
				fluid_log( "Cache _Expire: ( $key ). " . get_class( $cacheStore ) );
				$cacheStore->delete( $key );
			}


		}


	}


	function PutPersistant( $key, $value ) {
		if ( $this instanceof Fluid_ICache_Persistant ) {
			foreach( $this->cacheStore as $cacheStore ) {
				if ( $cacheStore instanceof Fluid_ICacheStore_Persistant ) {
					fluid_log( "Cache Put: ( $key ). " . get_class( $cacheStore ) );
					$cacheStore->put( $key, $value );


				}
			}
		}
	}


	function PutPersistantDependency( $from_key, $to_key ) {
		if ( $this instanceof Fluid_ICache_Persistant ) {
			foreach( $this->cacheStore as $cacheStore ) {
				if ( $cacheStore instanceof Fluid_ICacheStore_Persistant ) {
					fluid_log( "Cache Put Dependency: ( $from_key, $to_key ). " . get_class( $cacheStore ) );
					$cacheStore->addDependency( $from_key, $to_key );


				}
			}
		}
	}


	function PutInMemoryDistributed( $key, $value ) {
		if ( $this instanceof Fluid_ICache_InMemoryDistributed ) {
			foreach( $this->cacheStore as $cacheStore ) {
				if ( $cacheStore instanceof Fluid_ICacheStore_InMemoryDistributed ) {
					fluid_log( "Cache Put: ( $key ). " . get_class( $cacheStore ) );
					$cacheStore->put( $key, $value, $this->getTtl() );
				}
			}
		}
	}


	function PutInMemoryLocal( $key, $value ) {
		if ( $this instanceof Fluid_ICache_InMemoryLocal ) {
			foreach( $this->cacheStore as $cacheStore ) {
				if ( $cacheStore instanceof Fluid_ICacheStore_InMemoryLocal ) {
					fluid_log( "Cache Put: ( $key ). " . get_class( $cacheStore ) );
					$cacheStore->put( $key, $value, $this->getTtl() );
				}
			}
		}
	}


/*
	function Put() {
		$params = func_get_args();
		$data = array_pop( $params );
		$key = call_user_func_array(array($this, "MakeKey"), $params );
		fluid_log( "Cache Put: ( $key ). " . get_class( $this ) );


		$this->_Expire( $key );


		$value = serialize( $data );
		$this->PutPersistant( $key, $value );
		$this->PutInMemoryDistributed( $key, $value );
		$this->PutInMemoryLocal( $key, $value );
	}
*/


	function Expire() {
		$params = func_get_args();
		$key = call_user_func_array(array($this, "MakeKey"), $params );


		$this->_Expire( $key );
	}


	function GetFromPersistant( $key, $params ) {
		if ( $this instanceof Fluid_ICache_Persistant ) {
			fluid_log( "Cache GetFromPersistant: ( $key ). " );


			foreach( $this->cacheStore as $cacheStore ) {
				try {
					if ( $cacheStore instanceof Fluid_ICacheStore_Persistant ) {
						$value = $cacheStore->get( $key );
						fluid_log( "Cache Hit - Persistant: ( $key ). " . get_class( $cacheStore ) );
						if ( !empty( $value ) )
							return $value;
					}
				} catch ( Fluid_NoDataFoundException $e ) {}
			}
		}


		fluid_log( "Cache miss - Regenerate: ( $key )" );
		$data = call_user_func_array(array($this, "Regenerate"), $params);
		$value = serialize( $data );


		if ( $this instanceof Fluid_ICache_Persistant ) {
			$this->PutPersistant( $key, $value );
			foreach( $this->dependency_list as $dependency ) {
				$this->PutPersistantDependency( $dependency, $key );
			}
		}


		return $value;
	}


	function GetFromInMemoryDistributed( $key, $params ) {
		if ( $this instanceof Fluid_ICache_InMemoryDistributed ) {
			fluid_log( "Cache GetFromInMemoryDistributed: ( $key ). " );
			foreach( $this->cacheStore as $cacheStore ) {
				try {
					if ( $cacheStore instanceof Fluid_ICacheStore_InMemoryDistributed ) {
						$value = $cacheStore->get( $key );
						fluid_log( "Cache Hit - InMemoryDistributed: ( $key ). " . get_class( $cacheStore ) );
						if ( !empty( $value ) )
							return $value;
					}
				} catch ( Fluid_NoDataFoundException $e ) {}
			}
		}


		$value = $this->GetFromPersistant( $key, $params );
		if ( $this instanceof Fluid_ICache_InMemoryDistributed ) {
			$this->PutInMemoryDistributed( $key, $value );
		}


		return $value;
	}


	private function GetFromInMemoryLocal( $key, $params ) {
		if ( $this instanceof Fluid_ICache_InMemoryLocal ) {
			fluid_log( "Cache GetFromInMemoryLocal: ( $key ). " );
			foreach( $this->cacheStore as $cacheStore ) {
				try {
					if ( $cacheStore instanceof Fluid_ICacheStore_InMemoryLocal ) {
						$value = $cacheStore->get( $key );
						fluid_log( "Cache Hit - InMemoryLocal: ( $key ). " . get_class( $cacheStore ) );
						if ( !empty( $value ) )
							return $value;
					}
				} catch ( Fluid_NoDataFoundException $e ) {}
			}
		}


		$value = $this->GetFromInMemoryDistributed( $key, $params );
		if ( $this instanceof Fluid_ICache_InMemoryLocal ) {
			$this->PutInMemoryLocal( $key, $value );
		}


		return $value;
	}



	function Get() {
		$params = func_get_args();
		$key = call_user_func_array( array($this, "MakeKey"), $params );
		fluid_log( "Cache Get: ( $key ). " . get_class( $this ) );


		return unserialize( $this->GetFromInMemoryLocal( $key, $params ) );
	}


	function getDependentFromCache() {
		$params = func_get_args();
		$name = array_shift( $params );


		$cache = f()->Cache( $name );
		$key = call_user_func_array( array($cache, "MakeKey"), $params );

		$this->dependency_list[] = $key;
		return call_user_func_array( array($cache, "Get"), $params );
	}


}

