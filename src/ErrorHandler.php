<?php


function fluid_error_handler($errno, $errstr, $errfile, $errline) {
	if ( strpos( $errstr, 'Indirect modification of overloaded' ) !== false ) {
		//Version 5.2.1 correction
		return;
	}
	if ( $errline == 116 && strpos( $errfile, 'Db/Pgsql.php' ) > 0 )
		return;


	if ( isset( $GLOBALS['testing'] ) ) {
		debug_print_backtrace();
		die( "\n\n$errfile($errline). $errstr\n\n" );
	}


	header('HTTP/1.1 500 Internal Server Error');
	syslog( LOG_DEBUG, "fluid_error_handler. $errfile($errline). $errstr" );
	print "<h1>fluid_error_handler. $errfile($errline). $errstr</h1><br>";
	print "<pre>\n";
	if ( isset( $GLOBALS['debug_print_backtrace'] ) ) {
		debug_print_backtrace();
	}
	print "</pre>\n";
	die();


}


$old_error_handler = set_error_handler( 'fluid_error_handler' );
