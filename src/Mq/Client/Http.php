<?php
require_once "/etc/FluidMq.php";
require_once "Fluid/Mq/Client.php";
require_once "Fluid/Stomp.php";


class Fluid_Mq_Client_Http
	implements Fluid_Mq_Client {


	function __construct() {
		$this->stomp = new Fluid_Stomp();
		if ( isset( $GLOBALS['local_stomp_port'] ) )
			$this->stomp->port = $GLOBALS['local_stomp_port'];
	}


	function Send( $destination, $msg ) {
		$parts = explode( "@", $destination );
		$queue_name = $parts[0];

		$port = isset( $GLOBALS['local_http_mq_port'] ) ? $GLOBALS['local_http_mq_port'] : 80;
		$host_name = "localhost:$port";
		$response = fluid_http_post( "http://$host_name/mq/queue/$queue_name", $msg );
	}


}
