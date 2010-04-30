<?php
require_once 'Fluid/ICacheStore/InMemoryLocal.php';


class Fluid_CacheStore_Apc
		implements Fluid_ICacheStore_InMemoryLocal {


	function delete( $key ) {
		apc_delete( $key );
	}


	function put( $key, $value, $ttl ) {
		apc_add( $key , $value, $ttl  );
	}


	function get( $key ) {
		$response = apc_fetch( $key );

		if ( $response === false )
			throw new Fluid_NoDataFoundException();


		return $response;
	}


}
