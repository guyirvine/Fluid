<?php

class Dao_===Name=== {


	static function get( $connection, $user_id, $id ) {
		$sql = "SELECT t.id AS id " .
				"FROM ===name===_tbl t " .
				"INNER JOIN ===name===_access_vw a ON ( t.id = a.===name===_id ) " .
				"WHERE a.user_id = $1 " .
				"AND t.id = $2 " .
				"";

		$params = array( $user_id, $id );

		return $connection->queryForArray( $sql, $params );
	}

	static function update( $connection, $id, $name ) {
		$sql = "UPDATE ===name===_tbl " .
				"SET name = $1 " .
				"WHERE id = $2 " .
				"";

		$params = array( $name, $id );


		$connection->execute( $sql, $params );
	}


	static function insert( $connection, $name ) {

		$id = $connection->getNewId( '===name===_seq' );
		$created = strftime( "%e %b %Y %H:%M:%S" );
		$sql = 'INSERT INTO ===name===_tbl( id, created, name ) ' .
				"VALUES ($1, $2, $3 ) ";

		$params = array( $id, $created, $name );

		$connection->execute( $sql, $params );

		return $id;
	}


	static function delete( $connection, $id ) {
		$sql = 'DELETE FROM ===name===_tbl WHERE id = $1 ';

		$params = array( $id );

		$connection->execute( $sql, $params );

	}
	
	
}
