<?php

abstract class Fluid_Stomp_Server_Handler {

	protected $queueManager;
	protected $stompServer;


	function __construct( Fluid_Queue_Manager $queueManager, Fluid_Stomp_Server $stompServer ) {
		$this->queueManager = $queueManager;
		$this->stompServer = $stompServer;
	}

	abstract function Handle( Fluid_Stomp_Msg $msg, $client );

}
