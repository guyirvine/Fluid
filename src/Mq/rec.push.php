<?php
$GLOBALS['FluidMq'] = 1;
$GLOBALS['loggedInUser']['id'] = 0;

require_once "/etc/FluidMq.php";
require_once "Fluid/Bus.php";
require_once "_init.php";


$body = file_get_contents( "php://input" );

Fluid_Bus::get()->Receive( $body );


print "Finished<br>\n";
