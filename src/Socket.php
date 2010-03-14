<?php
class Fluid_SocketException extends Exception {}

class Fluid_Socket {

	function create() {
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false)
			throw new Fluid_SocketException( "socket_create() failed. Reason: " . socket_strerror(socket_last_error()) );


		return $socket;
	}


	function disconnect( $socket ) {
		if ( is_null( $socket ) )
			return;
		
		$result = socket_shutdown( $socket );
		socket_close( $socket );
		
		if ( $result === false )
			throw new Fluid_SocketException( "Exception on socket_shutdown" );

	}
	
	function write($socket,$data ) {
		$data_length = strlen( $data );
		if ( $data_length == 0 )
			return;

		while( $data_length>0 ) {
			$number_of_bytes_sent = @socket_write( $socket, $data );
			if ( $number_of_bytes_sent === false ) {
				throw new Fluid_SocketException( "Error on send. False returned" );
			} else if ( $number_of_bytes_sent == 0 ) {
				throw new Fluid_SocketException( "Error on send. 0 returned" );
			} else if ( $number_of_bytes_sent == $data_length ) {
				$data_length = 0;
			} else {
				$data = substr( $data, $number_of_bytes_sent );
				$data_length = strlen( $data );
			}
		}
//    apr_status_t rc = apr_socket_send(connection->socket, "\0\n", &size);
	}


	function read($socket) {
		// Keep reading bytes till end of frame is encountered.
		$buffer_length = 2048;
		$return_buffer = "";
		$loop = true;
		while( $loop ) {
      
			$length = 1;
			socket_clear_error($socket);
//			$result = socket_read($socket, $buffer_length );
			$read = array($socket);$write  = NULL; $except = NULL;$result = "";
//			if ( socket_select($read, $write, $except, 0, 1 ) > 0 )
			if ( socket_select($read, $write, $except, 0, 500) > 0 )
//			if ( socket_select($read, $write, $except, 1, 0) > 0 )
				$result = socket_read($socket, $buffer_length );


			switch ( true ) {
				case ( $result === false ):
					$last_error = socket_last_error();
					$last_error_string = socket_strerror( $last_error ) ;
					throw new Fluid_SocketException( "Error on read. False returned. ( $last_error ): $last_error_string" );

				case ( $result == "" ):
					//No bytes read
					$loop = false;
					break;

				default:
					//Buffer full, keep reading
					$return_buffer .= $result;
			}
		}
//		print "return_buffer: $return_buffer\n";
		return $return_buffer;
	}


}

