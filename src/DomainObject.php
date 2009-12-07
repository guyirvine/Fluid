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
				throw new Exception();
		}
	}

}
