<?php
require_once 'Fluid/Abstract.php';


abstract class Fluid_MessageHandler
	extends Fluid_Abstract {

	private $Bus;


	function __construct( Fluid $fluid, Fluid_Bus $bus ) {
		$this->Bus = $bus;
		parent::__construct( $fluid );
	}


	function __get( $name ) {
		if ( $name == 'Bus' ) {
			return $this->Bus;
		} else {
			return parent::__get( $name );
		}
	}
}

