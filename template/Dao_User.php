<?php
require_once 'Fluid/Dao.php';


class Dao_User 
	extends Fluid_Dao {

	function get( $id ) {
		$sql = 'SELECT u.id, ' .
						'u.email_address ' .
				'FROM user_tbl u ' .
				'WHERE id = $1 ' .
				'';


		$params = array( $id );


		return $this->connection->queryForArray( $sql, $params );
	}


}
