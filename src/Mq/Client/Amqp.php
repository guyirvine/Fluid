<?php
require_once "Fluid/Mq/Client.php";


class Fluid_Mq_Client_Amqp
	implements Fluid_Mq_Client {


	private $channel;


	function __construct($channel) {
		$this->channel = $channel;
	}


	function Send( $destination, $msg ) {
		$_msg = new AMQPMessage($msg, array('content_type' => 'text/plain'));
		fluid_log( "Fluid_Mq_Client_Amqp.Send. Destination: $destination, msg: $msg" );
		
		$this->channel->basic_publish($_msg, $destination);

	}


	public static function fromConnection(AMQPConnection $AMQPConnection) {
		return new Fluid_Mq_Client_Amqp($AMQPConnection->channel());
	}


}
