<?php
require_once 'gcis/Gcis.php';
require_once 'gcis/Db/Pgsql.php';
require_once '/etc/gcis/mq.php';


$old_error_handler = set_error_handler("gcis_error_handler");

