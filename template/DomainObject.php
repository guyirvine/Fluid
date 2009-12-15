<?php
require_once 'Fluid/DomainObject.php';


class ===Name=== 
	extends Fluid_DomainObject {


	function updateDetail( $name ) {
		f()->State( 'Update===Name===', $this->id, $name );
		f()->Raise( '===Name===Updated', $this->id );

	}


}
