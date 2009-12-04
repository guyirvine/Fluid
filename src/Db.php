<?php

class Fluid_ConnectionException extends Exception {}
class Fluid_DuplicateKeyException extends Exception {}
class Fluid_NoDataFoundException extends Exception {};
class Fluid_TooManyRowsException extends Exception {};

interface Fluid_Db
{


	function queryForArray( $sql, $params );
	function queryForValue( $sql, $params );
	function queryForResultset( $sql, $params );
	function queryForSearch( $sql, $params, $start, $count );
	function getNewId( $sequenceName );
	function execute( $sql, $params );

	function startTransaction();
	function commitTransaction();
	function rollbackTransaction();
	
}
