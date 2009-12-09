<?php
require_once 'Fluid/MessageHandler.php';


abstract class Fluid_Saga
	extends Fluid_MessageHandler {


	private $data;


	function __construct( Fluid $fluid, Fluid_Bus $bus, $data ) {
		$this->data = $data;
		parent::__construct( $fluid, $bus );
	}


	function getData() {
		return $this->data;
	}

	function __get( $name ) {
		if ( $name == "complete" ) {
			return $this->data["_complete"];
		} elseif ( isset( $this->data[$name] ) ) {
			return $this->data[$name];
		}

		return parent::__get( $name );
	}
	function __set( $name, $value ) {
		$this->data[$name] = $value;
	}


	function Complete() {
		$this->complete = true;
	}
	function isComplete() {
		return $this->complete;
	}


	function Handle( $msg_name, $xml ) {
		if ( !method_exists( $this, $msg_name ) ) {
			throw new Exception();
		}

		$this->$msg_name( $xml );
	}


}

