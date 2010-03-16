<?php
require_once "Fluid/ErrorHandler.php";
require_once "Fluid/Fns.php";
require_once "Fluid/Socket/Server.php";
require_once "Fluid/Stomp/Msg.php";
require_once "Fluid/Queue/Manager/FileBased.php";
require_once "Fluid/Stomp/Server/Handler/Send.php";
require_once "Fluid/Stomp/Server/Handler/Subscribe.php";
require_once "Fluid/Stomp/Server/Handler/Receipt.php";


class Fluid_Stomp_Server
		extends Fluid_Socket_Server {

	public $hostname;
	public $port;

	private $queueManager;
	private $handlerSend;
	private $handlerSubscribe;
	private $handlerReceipt;


	private $unhandledData;

	function __construct() {
		$this->hostname = "localhost";
		$this->port = 61613;
//		$this->port = 10001;
		$this->unhandledData = "";


		
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


	function splitOnNullCharacter( $data ) {
		$buffer = $this->unhandledData . $data;
		$token = "\0";

		$b="";$list = array();$length = strlen($buffer);
		for( $i=0;$i<$length;$i++ ) {
			if ( $buffer[$i] == $token ) {
				$list[] = $b;
				$b = "";
			} else {
				$b .= $buffer[$i];
			}

		}
//		$this->unhandledData = $b;
		$this->unhandledData = "";
		$list[] = $b;

		return $list;
	}

	function processData( $data, $client ) {

		$list = $this->splitOnNullCharacter( $data );
		foreach( $list as $buffer ) {
			if ( strlen( $buffer ) < 3 )
				continue;


			$msg = new Fluid_Stomp_Msg( $buffer );
			switch ( $msg->command ) {
				case "SEND":
					$this->handlerSend->Handle( $msg, $client );
					break;
				case "SUBSCRIBE":
					$this->handlerSubscribe->Handle( $msg, $client );
					break;

				case "CONNECT":
				case "DISCONNECT":
					break;

				default:
	//				print_r( $msg );
	//				print "\n===\n\n";
					print $msg->command . "\n";
			}
			$this->handlerReceipt->Handle( $msg, $client );
		}
	}

}


//$stompServer = new Fluid_Stomp_Server();
//$stompServer->run();
