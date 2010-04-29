<?php
require_once 'Fluid/Abstract.php';


abstract class Fluid_Cache
	extends Fluid_Abstract {


	function __get( $name ) {
		switch( $name ) {
			case 'cacheStore':
				return $this->fluid->cacheStore;
			default:
				return parent::__get( $name );
		}
	}


	function _Put( $key, $data ) {
		$this->_Expire( $key );


		foreach( $this->cacheStore as $cacheStore ) {
			$cacheStore->put( $key, $data );
		}


	}


	function _Expire( $key ) {
		foreach( $this->cacheStore as $cacheStore ) {
			if ( $cacheStore implements Fluid_ICacheStore_Persistant ) {
				foreach( $this->cacheStore->getDependencyList( $key ) as $dep_key ) {
					$this->_Expire( $dep_key );
					$cacheStore->removeDependencyList( $key );
					$cacheStore->delete( $key );
				}
			}
		}


	}


	function Put() {
		$params = func_get_args();
		$data = array_pop( $params );
		$key = call_user_func_array(array($this, "MakeKey"), $params );


		$this->_Expire( $key );


		$data = serialize( $data );
		foreach( $this->cacheStore as $cacheStore ) {
			if ( $this implements Fluid_ICacheStore_InMemoryLocal &&
					$cacheStore implements Fluid_ICacheStore_InMemoryLocal ) {
				$cacheStore->_Put( $key, $data, 60 );
			} elseif ( $this implements Fluid_ICacheStore_InMemoryDistributed &&
					$cacheStore implements Fluid_ICacheStore_InMemoryDistributed ) {
				$cacheStore->_Put( $key, $data, 60 );
			} elseif ( $this implements Fluid_ICacheStore_InMemoryPersistant &&
					$cacheStore implements Fluid_ICacheStore_Persistant ) {
				$cacheStore->_Put( $key, $data );
			}


		}
	}


	function Expire() {
		$params = func_get_args();
		$key = call_user_func_array(array($this, "MakeKey"), $params );


		$this->_Expire( $key );
	}


	function Get() {
		$params = func_get_args();
		$key = call_user_func_array(array($this, "MakeKey"), $params);

		foreach( $this->cacheStore as $cacheStore ) {
			try {
				if ( $cacheStore implements Fluid_ICacheStore_InMemoryLocal ) {
					$data = $cacheStore->get( $key );
					fluid_log( "Cache hit: ( $key ). " . get_class( $cacheStore ) );
					return $data;
				}
			} catch ( Fluid_NoDataFoundException $e ) {}
		}


		foreach( $this->cacheStore as $cacheStore ) {
			try {
				if ( $cacheStore implements Fluid_ICacheStore_InMemoryDistributed ) {
					$data = $cacheStore->get( $key );
					fluid_log( "Cache hit: ( $key ). " . get_class( $cacheStore ) );
					return $data;

				}
			} catch ( Fluid_NoDataFoundException $e ) {}
		}


		foreach( $this->cacheStore as $cacheStore ) {
			try {
				if ( $cacheStore implements Fluid_ICacheStore_Persistant ) {
					$data = $cacheStore->get( $key );
					fluid_log( "Cache hit: ( $key ). " . get_class( $cacheStore ) );
					return $data;

				}
			} catch ( Fluid_NoDataFoundException $e ) {}
		}


		$data = call_user_func_array(array($this, "Regenerate"), $params);
		$this->Put( $key, $data );


		return $data;
	}


}

