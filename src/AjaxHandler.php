<?php
require_once 'Fluid/Abstract.php';


function fluid_ajax_exception_handler($e) {
	fluid_log( "fluid_ajax_exception_handler. 1" );
	if ( get_class( $e ) == "Fluid_StateChangeException" ) {
		fluid_log( "fluid_ajax_exception_handler. 1.1" );
		header('HTTP/1.1 200 OK');
		header( 'x-valid: false' );
//		header( 'x-message: ' . $e->getMessage() );
		print $e->getMessage();
	} elseif ( get_class( $e ) == "Fluid_BusinessException" ) {
		fluid_log( "fluid_ajax_exception_handler. 1.2. (" . headers_sent() . ") " . $e->getMessage() );
		header( 'x-valid: false' );
		print $e->getMessage();
		fluid_log( "fluid_ajax_exception_handler. 1.2.1. " . $e->getMessage() );
		die();

	} else {
		fluid_log( "fluid_ajax_exception_handler. 1.3" );
		header( 'x-valid: false' );
//		header( 'x-message: ' . $e->getMessage() );
		print "Unexpected Error: " . $e->getMessage();
	}
}
set_exception_handler('fluid_ajax_exception_handler');


abstract class Fluid_AjaxHandler
	extends Fluid_Abstract {


	private $name;

	function Create() {
		throw new Exception( "Create not handled in: " . get_class( $this ) );
	}

	function Update() {
		throw new Exception( "Update not handled in: " . get_class( $this ) );
	}

	function Delete() {
		throw new Exception( "Delete not handled in: " . get_class( $this ) );
	}


	function __construct( Fluid $fluid, $name ) {
		$this->name = $name;
		parent::__construct( $fluid );
	}


	function url() {
		return strtolower( $this->name ) . ".php";
	}


	function returnValue() {
		return $this->url();
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
			case 'DELETE':
				return isset( $_POST['delete'] );
				break;

			case 'CREATE':
				return ( isset( $_POST['submit'] ) && !isset( $_POST[$param_name] ) );
				break;

			case 'UPDATE':
				return ( isset( $_POST['submit'] ) && isset( $_POST[$param_name] ) );
				break;

			default:
				throw new Exception();
		}
	}


	function Run() {
		if ( $this->c('DELETE') ) {
			fluid_log( "AjaxHandler.Run: Delete" );
			$this->Delete();

		} elseif ( $this->c('UPDATE' ) ) {
			fluid_log( "AjaxHandler.Run: Update" );
			$this->Update();

		} elseif ( $this->c('CREATE') ) {
			fluid_log( "AjaxHandler.Run: Create" );
			$this->Create();


		} else {
			$param_name = $this->getParamName();
			$isset_submit = isset( $_POST['submit'] );
			$isset_param_name = isset( $_POST[$param_name] );
			fluid_log( "AjaxHandler.Run: Unknown. " . get_class( $this ) . ". " . 
						"param_name: $param_name. " .
						"isset( _POST['submit'] ): $isset_submit. " .
						"isset( _POST[param_name] ): $isset_param_name " .
						"" );
		}


		if ( isset( $GLOBALS['testing'] ) ) {
			$GLOBALS['redirect'] = $this->returnValue();
		} else {
			fluid_log( "AjaxHandler.Run: Finished. Return value: " . $this->returnValue() );
			print $this->returnValue();
		}
	}


}

