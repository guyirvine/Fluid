<?php
require_once 'Fluid/CacheStore.php';

class Fluid_CacheStore_Memcache
	implements Fluid_CacheStore {

	private $memcache;

	function __construct( $host="localhost", $port="11211" ) {
		$this->host=$host;
		$this->port=$port;
		$this->memcache = new Memcache();
		$this->memcache->connect( $host, $port );
	}


	function delete( $key ) {
		$this->memcache->delete( $key );
	}


	function put( $key, $value ) {
		$this->memcache->set( $key, $value, 0, 60 );
	}


	function get( $key ) {
		$response = $this->memcache->get( $key );
		
		if ( $response === false )
			throw new Fluid_NoDataFoundException();


		return $response;
	}


	function getDependencyList( $from_key ) {
		$key = "$from_key.dep";

		$response = $this->memcache->get( $key );
		if ( $response === false ) {
			$response = array();
		} else {
			$response = unserialize( $response );
		}


		return $response;
	}


	function addDependency( $from_key, $to_key ) {
		$key = "$from_key.dep";

		$list = $this->getDependencyList( $from_key );
		$list[$to_key] = $to_key;
		$this->memcache->set( $key, serialize( $response ), 0, 65 );
		
	}


	function removeDependencyList( $from_key ) {
		$key = "$from_key.dep";
		$this->memcache->delete( $key );
	
	}


}
