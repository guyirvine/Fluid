<?php
require_once "Fluid/Fluid.php";
require_once "conf.php";


function fail_with_grace( $description ) {
	header("HTTP/1.0 500 Internal Server Error");
	print $description;
	die();
}

function processPathList( $path_list ) {
	if ( $path_list == "/" )
		return array();


	$_path_list = explode( '/', $path_list );
	if ( empty( $_path_list[0] ) ) {
		array_shift( $_path_list );
	}
	if ( empty( $_path_list[count( $_path_list )-1] ) ) {
		array_pop( $_path_list );
	}
	
	return $_path_list;
}


$path_list = processPathList( $_SERVER["PATH_INFO"] );
if ( strtolower( $_SERVER["REQUEST_METHOD"] ) != 'post' )
	fail_with_grace( "This mq only accepts post requests" );
if ( count( $path_list ) != 1 )
	fail_with_grace( "QueueName must be a single name. " . count( $path_list ) . " found." );


$queue_name = $path_list[0];
$post_data = file_get_contents( "php://input" );


$queue_dir = "$base_dir/$queue_name";
if ( !is_dir( $queue_dir ) )
	mkdir( $queue_dir );


$count = 1;
$unique = false;
$_msg_name = posix_getpid() . "_" . time();
while( !$unique ) {
	$msg_path = "$queue_dir/$_msg_name" . "_" . $count++ . ".msg";
	$unique = !is_file( $msg_path );
}


$tmp_path = "$msg_path.tmp";
file_put_contents( $tmp_path, $post_data );
rename( $tmp_path, $msg_path );
