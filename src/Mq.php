<?php
require_once "Fluid/Fluid.php";


interface Fluid_Mq {

	function Run();

	function AddPubSub( $exchange, $queue_list );
	function AddListener( $queue_list );

}
