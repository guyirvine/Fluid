<?php
require_once "Fluid/ICacheStore.php";


interface Fluid_ICacheStore_Persistant
		extends Fluid_ICacheStore {


	function put( $key, $value );


	function getDependencyList( $from_key );


	function addDependency( $from_key, $to_key );


	function removeDependencyList( $from_key );


}

