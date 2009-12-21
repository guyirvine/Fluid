<?php
chdir( '../src' );
require_once '../../../etc/FluidMq.php';

$buffer = <<<EOF
dropdb $mqdbname
createdb -T template0 --encoding unicode $mqdbname

psql -f ../sql/create_tables.sql $mqdbname
EOF;

exec( $buffer );

