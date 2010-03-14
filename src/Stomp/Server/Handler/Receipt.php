<?php
require_once "Fluid/Stomp/Server/Handler.php";


class Fluid_Stomp_Server_Handler_Receipt
	extends Fluid_Stomp_Server_Handler {


	private function sendReceiptToClient( $receipt_id, $client ) {
		$buffer =<<<EOF
RECEIPT
receipt-id:$receipt_id

\0
EOF;
		$this->stompServer->write( $client, $buffer );
	}


	function Handle( Fluid_Stomp_Msg $msg, $client ) {
		fluid_log( "Fluid_Stomp_Server_Handler_Receipt." );
		try {
			$this->sendReceiptToClient( $msg->getHeader( "receipt-id" ), $client );
			fluid_log( "Fluid_Stomp_Server_Handler_Receipt. Called." );
		} catch( Fluid_Stomp_Msg_HeaderNotFoundException $e ) {
		}

		fluid_log( "Fluid_Stomp_Server_Handler_Receipt. Finished" );
	}


}
