<?php
require_once 'Fluid/StateChangeHandler.php';


class StateChangeHandler_Create===Name===
	extends Fluid_StateChangeHandler {


	function ChangeState( $name ) {
		$id = $this->fluid->Dao( '===Name===' )->insert( $name );


		return $id;

	}


}
