<?php
require_once 'Fluid/ICacheStore/Persistant.php';


class Fluid_CacheStore_Plsql
		implements Fluid_ICacheStore_Persistant {

	private $connection;


	function __construct( $connection ) {
		$this->connection = $connection;
	}


	function get( $key ) {
		try {
			return Pgsql::queryForValue( $this->connection, "SELECT data FROM cache_tbl WHERE key = $1", array( $key ) );
		} catch ( NoDataFoundException $e ) {
			throw new Fluid_NoDataFoundException( $e );
		}
	}


	function delete( $key ) {
		Pgsql::execute( $this->connection, "DELETE FROM cache_tbl WHERE key = $1", array( $key ) );
	}


	function put( $key, $value ) {
		$this->delete( $key );


		$sql = "INSERT INTO cache_tbl( key, created, data ) VALUES ( $1, $2, $3 ) ";
		$params = array( $key, 
						strftime( "%e %b %Y %r" ), 
						$value );


		Pgsql::execute( $this->connection, $sql, $params );


	}


	function getDependencyList( $from_key ) {
		$_list = Pgsql::queryForResultSet( $this->connection, "SELECT to_key FROM cachedependency_tbl WHERE from_key = $1", array( $from_key ) );
		$list = array();
		foreach( $_list as $row ) {
			$list[] = $row['to_key'];
		}
		
		
		return $list;
	}


	function addDependency( $from_key, $to_key ) {
		Pgsql::execute( $this->connection, "INSERT INTO cachedependency_tbl( from_key, to_key ) VALUES ( $1, $2 )", array( $from_key, $to_key ) );
	}


	function removeDependencyList( $key ) {
		Pgsql::execute( $this->connection, "DELETE FROM cachedependency_tbl WHERE to_key = $1", array( $key ) );
	}


}
