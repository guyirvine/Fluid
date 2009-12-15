<?php

if ( $_SERVER['argc'] < 3 )
	die( "fluidbuilder pc [list,grid,search]\n" );

switch ( $_SERVER['argv'][2] )  {
	case 'search':
		include getAbsoluteFluidSrcDirectory() . "/pc/search.php";
		break;
	case 'list':
		include getAbsoluteFluidSrcDirectory() . "/pc/list.php";
		break;
	case 'grid':
		include getAbsoluteFluidSrcDirectory() . "/pc/grid.php";
		break;
	default:
		die( "fluidbuilder pc [list,grid]\n" );
}
