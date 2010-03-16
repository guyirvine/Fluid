<?php
require_once "/etc/FluidMq.php";
require_once "Fluid/Stomp/Msg.php";
require_once "Fluid/Stomp.php";

function get_all( Fluid_Stomp $stomp ) {

	$msg_list = array();
	foreach( $GLOBALS['mq_client_list'] as $queue_name ) {
		$stomp->subscribe( $queue_name );
		$content = "";
		$buffer = $stomp->receive();
		while( !empty( $buffer ) ) {
			$content .= $buffer;
			$buffer = $stomp->receive();
		}

		$parts = explode( "\0", $content );
		foreach( $parts as $part ) {
			if( strlen( $part ) > 2 ) {
				$msg = new Fluid_Stomp_Msg( $part );
				if ( $msg->command == "MESSAGE" ) {
					$msg_list[] = $msg;
				}
			}
		}
	}

	return $msg_list;
}


function async_sending( $end_time, $db ) {

while( time() < $end_time ) {

	$processed_msgs = false;
	$msg_list = array();


	$stomp = new Fluid_Stomp();
	$stomp->connect();
	$list = get_all( $stomp );
	$stomp->disconnect();
	foreach( $list as $msg ) {
		$processed_msgs = true;


		$msg_list[] = array( 'host'=>$GLOBALS['local_mq_host'],
								'uri'=>"/" . $msg->destination . "/FluidMq.php",
								'data'=>$msg->body
								);

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
			file_put_contents( "/tmp/log", "mq.bin.daemon.Data: $data\n", FILE_APPEND );;
		}

                fclose($r);
                unset($sockets[$id]);
//                $status[$id] .= $data;
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

/*
$pid = pcntl_fork();
if ($pid == -1) {
	die('could not fork');
} else if ($pid) {
	$is_parent = true;
	//we are the parent
} else {
*/

$end_time = time() + ( 60 * 60 );
$db = getMqConnection();

async_sending( $end_time, $db );
