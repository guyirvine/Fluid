<?php
require_once '/etc/gcis/mq.php';
require_once 'gcis/mq/dao/Queue.php';
require_once 'gcis/mq/dao/Inbox.php';


class mq_Receiver {


	private $connection;
	private $queue_id;


	function __construct( Gcis_Db $connection, $queue_name ) {
		$this->connection = $connection;

//		$this->queue_id = Dao_Queue::get_id_by_name( $connection, $queue_name );
		try {
			$this->queue_id = Dao_Queue::get_id_by_name( $this->connection, $queue_name );
		} catch( Fluid_NoDataFoundException $e ) {
			$this->queue_id = Dao_Queue::insert( $this->connection, $queue_name );
		}
    }


	function startTransaction() {
		$this->connection->startTransaction();
	}
	function commitTransaction() {
		$this->connection->commitTransaction();
	}
	function rollbackTransaction() {
		$this->connection->rollbackTransaction();
	}


    function getNextMsg() {
		$msg = Dao_Inbox::get_next_msg( $this->connection, $this->queue_id );
		Dao_Inbox::delete( $this->connection, $msg['id'] );
		
		return $msg['data'];
    }


}
