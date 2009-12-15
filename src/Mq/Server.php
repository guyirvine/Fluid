<?php
require_once '/etc/gcis/mq.php';
require_once 'gcis/mq/dao/Queue.php';
require_once 'gcis/mq/dao/Outbox.php';
require_once 'gcis/mq/dao/Inbox.php';


class mq_Server {


	private $connection;
	
	function __construct( Gcis_Db $connection ) {
		$this->connection = $connection;
	}


    function process() {
    	$received = false;
    	try {
			$this->connection->startTransaction();
			$msg = Dao_Outbox::get_next_msg( $this->connection );


			$parts = explode( "@", $msg['destination'] );
			$queue_name = $parts[0];
	    	try {
	    		$queue_id = Dao_Queue::get_id_by_name( $this->connection, $queue_name );
		   	} catch( Fluid_NoDataFoundException $e ) {
    			$queue_id = Dao_Queue::insert( $this->connection, $queue_name );
	    	}
	    	Dao_Inbox::insert( $this->connection, $queue_id, $msg['data'] );
			Dao_Outbox::delete( $this->connection, $msg['id'] );

	    	
			$this->connection->commitTransaction();
	    	$received = true;
		} catch ( Fluid_NoDataFoundException $e ) {
			$this->connection->rollbackTransaction();
		}
    	
    	
    	return $received;
    }


}
