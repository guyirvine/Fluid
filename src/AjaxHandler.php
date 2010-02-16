<?php
require_once 'Fluid/Abstract.php';


function fluid_ajax_exception_handler($e) {
	if ( get_class( $e ) == "Fluid_StateChangeException" ) {
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
			print $this->returnValue();
			$this->connection->execute( 'COMMIT', array() );
		}
	}


}

