<?php
require_once 'Fluid/Fluid.php';
require_once 'Fluid/Db/Pgsql.php';
require_once '_page.php';
require_once 'conf.php';


date_default_timezone_set( 'Pacific/Auckland' );
function check_password( $connection, $email_address, $password ) {
        $sql = 'SELECT u.id ' .
                   'FROM user_tbl u ' .
                   'WHERE u.email_address = $1 AND u.password = $2 ' .
                   '';

        $params = array( $email_address, $password );

        $GLOBALS['loggedInUser']['id'] = $connection->queryForValue( $sql, $params );
}


function format_decimal( $amount ) {
	if ( $amount == "" ) return "&nbsp;";
	
	return number_format( $amount, 1 );
}


$connection = new Fluid_Db_Pgsql( get_connection_string() );
include_once( 'Fluid/Auth/Basic.php' );
$user_id = $GLOBALS['loggedInUser']['id'];
f( $connection, $user_id );
