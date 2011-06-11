<?php
require_once 'Fluid/Configuration.php';


interface Fluid_Configuration_Mq
	extends Fluid_Configuration {


	/**
		$data should at least contain,
			"Bus"=>$bus
		public function Configure( $data );
	*/


}

