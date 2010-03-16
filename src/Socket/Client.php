<?php
require_once 'Fluid/Socket.php';


class Fluid_Socket_Client {

	private $socket;
	private $fluidSocket;


	function connect( $hostname, $port ) {
		//$address = gethostbyname('www.example.com');
		$address = $hostname;


		$this->fluidSocket = new Fluid_Socket();
		$this->socket = $this->fluidSocket->create();
		$result = socket_connect($this->socket, $address, $port);
		if ($result === false)
			throw new Fluid_SocketException( "socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($this->socket)) );


// Set socket options.
//   rc = apr_socket_timeout_set( connection->socket, 2*APR_USEC_PER_SEC);CHECK_SUCCESS;
	}

	function disconnect() {
		$this->fluidSocket->disconnect( $this->socket );
	}

	function write($data ) {
		$this->fluidSocket->write( $this->socket, $data );
	}

	function read() {
		return $this->fluidSocket->read( $this->socket );
	}


}

