<?php
<<<<<<< HEAD:src/Fns.php

=======
	
>>>>>>> f43afefea817e115193e0ae0f7eb9aecb338c762:src/Fns.php
function fluid_log( $string, $filename="/tmp/log" ) {
<<<<<<< HEAD:src/Fns.php
        if ( $GLOBALS['logging'] == 1 )
                file_put_contents( $filename, "$string\n", FILE_APPEND );
=======
//	if ( $GLOBALS['logging'] == 1 )
		file_put_contents( $filename, "$string\n", FILE_APPEND );
>>>>>>> f43afefea817e115193e0ae0f7eb9aecb338c762:src/Fns.php
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
	return $_POST[$name];
}
function g( $name ) {
	return $_GET[$name];
}


function i( $expression, $value ) {
	return $expression ? $value : null;
}
function i_nn( $variable, $value ) {
	return i( !is_null( $variable ), $value );
}


function fluid_flatten( $array, $depth=null ) {
	$list = array();
	foreach( $array as $item ) {
		if ( is_array( $item ) ) {
			$_list = flatten( $item );
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
<<<<<<< HEAD:src/Fns.php
        if ( strlen( $string ) == 0 ) {
            return '';
        }
        
        $parts = explode( "\n", $string, 2 );
        if ( strlen( $parts[0] ) < $max_len ) {
            return $parts[0];
        }
=======
	if ( strlen( $string ) == 0 ) {
		return '';
	}
>>>>>>> f43afefea817e115193e0ae0f7eb9aecb338c762:src/Fns.php

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


function fluid_pipeline( $pipeline, $input, $params=null ) {
	foreach( $pipeline as $row ) {
		switch( $row[0] ) {
			case 'F': //filter
				$input = fluid_array_filter( $input, $row[1], $params );
				break;
			case 'M': //map
				$input = fluid_array_map( $row[1], $input, $params );
				break;
			case 'T': //flatten
				$input = flatten( $input );
				break;
			case 'R': //reduce
				$input = array_reduce( $input, $row[1] );
				break;
			default:
				throw new Exception( 'Type: ' . $row[0] . ', not supported.' );
		}
	}
	
	return $input;
}
