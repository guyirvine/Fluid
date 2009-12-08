<?php
require_once 'Fluid/Abstract.php';


abstract class Fluid_DomainEventHandler
	extends Fluid_Abstract {


	function Publish( $xml ) {
		$this->fluid->Publish( $xml );
	}


	function Send( $xml ) {
		$this->fluid->Send( $xml );
	}


}

