<?php

require_once 'Fluid/Bus.php';
require_once 'Fluid/Mq/Amqp.php';
require_once "Fluid/Mq/Client/Amqp.php";


class Fluid_Bus_Amqp 
	extends Fluid_Bus {


	private $AMQPConnection;
	private $channel;

	function __construct( AMQPConnection $AMQPConnection, $local_queue ) {

		$this->channel = $AMQPConnection->channel();


		$mqClient = new Fluid_Mq_Client_Amqp( $AMQPConnection->channel() );


		parent::__construct( $mqClient, $local_queue );
	}


	function processMessage($msg) {
		echo "\n--------\n";
		echo $msg->body;
		echo "\n--------\n";

		$msg->delivery_info['channel']->
		basic_ack($msg->delivery_info['delivery_tag']);

/*
		// Send a message with the string "quit" to cancel the consumer.
		if ($msg->body === 'quit') {
			$msg->delivery_info['channel']->
			basic_cancel($msg->delivery_info['consumer_tag']);
		}
*/
		$this->Receive( $msg->body );
	}

	function shutdown($ch, $conn){
	    $ch->close();
	    $conn->close();
	}

	public function Run() {
		fluid_log( "Fluid_Bus_Amqp.Run. Starting Loop" );

		// Loop as long as the channel has callbacks registered
		while(count($this->channel->callbacks)) {
			fluid_log( "Fluid_Bus_Amqp.Run. About to enter wait state" );
			$this->channel->wait();
		}

		fluid_log( "Fluid_Bus_Amqp.Run. Shutting down" );
		register_shutdown_function('shutdown', $this->channel, $this->AMQPConnection);

	}

	public function AddPubSub( $exchange, $queue_list ) {
		$this->channel->exchange_declare($exchange, 'fanout', false, true, false);
		foreach( $queue_list as $queue_name ) {
			/*
			    name: $queue
			    passive: false
			    durable: true // the queue will survive server restarts
			    exclusive: false // the queue can be accessed in other channels
			    auto_delete: false //the queue won't be deleted once the channel is closed.
			*/
			$this->channel->queue_declare($queue_name, false, true, false, false);
			$this->channel->queue_bind($queue_name, $exchange);
		}

		return $this;
	}

	public function Listen( $queue_list ) {
		if ( !is_array( $queue_list ) )
			$queue_list = array( $queue_list );
		/*
		    queue: Queue from where to get the messages
		    consumer_tag: Consumer identifier
		    no_local: Don't receive messages published by this consumer.
		    no_ack: Tells the server if the consumer will acknowledge the messages.
		    exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
		    nowait:
		    callback: A PHP Callback
		*/
		$consumer_tag = null;
		foreach( $queue_list as $queue_name ) {
			$exchange = $queue_name;
			$this->channel->exchange_declare($exchange, 'direct', false, true, false);
			$this->channel->queue_declare($queue_name, false, true, false, false);
			$this->channel->queue_bind($queue_name, $exchange);
			$this->channel->basic_consume($queue_name, $consumer_tag, false, false, false, false, array( $this, 'processMessage') );
			fluid_log( "Fluid_Bus_Amqp.Listen. Binding. $queue_name, $exchange" );
		}
		
		return $this;
	}

	public static function get($local_queue, $host='localhost', $port=5672, $user='guest', $pass='guest',$vhost='/') {

		return 
			new Fluid_Bus_Amqp( 
				new AMQPConnection($host, $port, $user, $pass, $vhost ), 
				$local_queue );
	}

	function Publish( $xml ) {
		$this->Send( $xml );
	}
}
/*
$exchange = 'pub';
$queue = 'sub1';
$consumer_tag = 'consumer1';

$ch = $conn->channel();


$ch->queue_bind($queue, $exchange);

*/
