<?php
require_once "Fluid/Socket/Client.php";
class Fluid_StompException extends Exception {}


class Fluid_Stomp {

	private $socket;
	private $hostname;
	public $port;


	function __construct() {
		$this->hostname = "localhost";
		$this->port = 61613;
	}


	function connect() {
		$this->socket = new Fluid_Socket_Client();
		$this->socket->connect( $this->hostname, $this->port );
$buffer =<<<EOF
CONNECT

\0
EOF;
		$this->socket->write($buffer);

		$buffer = $this->socket->read();
	}


	function disconnect() {
		$this->socket->write("DISCONNECT\n\n");
		$this->socket->disconnect();
	}


	function subscribe( $queue_name ) {
$buffer =<<<EOF
SUBSCRIBE
destination:$queue_name

\0
EOF;
		$this->socket->write($buffer);
	}


	function _send( $queue_name, $data ) {
$buffer =<<<EOF
SEND
destination:$queue_name

$data
\0
EOF;
		$this->socket->write($buffer);
	}


	function send( $queue_name, $data ) {
		$this->_send( "$queue_name", $data );
	}


	function publish( $queue_name, $data ) {
		$this->_send( "$queue_name", $data );
	}


	function receive() {
		return $this->socket->read();
	}
}

