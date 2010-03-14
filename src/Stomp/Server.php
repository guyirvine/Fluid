<?php
require_once "Fluid/ErrorHandler.php";
require_once "Fluid/Fns.php";
require_once "Fluid/Socket/Server.php";
require_once "Fluid/Stomp/Msg.php";
require_once "Fluid/Queue/Manager/FileBased.php";
require_once "Fluid/Stomp/Server/Handler/Send.php";
require_once "Fluid/Stomp/Server/Handler/Subscribe.php";
require_once "Fluid/Stomp/Server/Handler/Receipt.php";

$GLOBALS['logging'] = 1;

class Fluid_Stomp_Server
		extends Fluid_Socket_Server {

	private $hostname;
	private $port;

	private $queueManager;
	private $handlerSend;
	private $handlerSubscribe;
	private $handlerReceipt;


	function __construct() {
		$this->hostname = "localhost";
//		$this->port = 61613;
		$this->port = 10001;
		
		$queueManager = new Fluid_Queue_Manager_FileBased();

		$this->handlerSend = new Fluid_Stomp_Server_Handler_Send( $queueManager, $this );
		$this->handlerSubscribe = new Fluid_Stomp_Server_Handler_Subscribe( $queueManager, $this );
		$this->handlerReceipt = new Fluid_Stomp_Server_Handler_Receipt( $queueManager, $this );
	}


	function run() {
		$this->connect( $this->hostname, $this->port );
		parent::run();
	}


	function createDirectory( $path ) {
		if ( !is_dir( $path ) )
			mkdir( $path, 0700, true );
	}


	function processData( $data, $client ) {
		$msg = new Fluid_Stomp_Msg( $data );
		switch ( $msg->command ) {
			case "SEND":
				$this->handlerSend->Handle( $msg, $client );
				break;
			case "SUBSCRIBE":
				$this->handlerSubscribe->Handle( $msg, $client );
				break;

			default:
				print_r( $msg );
				print "\n===\n\n";
		}
		$this->handlerReceipt->Handle( $msg, $client );
	}

}


//$stompServer = new Fluid_Stomp_Server();
//$stompServer->run();
