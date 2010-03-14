<?php
require_once "Fluid/Stomp/Server/Handler.php";


class Fluid_Stomp_Server_Handler_Subscribe 
	extends Fluid_Stomp_Server_Handler {


	private function sendMessageToClient( $queue_name, $msg_id, $body, $client ) {
		$buffer =<<<EOF
MESSAGE
destination:$queue_name
message-id: $msg_id

$body
\0
EOF;
		$this->stompServer->write( $client, "$buffer" );
	}


	function Handle( Fluid_Stomp_Msg $msg, $client ) {
		$queue_name = $msg->destination;
		fluid_log( "Fluid_Stomp_Server_Handler_Subscribe. {$queue_name}" );

		$queuedMsg = $this->queueManager->peekAtNextMsgInQueue( $queue_name );
		while ( $queuedMsg !== false  ) {
			$msg_id = $queuedMsg['id'];
			$this->sendMessageToClient( $queue_name, $msg_id, $queuedMsg['body'], $client );
			$this->queueManager->removeMsgFromQueue( $queue_name, $queuedMsg['id'] );
			$queuedMsg = $this->queueManager->peekAtNextMsgInQueue( $queue_name );
		}

		fluid_log( "Fluid_Stomp_Server_Handler_Subscribe. {$queue_name}. Finished" );
	}


}
