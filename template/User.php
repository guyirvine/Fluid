<?php
require_once 'Fluid/DomainObject.php';


class User 
	extends Fluid_DomainObject {


	function create_name_( $name ) {
		$id = f()->State( 'Create_name_', $this->id, $name );
		f()->Raise( '_name_Updated', $id );


		return $id;
	}


	function CreateUser( $name ) {
		$id = f()->State( 'CreateUser', $name );
		f()->Raise( 'UserCreated', $id, $name );


		return $id;
	}


}
