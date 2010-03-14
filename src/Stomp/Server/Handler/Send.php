<?php
require_once "Fluid/Stomp/Server/Handler.php";


class Fluid_Stomp_Server_Handler_Send 
	extends Fluid_Stomp_Server_Handler {

	function Handle( Fluid_Stomp_Msg $msg, $client ) {
		$this->queueManager->addMsgToQueue( $msg->destination, $msg->body );
	}

}
