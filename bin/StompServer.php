<?php
require_once "Fluid/Stomp/Server.php";

$stompServer = new Fluid_Stomp_Server();
$stompServer->run();
