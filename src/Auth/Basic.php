<?php
	function basic_auth_denied() {
		header('WWW-Authenticate: Basic realm="guyirvine.com"');
		header('HTTP/1.0 401 Unauthorized');
		echo 'Text to send if user hits Cancel button\n';
		exit;
	}

	//Initialise the objects we'll need
	if ( !isset( $GLOBALS['FluidMq'] ) &&
			!isset($GLOBALS['testing'])) {
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			basic_auth_denied();
		} elseif ( !isset($_SERVER['PHP_AUTH_PW'])) {
			basic_auth_denied();
		} else {
			$user = $_SERVER['PHP_AUTH_USER'];
			$pass = $_SERVER['PHP_AUTH_PW'];

			try {
				check_password( $connection, $user, $pass );

			} catch ( Exception $e ) {
				basic_auth_denied();
			}
		}
	}
