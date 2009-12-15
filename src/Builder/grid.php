<?php

if ( $_SERVER['argc'] < 3 )
	die( "fluidbuilder pc [list,grid]\n" );

switch ( $_SERVER['argv'][2] )  {
	case 'list':
		include getAbsoluteFluidSrcDirectory() . "/pc/list.php";
		break;
	default:
		die( "fluidbuilder pc [list,grid]\n" );
}
