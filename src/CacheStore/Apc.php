<?php
require_once 'Fluid/CacheStore.php';

class Fluid_CacheStore_Apc
	implements Fluid_CacheStore {


	function set( $key, $value, $ttl ) {
		apc_add( $key , $value, $ttl  );
	}


	function get( $key ) {
		$response = apc_fetch( $key );

		if ( $response === false )
			throw new NoCacheItemFoundException();


		return $response;
	}


}
