<?php
require_once "Fluid/Fluid.php";


class Fluid_Rest {


	private $path_list;
	public $base_path;


	function __construct() {
		$this->base_path = '_rest';
	}


	function processPathList( $path_list ) {
		if ( $path_list == "/" )
			return array();


		$_path_list = explode( '/', $path_list );
		if ( empty( $_path_list[0] ) ) {
			array_shift( $_path_list );
		}
		if ( empty( $_path_list[count( $_path_list )-1] ) ) {
			array_pop( $_path_list );
		}
		
		return $_path_list;
	}


	function Run() {
		$this->path_list = $this->processPathList( $_SERVER["PATH_INFO"] );


		$request_method = strtolower( $_SERVER["REQUEST_METHOD"] );
		$path = "{$this->base_path}/{$this->path_list[0]}.$request_method.php";
		if ( is_file( $path ) ) {
			require_once $path;
			return;
		}

		throw new Fluid_NoDataFoundException( "Could not find resource: $path" );
	}


}


$rest = new Fluid_Rest();
