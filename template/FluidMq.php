<?php
require_once "Fluid/Mq/Receiver.php";
require_once "/etc/FluidMq.php";
require_once "_init.php";

$GLOBALS['logging'] = 1;


Fluid_Mq_Receiver::Receive();


print "Finished<br>\n";

