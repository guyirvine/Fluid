<?php
require_once 'Fluid/Abstract.php';


function fluid_ajax_exception_handler($e) {
	if ( get_class( $e ) == "StateChangeException" ) {
		header( 'x-valid: false' );
		print $e->getMessage();
	} else {
		header( 'x-valid: false' );
		print "Unexpected Error: " . $e->getMessage();
	}
}
set_exception_handler('fluid_ajax_exception_handler');


abstract class Fluid_AjaxHandler
	extends Fluid_Abstract {


	private $name;

	function Create() {
		throw new Exception();
	}

	function Update() {
		throw new Exception();
	}

	function Delete() {
		throw new Exception();
	}


	function __construct( Fluid $fluid, $name ) {
		$this->name = $name;
		parent::__construct( $fluid );
	}


	function url() {
		return strtolower( $this->name ) . ".php";
	}


	function getParamName() {
		$parts = pathinfo( $_SERVER['REQUEST_URI'] );
		$name = $parts['filename'];
		$param_name = $name . "_id";


		return $param_name;
	}


	function c( $command ) {
		$param_name = $this->getParamName();


		switch( $command ) {
			case 'CREATE':
				return ( isset( $_POST['submit'] ) && !isset( $_POST[$param_name] ) );
				break;

			case 'UPDATE':
				return ( isset( $_POST['submit'] ) && isset( $_POST[$param_name] ) );
				break;

			case 'DELETE':
				return isset( $_GET['delete'] );
				break;

			default:
				throw new Exception();
		}
	}


	function Run() {
		if ( $this->c('UPDATE' ) ) {
			$this->Update();

		} elseif ( $this->c('DELETE') ) {
			$this->Delete();

		} elseif ( $this->c('CREATE') ) {
			$this->Create();

		}


		if ( isset( $GLOBALS['testing'] ) ) {
			$GLOBALS['redirect'] = $this->url();
		} else {
			print $this->url();
			$this->connection->execute( 'COMMIT', array() );
		}
	}


}

