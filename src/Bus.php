<?php
require_once "Fluid/Fluid.php";


abstract class Fluid_Bus {

	private $saga_id;

	private $mqClient;
	public $local_queue;
	private $message_queue_map;

	public $reply_to;

	function __construct( Fluid_Mq_Client $mqClient, $local_queue ) {
		$this->sagaId = null;

		$this->mqClient = $mqClient;
		$this->local_queue = $local_queue;
		$this->reply_to = $local_queue;
		
		$this->message_queue_map = array(); 

		if ( is_file( "Configuration/Bus.php" ) ) {
			require_once "Configuration/Bus.php";
			$configuration = new Configuration_Bus();
			$configuration->Configure( array( "Bus"=>$this ) );
		}
		
		fluid_log( "Fluid_Bus.__construct. Adding listener to queue: " . $this->local_queue );
		$this->Listen( array( $this->local_queue ) );

	}

	public function addMsgMap( $msgName, $queue ) {
		$this->message_queue_map[$msgName] = $queue;
		
		return $this;
	}

	abstract public function AddPubSub( $exchange, $queue_list );
	abstract public function Listen( $queue_list );
	abstract public function Run( );

	private function localSend( $to, $xml ) {
		if ( isset( $GLOBALS['testing'] ) ) {
			$GLOBALS['Bus.Send'][] = $xml->getName();
			return;
		}

		$buffer = $xml->asXML();
		$parts = explode( "\n", $buffer, 2 );

		$saga_txt = is_null( $this->sagaId ) ? "" : " sagaId='" . $this->sagaId . "'";


		$msg = "<msg from='" . $this->reply_to . "'$saga_txt>" .
					$parts[1] .
				"</msg>";

		if ( $to === false ) {
			fluid_log( "Fluid_Bus.localSend. Attemp local receive. $msg" );
			$this->Receive( $msg );
		} else {
			fluid_log( "Fluid_Bus.localSend. $to. $msg" );
			$this->mqClient->Send( $to, $msg );
		}
	}


	function Send( $xml ) {
		$msg_name = (string)$xml->getName();


		if ( isset( $this->message_queue_map[$msg_name] ) ) {
			$to = $this->message_queue_map[$msg_name];
			fluid_log( "Fluid_Bus.Send.$to: $msg_name" );
		} else {
			$to = false;
			fluid_log( "Fluid_Bus.Send.Attempt local receive: $msg_name" );
		}
		$this->localSend( $to, $xml );

	}

    
	function Reply( $xml ) {
		if ( $this->from == "" )
			trigger_error( "From not set for Reply" );

		$msg_name = (string)$xml->getName();
		$to = $this->from;

		$this->localSend( $to, $xml );
	}


	function Receive( $buffer ) {

		try {
			$mqMsg = simplexml_load_string( $buffer );
			$list = $mqMsg->children();
			$msg = $list[0];

			$this->from = (string)$mqMsg['from'];
			$this->sagaId = isset( $mqMsg['sagaId'] ) ? $mqMsg['sagaId'] : null;
			fluid_log( "Fluid_Bus.Receive. from: " . $this->from . ". $buffer" );
//			f()->connection->startTransaction();
			f()->Handle( $this, $msg );
//			f()->connection->commitTransaction();
			$this->from = "";
			$this->sagaId = null;
		} catch ( Fluid_NoDataFoundException $e ) {
			break;
		}


	}

	function Subscribe( $msg_name ) {

	}

	function Publish( $xml ) {
		$buffer = $xml->asXML();
		$parts = explode( "\n", $buffer, 2 );

		$msg_name = (string)$xml->getName();
		if ( isset( $this->iniFile['Subscription'][$msg_name] ) ) {
			$to_string = $this->iniFile['Subscription'][$msg_name];
			$to_list = explode( ",", $to_string );

			foreach( $to_list as $to ) {
				$this->localSend( $to, $xml );
			}
		} else {
			$this->localSend( false, $xml );
		}

	}

	static function get( $app_name="" ) {
		static $name = null;


		if ( $app_name == "" ) {
			if ( $name == "" ) {
				$parts = explode( "/", $_SERVER['REQUEST_URI'] );
				$name = $parts[1];
			}
			$app_name = $name;
		} else {
			$name = $app_name;
		}

		$bus = new Fluid_Bus( $app_name );

		return $bus;
	}

}
