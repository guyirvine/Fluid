<?php


class Dao_User {

	static function get( $connection, $id ) {
		$sql = 'SELECT u.id, ' .
						'u.email_address ' .
				'FROM user_tbl u ' .
				'WHERE id = $1 ' .
				'';


		$params = array( $id );


		return $connection->queryForArray( $sql, $params );
	}


}
