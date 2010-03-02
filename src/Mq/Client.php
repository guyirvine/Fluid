<?php
require_once "/etc/FluidMq.php";


class Fluid_Mq_Client {


	private $db;


	function __construct() {
		$this->db = getMqConnection();
	}


	function SendMsg( $destination, $msg ) {
		$parts = explode( "@", $destination );
		$queue_name = $parts[0];
		try {
			$queue_id = $this->db->queryForValue( "SELECT q.id FROM queue_tbl q WHERE q.name = $1", array( $queue_name ) );

		} catch( Fluid_NoDataFoundException $e ) {
			$queue_id = $this->db->getNewId( 'queue_seq' );
			$this->db->execute( "INSERT INTO queue_tbl( id, name ) VALUES ( $1, $2 ) ", array( $queue_id, $queue_name ) );
		}


		$sql = "INSERT INTO inbox_tbl( id, queue_id, queue_name, data, created ) " .
			    "VALUES ( NEXTVAL( 'inbox_seq' ), $1, $2, $3, $4 ) ";
		$params = array( $queue_id, $queue_name, $msg, strftime( "%e %b %Y %H:%M", time() ) );

		$this->db->execute( $sql, $params );

		return $queue_id;
	}


	static function get() {
		static $mq = null;

		if ( is_null( $mq ) ) {
			$mq = new Fluid_Mq_Client( getMqConnection() );
		}

		return $mq;
	}


	static function Send( $destination, $msg ) {
		Fluid_Mq_Client::get()->SendMsg( $destination, $msg );
	}

}
