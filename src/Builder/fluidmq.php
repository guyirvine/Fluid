<?php

if ( $_SERVER['argc'] < 2 )
	die( "fluidbuilder fluidmq\n" );


write_file( "./src/FluidMq.php", file_get_contents( getAbsoluteFluidTemplateDirectory() . "/FluidMq.php" ) );
