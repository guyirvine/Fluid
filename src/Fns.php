<?php
class Fluid_HttpException extends Exception {}


function _fluid_create_hierarchy_from_list( $id_list, $list, $parent_id, $rank, $depth ) {
	foreach( $list as $key=>$item ) {
		if ( is_null( $parent_id ) && is_null( $item['parent_id'] ) ||
				$parent_id == $item['parent_id'] ) {
			$_rank = "$rank.$key";
			$id_list[] = array( $key, $_rank, $depth );
			$id_list = _fluid_create_hierarchy_from_list( $id_list, $list, $item['id'], $_rank, $depth+1 );
		}
	}

	
	return $id_list;
}
function fluid_create_hierarchy_from_list( $list, $top_level_parent_id_marker=null ) {
	$id_list = array();
	return _fluid_create_hierarchy_from_list( $id_list, $list, $top_level_parent_id_marker, "", 0 );
}


function fluid_log( $string, $filename="/tmp/log" ) {
//	print "Logging: " . $GLOBALS['logging'] . "<br>\n";
        if ( isset( $GLOBALS['logging'] ) && $GLOBALS['logging'] == 1 ) {
//                file_put_contents( $filename, strftime( "%d %b %Y %H:%M" ) . "$string\n", FILE_APPEND );
		syslog( LOG_ERR, $string );
	}
}


function fluid_log_memusage( $string, $filename="/tmp/log.memusage" ) {
        if ( isset( $GLOBALS['log_memusage'] ) && $GLOBALS['log_memusage'] == 1 )
		file_put_contents( $filename, "$string. " . memory_get_usage(true) . "\n", FILE_APPEND );

}

function c( $command ) {
	$parts = pathinfo( $_SERVER['REQUEST_URI'] );
	$name = $parts['filename'];
	$param_name = $name . "_id";


	switch( $command ) {
		case 'CREATE':
			return ( isset( $_POST['submit'] ) && !isset( $_POST[$param_name] ) );
			break;

		case 'UPDATE':
			return ( isset( $_POST['submit'] ) && isset( $_POST[$param_name] ) );
			break;

		case 'DELETE':
			return isset( $_GET['delete'] );
			break;

		default:
			throw new Exception();
	}
}


function a() {
	return func_get_args();
}


function s() {
	$array = func_get_args();
	$key = array_shift( $array );

	for( $i=0;$i<count( $array );$i=$i+2 ) {
		if ( $array[$i] == $key )
			return $array[$i+1];
	}


	throw new Exception();
}


function p( $name ) {
	if ( !isset( $_POST[$name] ) ) {
		fluid_log( "Post variable, $name, not found." );
		throw new Fluid_NoDataFoundException( "Post variable, $name, not found." );
	}

	return $_POST[$name];
}
function g( $name ) {
	if ( !isset( $_GET[$name] ) ) {
		fluid_log( "Get variable, $name, not found." );
		throw new Fluid_NoDataFoundException( "Get variable, $name, not found." );
	}

	return $_GET[$name];
}


function i( $expression, $value ) {
	return $expression ? $value : null;
}
function i_nn( $variable, $value ) {
	return i( !is_null( $variable ), $value );
}

function fluid_http_post($url, $data, $optional_headers = null) {
	$params = array('http' => array(
					'method' => 'POST',
					'content' => $data ));
	if ( strpos( strtolower( $optional_headers ), 'content-type' ) === false )
		$optional_headers = "Content-type: application/x-www-form-urlencoded\r\n" . $optional_headers;
	$params['http']['header'] = $optional_headers;


	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Fluid_HttpException("Problem with $url, $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		throw new Fluid_HttpException("Problem reading data from $url, $php_errormsg");
	}

	
	$ret = @stream_get_meta_data( $fp );
	$ret['body'] = $response;

	return $ret;
}




function fluid_flatten( $array, $depth=null ) {
	$list = array();
	foreach( $array as $item ) {
		if ( is_array( $item ) ) {
			$_list = fluid_flatten( $item );
			$list = array_merge( $list, $_list );
		} else {
			if ( !is_null( $item ) )
				$list[] = $item;
		}
	}

	return $list;
}


function fluid_coalesce() {
	$args = func_get_args();
	foreach ($args as $arg) {
		if (!empty($arg)) {
			return $arg;
		}
	}

	return $args[0];
}


