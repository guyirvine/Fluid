<?php
require_once "Fluid/ICacheStore.php";


interface Fluid_ICacheStore_InMemoryLocal
		extends Fluid_ICacheStore {


	function put( $key, $value, $ttl );


}

