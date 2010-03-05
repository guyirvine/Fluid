<?php
require_once 'Fluid/FetchingStrategy.php';


class FetchingStrategy_===Name===
    extends Fluid_FetchingStrategy {

	function Get( $id ) {
		$data = $this->fluid->Dao( "===Name===" )->get( $id );

		return $this->fluid->build( "===Name===", $data );

	}

}
