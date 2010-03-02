<?php
require_once "/etc/FluidMq.php";
require_once "Fluid/ErrorHandler.php";


function msgs_pending( $db ) {
	$current_timstamp = strftime( "%e %b %Y %H:%M:%S", time() );
	$sql = "SELECT DISTINCT i.id " .
			"FROM inbox_tbl i " .
			"WHERE i.processed_yn = 'N' " .
			"AND COALESCE( i.dont_process_until, $1 ) <= $1 " .
			"LIMIT 1 " .
			"";

	try {
		$db->queryForValue( $sql, array( $current_timstamp ) );
		return true;
	} catch ( Fluid_NoDataFoundException $e ) {
		return false;
	}
}


function async_sending( $end_time, $db ) {

	$host=$GLOBALS['local_mq_host'];
	$clients = array( "bugtraq" );
	while( time() < $end_time ) {

		if ( msgs_pending( $db ) ) {
//print "Pending\n";
			$timeout = 15;
			$status = array();
			$sockets = array();
			/* Initiate connections to all the hosts simultaneously */
			foreach ($clients as $id=>$client) {
//print "Initiate: $host:$client\n";
				$s = stream_socket_client( $host, $errno, $errstr, $timeout, 
						STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT);
				if ($s) {
					$sockets[$id] = $s;
					$status[$id] = "in progress";
				} else {
					$status[$id] = "failed, $errno $errstr";
				}
			}


			/* Now, wait for the results to come back in */
			while (count($sockets)) {
			    $read = $write = $sockets;
				/* This is the magic function - explained below */
				$e = array();
				$n = stream_select($read, $write, $e, $timeout);
				if ($n > 0) {
					/* readable sockets either have data for us, or are failed
						* connection attempts */
					foreach ($read as $r) {
						$id = array_search($r, $sockets);
						$data = fread($r, 8192);
						if (strlen($data) == 0) {
							if ($status[$id] == "in progress") {
								$status[$id] = "failed to connect";
							}
							fclose($r);
							unset($sockets[$id]);
						} else {
		print "mq.bin.daemon.Data: $data\n";
							if ( strpos( $data, 'error' ) !== false ) {
								fluid_log( "mq.bin.daemon.Data: $data" );
							}

							fclose($r);
							unset($sockets[$id]);
						}
					}

		
					/* writeable sockets can accept an HTTP request */
					foreach ($write as $w) {

						$id = array_search($w, $sockets);

						if ( $status[$id] != "waiting for response" ) {
							$uri = "/" . $clients[$id] . "/FluidMq.php";
//							$req =  "GET $uri HTTP/1.1\r\n".
//									"Host: $host\n". "User-Agent: FluidMq\r\n\r\n";
//							fwrite($w, $req);
				$reqbody = 'data';
				$contentlength = strlen($reqbody);
				$req =  "POST $uri HTTP/1.1\r\n".
							"Host: $host\n". "User-Agent: FluidMq\r\n".
							"Content-Type: application/x-www-form-urlencoded\r\n".
							"Content-Length: $contentlength\r\n\r\n".
							"$reqbody\r\n"; 
				fwrite($w, $req);

							$status[$id] = "waiting for response";

						}
					}
				} else {
					/* timed out waiting; assume that all hosts associated
						* with $sockets are faulty */
					foreach ($sockets as $id => $s) {
						$status[$id] = "timed out " . $status[$id];
					}
					break;
				}
			}

			foreach ($clients as $id => $client) {
				file_put_contents( "/tmp/log", "Host: " . $client . ". Status: " . $status[$id] . "\n", FILE_APPEND );
			}
	
		} else {
			sleep(1);
		}
	}

}



function error_handler($errno, $errstr, $errfile, $errline) {
	file_put_contents( "/tmp/log", "mq_error_handler. $errfile($errline). $errstr</h1>\n", FILE_APPEND );
}


$old_error_handler = set_error_handler( 'error_handler' );

$end_time = time() + ( 60 * 60 );
$db = getMqConnection();

async_sending( $end_time, $db );
