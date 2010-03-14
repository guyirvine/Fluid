<?php

interface Fluid_Queue_Manager {

	function addMsgToQueue( $queue_name, $msg );
	
	function peekAtNextMsgInQueue( $queue_name );
	
	function removeMsgFromQueue( $queue_name, $id );

}
