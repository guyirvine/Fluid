<?php
require_once 'Fluid/Db.php';


class Fluid_Db_Pgsql
	implements Fluid_Db
{

	
	private $connection;
	
	
	function __construct( $connection_string ) {
		$this->connection = pg_connect( $connection_string );

		$status = pg_connection_status($this->connection);
		if ( $status !== PGSQL_CONNECTION_OK ) {
			throw new Fluid_ConnectionException();
		}
      
	}


	function queryForArray( $sql, $params ) {
		$result = pg_query_params( $this->connection, $sql, $params );
		if ( $result === false ) {
			$message = pg_last_error( $this->connection );
			throw new Fluid_ConnectionException( $message );
		}

		switch( pg_num_rows( $result ) ) {
			case -1:
				throw new Fluid_ConnectionException();

			case 0:
				throw new Fluid_NoDataFoundException( "$sql. " . print_r( $params, true ) );
				break;

			case 1:
				$row = pg_fetch_assoc( $result );
				if ( $row === false )
					throw new Fluid_ConnectionException();

				break;
			default:
				throw new Fluid_TooManyRowsException( $sql  );
				break;
		}

		return $row;
	}


	function queryForValue( $sql, $params ) {
		$result = pg_query_params( $this->connection, $sql, $params );
		if ( $result === false ) {
			$message = pg_last_error( $this->connection );
			throw new Fluid_ConnectionException( $message );
		}

		switch( pg_num_rows( $result ) ) {
			case -1:
				throw new Fluid_ConnectionException(pg_last_error( $this->connection ));

			case 0:
				throw new Fluid_NoDataFoundException( "$sql. " . print_r( $params, true ) );

			case 1:
				$row = pg_fetch_row( $result );
				$value = $row[0];
				break;

			default:
				throw new Fluid_TooManyRowsException( $sql );
				break;
		}


		return $value;
	}


	function queryForResultset( $sql, $params ) {
		$result = pg_query_params( $this->connection, $sql, $params );
		if ( $result === false ) {
			$message = pg_last_error( $this->connection );
			throw new Fluid_ConnectionException( $message );
		}

		$list = array();
		while( ( $row = pg_fetch_assoc( $result ) ) ) {
			$list[] = $row;	
		}

		return $list;
	}


	function getNewId( $sequenceName ) {
		$sql = "SELECT NEXTVAL( $1 ) AS new_id";

		$result = pg_query_params( $this->connection, $sql, array( $sequenceName ) );
		if ( $result === false )
			throw new Fluid_ConnectionException();

		$row = pg_fetch_row( $result );
		if ( $row === false )
			throw new Fluid_ConnectionException();


		return $row[0];
	}


	function execute( $sql, $params ) {
		$result = @pg_query_params( $this->connection, $sql, $params );
		if ( !$result ) {
			$message = pg_last_error( $this->connection );
			if ( strpos( $message, "duplicate key" ) > 0 ) {
				throw new Fluid_DuplicateKeyException( $message );
			} else {
				throw new Fluid_ConnectionException( "$message. sql: $sql. " . print_r( $params, true ) );
			}
		}
	}


	function startTransaction() {
		pg_exec( $this->connection, 'BEGIN' );
	}
	
	function commitTransaction() {
		pg_exec( $this->connection, 'COMMIT' );
	}

	function rollbackTransaction() {
		pg_exec( $this->connection, 'ROLLBACK' );
	}


}
