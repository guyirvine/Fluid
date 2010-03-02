<?php
$GLOBALS['FluidMq'] = 1;
$GLOBALS['loggedInUser']['id'] = 0;
require_once "Fluid/Bus.php";


class Fluid_Mq_Receiver {


	CONST DONT_PROCESS_UNTIIL_TIMEOUT = 300;
	private $db;


	function __construct() {
		$this->db = getMqConnection();
	}


	function getListForQueue( $queue_name ) {
		fluid_log( "Fluid_Mq_Receiver.getListForQueue: $queue_name" );
		$current_timstamp = strftime( "%e %b %Y %H:%M:%S", time() );
		
		$sql = "SELECT i.id, i.data, i.version " .
				"FROM inbox_tbl i " .
				"WHERE i.queue_name = $1 " .
				"AND i.processed_yn = 'N' " .
				"AND COALESCE( i.dont_process_until, $2 ) <= $2 " .
				"ORDER BY i.created " .
				"";

		$list = $this->db->queryForResultSet( $sql, array( $queue_name, $current_timstamp ) );
		fluid_log( "Fluid_Mq_Receiver.getListForQueue: $queue_name. Finished" );
		
		return $list;
	}


	function startProcessingMsg( $msg_id, $version ) {
		fluid_log( "Fluid_Mq_Receiver.startProcessingMsg: $msg_id, $version" );
		$new_timstamp = strftime( "%e %b %Y %H:%M:%S", time() + 300 );


		$sql = "UPDATE inbox_tbl " .
				"SET dont_process_until = $1, " .
					"version = $2 " .
				"WHERE id = $3 " .
				"AND version = $4 " .
				"";
		
		$new_version = $version+1;
		$this->db->execute( $sql, array( $new_timstamp, $new_version, $msg_id, $version ), 1 );
		
		fluid_log( "Fluid_Mq_Receiver.startProcessingMsg: $msg_id, $version. Finished" );
		return $new_version;
	}

	function finishedProcessingMsg( $msg_id ) {
		fluid_log( "Fluid_Mq_Receiver.finishedProcessingMsg: $msg_id" );
		$timstamp = strftime( "%e %b %Y %H:%M:%S", time() );
		$sql = "UPDATE inbox_tbl " .
				"SET processed_at = $1, " .
					"processed_yn = 'Y' " .
				"WHERE id = $2 " .
				"";
		
		$this->db->execute( $sql, array( $timstamp, $msg_id ) );
		
		fluid_log( "Fluid_Mq_Receiver.finishedProcessingMsg: $msg_id. Finished" );
	}

	function ReceiveMsgs( $queue_name ) {
		$loop = true;
		while( $loop ) {
			$loop = false;
			$list = $this->getListForQueue( $queue_name );
			foreach( $list as $msg ) {
				try {
					fluid_log( "Fluid_Mq_Receiver.ReceiveMsgs: $queue_name. " . $msg['id'] . ". 1" );
					$version = $this->startProcessingMsg( $msg['id'], $msg['version'] );
					$loop = true;
					fluid_log( "Fluid_Mq_Receiver.ReceiveMsgs: $queue_name. " . $msg['id'] . ". 2" );
					Fluid_Bus::get()->Receive( $msg['data'] );
					$this->finishedProcessingMsg( $msg['id'] );
					fluid_log( "Fluid_Mq_Receiver.ReceiveMsgs: $queue_name. " . $msg['id'] . ". 3" );

			
				} catch ( Fluid_OptimisticLockException $e ) {}
			}
		}


	}


	static function get() {
		static $mq = null;

		if ( is_null( $mq ) ) {
			$mq = new Fluid_Mq_Receiver( getMqConnection() );
		}

		return $mq;
	}


	static function Receive( $queue="" ) {
		if ( $queue == "" ) {
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$parts = explode( "/", $_SERVER['REQUEST_URI'] );
				$queue = $parts[1];
			} else {
				throw new Fluid_NoDataFoundException( "Queue Name not specified" );
			}
		}
		
		Fluid_Mq_Receiver::get()->ReceiveMsgs( $queue );
	}

}
