<?php
require_once 'Fluid/Socket.php';


class Fluid_Socket_Server {

	private $socket;
	protected $fluidSocket;


	function connect( $hostname, $port ) {
		//$address = gethostbyname('www.example.com');
		$address = $hostname;


		$this->fluidSocket = new Fluid_Socket();
		$this->socket = $this->fluidSocket->create();
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);

		$result = socket_bind($this->socket, $address, $port);
		if ($result === false)
			throw new Fluid_SocketException( "socket_bind() failed. Reason: " . socket_strerror(socket_last_error($this->socket)) );
	}


	function disconnect() {
		$this->fluidSocket->disconnect( $this->socket );
	}
	
	function write($socket, $data ) {
		$this->fluidSocket->write( $socket, $data );
	}

	function read( $socket ) {
		return $this->fluidSocket->read( $socket );
	}





	function run() {

		// start listen for connections
		$result = socket_listen($this->socket);
		if ($result === false)
			throw new Fluid_SocketException( "socket_listen() failed. Reason: " . socket_strerror(socket_last_error($this->socket)) );


		// create a list of all the clients that will be connected to us..
		// add the listening socket to this list
		$clients = array($this->socket);


		while (true) {
			// create a copy, so $clients doesn't get modified by socket_select()
			$read = $clients;
        
			// get a list of all the clients that have data to be read from
			// if there are no clients with data, go to next iteration
			$w = NULL;$e = NULL;
			if (socket_select($read, $w, $e, 0) < 1)
				continue;
        
			// check if there is a client trying to connect
			if (in_array($this->socket, $read)) {
				// accept the client, and add him to the $clients array
				$clients[] = $newsock = socket_accept($this->socket);

				// remove the listening socket from the clients-with-data array
				$key = array_search($newsock, $read);
				unset($read[$key]);
			}


			// loop through all the clients that have data to read from
			foreach ($read as $read_sock) {
				// read until newline or 1024 bytes
				// socket_read while show errors when the client is disconnected, so silence the error messages
//				$data = @socket_read($read_sock, 1024, PHP_NORMAL_READ);
				$data = socket_read($read_sock, 1024);


				// check if the client is disconnected
				if ($data === false) {
					// remove client for $clients array
					$key = array_search($read_sock, $clients);
					socket_close( $clients[$key] );
					unset($clients[$key]);
					echo "client disconnected.\n";
					// continue to the next client to read from, if any
					continue;
				}


				// check if there is any data after trimming off the spaces
				if (!empty($data)) {
//					$key = array_search($read_sock, $clients);

					$this->processData( $data, $read_sock );
				}
            
			} // end of reading foreach
		}

		// close the listening socket
		$this->disconnect();

	}


	function processData( $data, $read_sock ) {
		print $data;
		print "\n===\n\n";
	}
	
	
}

