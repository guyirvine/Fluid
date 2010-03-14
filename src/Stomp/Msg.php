<?php
class Fluid_Stomp_Msg_HeaderNotFoundException extends Exception {};


class Fluid_Stomp_Msg {
	private $payload;
	private $command;
	private $header_list;
	private $body;

	function processHeaderSection( $headersection_payload ) {
		$part_list = explode( "\n", $headersection_payload );
		$this->command = array_shift( $part_list );
		foreach( $part_list as $part ) {
			$header_parts = explode( ":", $part );
			$this->header_list[$header_parts[0]] = $header_parts[1];
		}
	}
	
	function parse( $payload ) {
		$parts = explode( "\n\n", $payload, 2 );
		$this->processHeaderSection( $parts[0] );
		$this->body = $parts[1];
	}

	function __construct( $payload ) {
		$this->header_list = array();
		$this->payload = $payload;
		$this->parse( $payload );
	}
	

	public function getHeader( $name ) {
		if ( isset( $this->header_list[$name] ) )
			return $this->header_list[$name];

		throw new Fluid_Stomp_Msg_HeaderNotFoundException( "Header, $name, not found." );
	}

	public function __get( $name ) {
		switch ( $name ) {
			case "command":
				return $this->command;
			case "body":
				return $this->body;
			default:
				return $this->getHeader( $name );
		}
	}

}
