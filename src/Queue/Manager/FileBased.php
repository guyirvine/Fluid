<?php
require_once "Fluid/Queue/Manager.php";


class Fluid_Queue_Manager_FileBased
	implements Fluid_Queue_Manager {

	private $queueDir;


	function __construct() {
		$this->queueDir = getcwd() . "/data";
	}

	private function makeSurePathExists( $path ) {
		if ( !is_dir( $path ) )
			mkdir( $path, 0777, true );
	}


	private function getQueuePathFromQueueName( $queue_name ) {
		return "{$this->queueDir}/$queue_name";
	}

	function addMsgToQueue( $queue_name, $msg ) {
		$queue_path = $this->getQueuePathFromQueueName( $queue_name );
		$this->makeSurePathExists( $queue_path );


		$filepath_list = glob( "$queue_path/*.msg" );
		$count = 0;
		if ( count( $filepath_list ) > 0 ) {
			foreach( $filepath_list as $filepath ) {
				$parts = pathinfo( $filepath );
				$count = ( $count < $parts['filename'] ) ? $parts['filename'] : $count;
			}
			$count++;
		}
		file_put_contents( "$queue_path/$count.msg", $msg );
	}
	
	
	function peekAtNextMsgInQueue( $queue_name ) {
		$queue_path = $this->getQueuePathFromQueueName( $queue_name );
		$this->makeSurePathExists( $queue_path );


		$msg_path = "$queue_path/*.msg";
		fluid_log( "Fluid_Stomp_Server.processSubscribe.msg_path: $msg_path" );
		$filepath_list = glob( "$queue_path/*.msg" );
		if ( count( $filepath_list ) == 0 ) {
			return false;
		}
		
		$filepath = array_shift( $filepath_list );
		$body = file_get_contents( $filepath );


		return array( 'id'=>$filepath, 'body'=>$body );
	}
	
	function removeMsgFromQueue( $queue_name, $id ) {
		$filepath = $id;
		unlink( $filepath );
	}

}
