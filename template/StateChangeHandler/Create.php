<?php
require_once 'Fluid/StateChangeHandler.php';
require_once 'Dao/===Name===.php';


class StateChangeHandler_Create===Name===
	extends Fluid_StateChangeHandler {


	function ChangeState( $name ) {
		$id = Dao_===Name===::insert( $this->connection,
									$name );


		return $id;

	}


}
