<?php
require_once "FluidMq/Client.php";

class Fluid_Bus {


    private $conn;
    private $iniFile;
    public $from;
    private $replyTo;
    
    private $MqClient;


    function __construct( $app_name ) {
        $this->iniFile = parse_ini_file( "/etc/Fluid_Bus/$app_name.ini", true );
 		$this->MqClient = FluidMq_Client::get();


		$this->replyTo = "$app_name@localhost";
    }
    

    private function localSend( $to, $xml ) {
        $buffer = $xml->asXML();
        $parts = explode( "\n", $buffer, 2 );


	$msg = "<msg from='" . $this->replyTo . "'>" .
					$parts[1] .
				"</msg>";

        $this->MqClient->SendMsg( $to, $msg );


    }
    
    
    function Send( $xml ) {
        $msg_name = (string)$xml->getName();
		file_put_contents( "/tmp/log", "Fluid_Bus.Send.$msg_name\n", FILE_APPEND );

        $to = $this->iniFile['Bus'][$msg_name];


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

			$this->from = (string)$msg['from'];
			f()->connection->startTransaction();
			f()->Handle( $this, $msg );
			f()->connection->commitTransaction();
			$this->from = "";
		} catch ( NoDataFoundException $e ) {
			break;
		}


    }

    function Subscribe( $msg_name ) {

    }


    function Publish( $xml ) {
        $buffer = $xml->asXML();
        $parts = explode( "\n", $buffer, 2 );

        $msg_name = (string)$xml->getName();
        $to_string = $this->iniFile['Subscription'][$msg_name];
        $to_list = explode( ",", $to_string );

        foreach( $to_list as $to ) {
			$this->localSend( $to, $xml );
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
