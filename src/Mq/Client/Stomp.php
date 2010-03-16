<?php
require_once "/etc/FluidMq.php";
require_once "Fluid/Mq/Client.php";
require_once "Fluid/Stomp.php";


class Fluid_Mq_Client_Stomp
	implements Fluid_Mq_Client {


	private $stomp;


	function __construct() {
		$this->stomp = new Fluid_Stomp();
		if ( isset( $GLOBALS['local_stomp_port'] ) )
			$this->stomp->port = $GLOBALS['local_stomp_port'];
	}


	function Send( $destination, $msg ) {
		$parts = explode( "@", $destination );
		$queue_name = $parts[0];

		$this->stomp->connect();
		$this->stomp->send( $queue_name, $msg );
		$this->stomp->disconnect();
	}


}
