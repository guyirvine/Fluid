<?php
require_once 'Fluid/Dao.php';


class Dao_===Name===  
	extends Fluid_Dao {


	function get( $id ) {
		$sql = "SELECT t.id AS id " .
				"FROM ===name===_tbl t " .
				"INNER JOIN ===name===_access_vw a ON ( t.id = a.===name===_id ) " .
				"WHERE a.user_id = $1 " .
				"AND t.id = $2 " .
				"";

		$params = array( $this->user_id, $id );

		return $this->connection->queryForArray( $sql, $params );
	}

	function update( $id, $name ) {
		$sql = "UPDATE ===name===_tbl " .
				"SET name = $1 " .
				"WHERE id = $2 " .
				"";

		$params = array( $name, $id );


		$this->connection->execute( $sql, $params );
	}


	function insert( $name ) {

		$id = $this->connection->getNewId( '===name===_seq' );
		$created = strftime( "%e %b %Y %H:%M:%S" );
		$sql = 'INSERT INTO ===name===_tbl( id, created, name ) ' .
				"VALUES ($1, $2, $3 ) ";

		$params = array( $id, $created, $name );

		$this->connection->execute( $sql, $params );

		return $id;
	}


	function delete( $id ) {
		$sql = 'DELETE FROM ===name===_tbl WHERE id = $1 ';

		$params = array( $id );

		$this->connection->execute( $sql, $params );

	}
	
	
}
