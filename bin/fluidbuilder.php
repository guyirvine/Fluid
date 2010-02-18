#!/usr/bin/php
<?php
function getAbsoluteFluidDirectory() {
	$path = $_SERVER['SCRIPT_FILENAME'];
	while( is_link($path))
		$path = readlink( $path );


	$parts = explode( "/", $path );
	array_pop( $parts );
	array_pop( $parts );
	

	return implode( "/", $parts );
}

function getAbsoluteFluidSrcDirectory() {
	return getAbsoluteFluidDirectory() . "/src/Builder";
}
function getAbsoluteFluidTemplateDirectory() {
	return getAbsoluteFluidDirectory() . "/template";
}


require_once getAbsoluteFluidSrcDirectory() . "/fns.php";


if ( $_SERVER['argc'] < 2 )
	die( "fluidbuilder [init, mobile, pc, cache, domaineventhandler, messagehandler]\n" );


switch ( $_SERVER['argv'][1] )  {
	case 'init':
		include getAbsoluteFluidSrcDirectory() . "/init.php";
		break;
	case 'pc':
		if ( !is_file( "src/_init.php" ) )
			die( "run 'fluidbuilder init' first\n" );
		include getAbsoluteFluidSrcDirectory() . "/pc.php";
		break;
	case 'cache':
		if ( !is_file( "src/_init.php" ) )
			die( "run 'fluidbuilder init' first\n" );
		include getAbsoluteFluidSrcDirectory() . "/cache.php";
		break;
	case 'domaineventhandler':
		if ( !is_file( "src/_init.php" ) )
			die( "run 'fluidbuilder init' first\n" );
		include getAbsoluteFluidSrcDirectory() . "/domaineventhandler.php";
		break;
	case 'messagehandler':
		if ( !is_file( "src/_init.php" ) )
			die( "run 'fluidbuilder init' first\n" );
		include getAbsoluteFluidSrcDirectory() . "/messagehandler.php";
		break;
	default:
		die( "fluidbuilder [init, mobile, pc, cache,domaineventhandler]\n" );
}
