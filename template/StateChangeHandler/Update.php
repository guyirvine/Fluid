<?php
require_once 'Fluid/StateChangeHandler.php';
require_once 'Dao/===Name===.php';


class StateChangeHandler_Update===Name===
	extends Fluid_StateChangeHandler {


	function ChangeState( $id, $name ) {
		Dao_===Name===::update( $this->connection,
							$id, 
							$name );

	}


}
