<?php
require_once 'Fluid/StateChangeHandler.php';
require_once 'Dao/===Name===.php';


class StateChangeHandler_Update===Name===
	extends Fluid_StateChangeHandler {


	function ChangeState( $id, $name ) {
		$this->fluid->Dao( '===Name===' )->update( $id, $name );

	}


}
