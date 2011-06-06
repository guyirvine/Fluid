<?php
require_once "/etc/FluidMq.php";
require_once "Fluid/ErrorHandler.php";


function async_sending( $end_time, $base_dir ) {

	while( time() < $end_time ) {

		$processed_msgs = false;
		$msg_list = array();


		foreach( glob( "/var/lib/mq/*" ) as $dir_path ) {
			$pathparts = pathinfo( $dir_path );
			if ( $dir_path == "/var/lib/mq/." ||
					$dir_path == "/var/lib/mq/.." ||
					!is_dir( $dir_path ) )
				continue;

			foreach( glob( "$dir_path/*.msg" ) as $msg_path ) {
				$processed_msgs = true;


				$msg_list[] = array( 'host'=>$GLOBALS['local_mq_destination_host'],
									'uri'=>"/" . $pathparts['basename'] . "/FluidMq.php",
									'msg_path'=>$msg_path,
									'data'=>file_get_contents( $msg_path )
									);
			}

		}


		if ( $processed_msgs ) {
			$timeout = 15;
			$status = array();
			$sockets = array();
			/* Initiate connections to all the hosts simultaneously */
			foreach ($msg_list as $id => $msg) {
				$host=$msg['host'];
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
						$msg = $msg_list[$id];
						$data = fread($r, 8192);
						if (strlen($data) == 0) {
							if ($status[$id] == "in progress") {
								$status[$id] = "failed to connect";
							}
							fclose($r);
							unset($sockets[$id]);
							unlink( $msg['msg_path'] );

						} else {
							print "mq.bin.daemon.Data: $data\n";
							if ( strpos( $data, 'error' ) !== false ) {
								file_put_contents( "/tmp/log", "mq.bin.daemon.Data: $data\n", FILE_APPEND );;
							}

							fclose($r);
							unset($sockets[$id]);
							$content = file_get_contents( $msg['msg_path'] );
							file_put_contents( $msg['msg_path'], "$content\n$data", FILE_APPEND );
							unlink( $msg['msg_path'] );
						}
					}
					/* writeable sockets can accept an HTTP request */
					foreach ($write as $w) {
						$id = array_search($w, $sockets);

						if ( $status[$id] != "waiting for response" ) {
							$msg = $msg_list[$id];
							$uri = $msg['uri'];
							$reqbody = $msg['data'];
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

			foreach ($msg_list as $id => $msg) {
				file_put_contents( "/tmp/log", "Host: " . $msg['host'] . "/" . $msg['uri'] . ". Status: " . $status[$id] . "\n", FILE_APPEND );
			}
			
		}
		

		if ( !$processed_msgs ) {
			sleep(1);
		}
	}

}



function error_handler($errno, $errstr, $errfile, $errline) {
	file_put_contents( "/tmp/log", "mq_error_handler. $errfile($errline). $errstr</h1>\n", FILE_APPEND );
}


$old_error_handler = set_error_handler( 'error_handler' );
$end_time = time() + ( 60 * 60 );


async_sending( $end_time, $base_dir );
