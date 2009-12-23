<?php
require_once 'Fluid/Abstract.php';


abstract class Fluid_Cache
	extends Fluid_Abstract {


	function Put( $key, $data ) {
		$this->Expire( $key );

		$sql = "INSERT INTO cache_tbl( key, created, data ) VALUES ( $1, $2, $3 ) ";
		$params = array( $key, 
						strftime( "%e %b %Y %r" ), 
						serialize( $data ) );
	
		$this->connection->execute( $sql, $params );
	}


	function Expire() {
		$params = func_get_args();
		$key = call_user_func_array(array($this, "MakeKey"), $params);

		$this->connection->execute( "DELETE FROM cache_tbl WHERE key = $1", array( $key ) );
	}


	function Get() {
		try {
			$params = func_get_args();
			$key = call_user_func_array(array($this, "MakeKey"), $params);


			$_data = $this->connection->queryForValue( "SELECT data FROM cache_tbl WHERE key = $1", array( $key ) );
			return unserialize( $_data );

		} catch ( Fluid_NoDataFoundException $e ) {
			$data = call_user_func_array(array($this, "Regenerate"), $params);
		}
		
		
		return $data;
	}


}

