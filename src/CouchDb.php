<?php

class CouchDbException extends Exception {};
class NoDocumentFoundException extends CouchDbException {};
class DocumentConflictException extends CouchDbException {};

/*
$tuple['_id'] = $id;
$tuple['payload'] = $payload;
$tuple['_rev'] = $get_tuple['body']['_rev'];
*/

class Fluid_CouchDb {

	private $host;
	private $port;
	private $database;
	private $databaseChecked;

	function __construct( $database, $host="localhost", $port="5984" ) {
		$this->host=$host;
		$this->port=$port;
		$this->database=$database;
		$this->databaseChecked = false;
		
	}


	private function _send( $method, $url, $payload = NULL) {
		$socket = @fsockopen($this->host, $this->port, $errno, $errstr);


		if (!$socket)
			throw new Exception($errno . ': ' . $errstr);


		$request = "$method $url HTTP/1.0\r\nHost: {$this->host}\r\n";


		if ($payload !== NULL) {
			$request .= 'Content-Length: ' . strlen($payload) . "\r\n\r\n";
			$request .= $payload;
		}


		$request .= "\r\n";
		fwrite($socket, $request);


		$buffer = '';


		while (!feof($socket))
			$buffer .= fgets($socket);


		list($_headers, $body) = explode("\r\n\r\n", $buffer);


		$headers = split( "\n", $_headers );
		$parts = split( " ", $headers[0] );
		$headers['code'] = $parts[1];


		return array('headers' => $headers, 'body' => $body);
	}


	private function createDb() {
		$url = "/{$this->database}";

		return $this->_send( $method, $url, $payload );
	}
	function checkDb() {
		if ( $this->databaseChecked )
			return;
		
		$url = "/{$this->database}";
		$response = $this->_send( "GET", $url );
		if ( $response['headers']['code'] == "404" ) {
			$response = $this->_send( "PUT", $url );
		}
		
		$this->databaseChecked = true;
	}
	private function send( $method, $key, $payload = NULL) {
		$this->checkDb();
		$url = "/{$this->database}/$key";

		return $this->_send( $method, $url, $payload );
	}


	function set( $key, $tuple ) {
		$response = $this->send( 'PUT', $key, json_encode($tuple) );
		if ( $response['headers']['code'] == '409' ) {
			throw new DocumentConflictException( print_r( $response, true ) );

		} elseif ( $response['headers']['code'] != '200' && $response['headers']['code'] != '201' ) {
			var_dump( $response );
			throw new Exception();
		}



		return $response;
	}


	function get( $key ) {
		$response = $this->send( 'GET', $key );
		if ( $response['headers']['code'] ==  '200' ||$response['headers']['code'] ==  '201' ) {
			$response['body'] = json_decode($response['body'], TRUE);
		} else {
			throw new NoDocumentFoundException("Document with key '$key' could not be fetched." );
		}

		return $response;
	}


	function head( $key ) {
		$response = $this->send( 'HEAD', $key);
		if (strpos($response['headers'], 'HTTP/1.0 200 OK') === 0) {
			$response['body'] = json_decode($response['body'], TRUE);
		} else {
			throw new Exception( "Object with id '$key' could not be fetched." );
		}


		return $response;
	}
    
}
