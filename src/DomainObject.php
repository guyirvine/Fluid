<?php

abstract class Fluid_DomainObject {

	protected $data;

	function init( $data ) {
		$this->data = $data;
	}

	function __get( $name ) {
		switch( $name ) {
			case 'id':
				return $this->data['id'];


			default:
				$description = "Attribute: $name, not found for DomainObject: " . get_class( $this ) . ".";
				if ( $name == "connection" ) {
					$description .= " For connection, try f()->connection.";
				}
				fluid_log( $description );
				throw new Exception( $description );
		}
	}

}
