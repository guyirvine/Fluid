<?php
require_once '_init.php';


if ( isset( $_POST['submit'] ) ) {
	if ( isset( $_POST['===name===_id'] ) ) {
		$===name===_id = p('===name===_id');
		f()->===Name===()->updateDetail( p('name') );

	}

	fluid_redirect( "===name===.php?===name===_id=" . $===name===_id );

}