function fluid_parse_csv_line( $input, $delimiter=",", $enclosure='"' ) {
	$fh = fopen('php://memory', 'rw'); 
	fwrite($fh, $input); 
	rewind($fh); 
	$result = fgetcsv( $fh, strlen( $input ), $delimiter, $enclosure ); 
	fclose($fh); 
	return $result;
}


function fluid_substr_on_word_boundary( $string, $max_len=50, $suffix=' ...' ) {
        if ( strlen( $string ) == 0 ) {
            return '';
        }
        
        $parts = explode( "\n", $string, 2 );
        if ( strlen( $parts[0] ) < $max_len ) {
            return $parts[0];
        }

        $partial_string = substr( $parts[0], 0, $max_len );
        $end_pos = strrpos( $partial_string, " " );
        $final_string = substr( $partial_string, 0, $end_pos );
        $final_string = trim( $final_string );
        
        $final_string .= $suffix;

        return $final_string;
}    


function fluid_redirect( $url ) {
	if ( empty( $GLOBALS['testing'] ) ) {
		header( 'Location: ' . $url );
		exit();
	} else {
		$GLOBALS['redirect'] = $url;
	}
}


function fluid_format_for_html( $string ) {
	if ( empty( $string ) ) return null;

	return htmlspecialchars( $string, ENT_QUOTES, 'utf-8' );
}


function fluid_array_filter( $input, $callback, $userdata=null ) {
	if ( is_null( $userdata ) ) {
		return array_filter( $input, $callback );
	}

	$list = array();
	foreach( $input as $row ) {
		if ( $callback( $row, $userdata ) ) {
			$list[] = $row;
		}
	}
	
	return $list;
}

function fluid_array_map( $callback, $input, $userdata=null ) {
	if ( is_null( $userdata ) ) {
		$list = array_map( $callback, $input );
		return $list;
	} else {
		$list = array();
		foreach( $input as $row ) {
			$list[] = $callback( $row, $userdata );
		}
		return $list;
	}
	

}


function fluid_array_reduce( $input, $callback, $userdata=null ) {
	if ( is_null( $userdata ) ) {
		$list = array_reduce( $input, $callback );
		return $list;
	} else {
		$value = null;
		foreach( $input as $row ) {
			$value = $callback( $value, $row, $userdata );
		}
		return $value;
	}
	

}


function fluid_pipeline() {
	$args = func_get_args();
	$input = array_shift( $args );
	$params = array_shift( $args );

	if ( is_array( $args[0] ) )
		$args = $args[0];


	while ( count( $args ) > 0 ) {
		$switch = array_shift( $args );
		
		switch( $switch ) {
			case 'F': //filter
				$callback = array_shift( $args );
				$input = fluid_array_filter( $input, $callback, $params );
				break;
			case 'M': //map
				$callback = array_shift( $args );
				$input = fluid_array_map( $callback, $input, $params );
				break;
			case 'T': //flatten
				$input = fluid_flatten( $input );
				break;
			case 'R': //reduce
				$callback = array_shift( $args );
				$input = array_reduce( $input, $callback );
				break;
			case 'W': //Walk
				$callback = array_shift( $args );
				array_walk( $input, $callback, $params );
				break;
			default:
				throw new Exception( "Type: $switch, not supported." );
		}
	}
	
	return $input;
}


function moveItemInList( $list, $oldSeqNum, $newSeqNum ) {
	if ( $oldSeqNum == $newSeqNum )
		return $list;
	if ( $newSeqNum < 1 )
		$newSeqNum = 1;


	$oldSeqNum--;
	$newSeqNum--;
	$itemToBeMoved = $list[$oldSeqNum];
	$new_list=array();
	foreach( $list as $seq=>$item ) {
		if ( $oldSeqNum == $seq )
			continue;


		switch ( true ) {
			case ( $seq < $newSeqNum ):
				$new_list[] = $item;
				break;
			case ( $seq > $newSeqNum ):
				$new_list[] = $item;
				break;


			case ( $newSeqNum < $oldSeqNum ):
				$new_list[] = $itemToBeMoved;
				$new_list[] = $item;
				$itemToBeMoved = null;
				break;
			case ( $newSeqNum > $oldSeqNum ):
				$new_list[] = $item;
				$new_list[] = $itemToBeMoved;
				$itemToBeMoved = null;
				break;

		}
				
			
	}
	if ( !is_null( $itemToBeMoved ) )
		$new_list[] = $itemToBeMoved;

	return $new_list;
}
