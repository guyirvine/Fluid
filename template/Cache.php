<?php
require_once 'Fluid/Cache.php';


class Cache_===Name===
	extends Fluid_Cache {


	function MakeKey( $id ) {
		return "===name===-$id";
	}

	function Regenerate( $id ) {
		$key = $this->MakeKey( $id );

		$data['id'] = $id;

		$this->Put( $key, $data );
		
		return $data;
	}



}

