<?php

class CacheStoreException extends Exception {};
class NoCacheItemFoundException extends CacheStoreException {};


interface Fluid_CacheStore {


	function delete( $key );


	function put( $key, $value );


	function get( $key );


	function getDependencyList( $from_key );


	function addDependency( $from_key, $to_key );


	function removeDependencyList( $from_key );

}
