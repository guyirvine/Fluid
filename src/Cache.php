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


	protected function Put( $key, $data ) {
		$this->Expire( $key );

		$this->cacheStore->put( $key, $data );
	}


	function _Expire( $key ) {
		foreach( $this->cacheStore->getDependencyList( $key ) as $dep_key ) {
			$this->_Expire( $dep_key );
			$this->cacheStore->removeDependencyList( $key );
			$this->cacheStore->delete( $key );
		}
		
	}

	function Expire() {
		$params = func_get_args();
		$key = call_user_func_array(array($this, "MakeKey"), $params );
		$this->_Expire( $key );
	}


	function Get() {
		try {
			$params = func_get_args();
			$key = call_user_func_array(array($this, "MakeKey"), $params);


			$_data = $this->cacheStore->get( $key );
			return  $_data;

		} catch ( Fluid_NoDataFoundException $e ) {
			$data = call_user_func_array(array($this, "Regenerate"), $params);
			$this->Put( $key, $data );
		}


		return $data;
	}


}

