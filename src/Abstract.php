<?php
require_once 'Fluid/Fluid.php';


abstract class Fluid_Abstract {


	private $fluid;


	function __construct( Fluid $fluid ) {
		fluid_log( "Construct: " . get_class( $this ) );
		$this->fluid = $fluid;
	}


	function __get( $name ) {
		switch( $name ) {
			case 'connection':
				return $this->fluid->connection;
			case 'user_id':
				return $this->fluid->user_id;
			case 'fluid':
				return $this->fluid;
			default:
				throw new NoDataFoundException();
		}
	}


}
