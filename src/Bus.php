<?php
require_once "Fluid/Fluid.php";
require_once "Fluid/Mq/Client.php";

class Fluid_Bus {


	private $conn;
	private $iniFile;
	public $from;
	public $sagaId;
	public $appName;
	private $replyTo;

	private $MqClient;


	function __construct( $app_name ) {
		$this->sagaId = null;
		$_ini_file_path = "/etc/Fluid_Bus/$app_name.ini";
		if ( is_file( $_ini_file_path ) ) {
			$this->iniFile = parse_ini_file( $_ini_file_path, true );
		} else {
			$this->iniFile = array();
		}
		
		$this->MqClient = Fluid_Mq_Client::get();


		$this->appName = $app_name;
		$this->replyTo = "$app_name@localhost";
	}


	private function localSend( $to, $xml ) {
		if ( isset( $GLOBALS['testing'] ) ) {
			$GLOBALS['Bus.Send'][] = $xml->getName();
			return;
		}

		$buffer = $xml->asXML();
		$parts = explode( "\n", $buffer, 2 );

		$saga_txt = is_null( $this->sagaId ) ? "" : " sagaId='" . $this->sagaId . "'";


		$msg = "<msg from='" . $this->replyTo . "'$saga_txt>" .
					$parts[1] .
				"</msg>";

		if ( $to === false ) {
			fluid_log( "Fluid_Bus.localSend. Attemp local receive. $msg" );
			$this->Receive( $msg );
		} else {
			fluid_log( "Fluid_Bus.localSend. $to. $msg" );
			$this->MqClient->SendMsg( $to, $msg );
		}
	}


	function Send( $xml ) {
		$msg_name = (string)$xml->getName();


		if ( isset( $this->iniFile['Bus'][$msg_name] ) ) {
			$to = $this->iniFile['Bus'][$msg_name];
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
			f()->connection->startTransaction();
			f()->Handle( $this, $msg );
			f()->connection->commitTransaction();
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
