<?php
function COPY_RECURSIVE_DIRS($dirsource, $dirdest) { 
	exec( "cp -R $dirsource $dirdest" );
}


$parts = explode( "/", getcwd() );
$name = $parts[count( $parts )-1];

$init_content = get_content( getAbsoluteFluidTemplateDirectory() . "/_init.php", $name );
$_pc_content = get_content( getAbsoluteFluidTemplateDirectory() . "/_pc.php", $name );
$_page_content = get_content( getAbsoluteFluidTemplateDirectory() . "/_page.php", $name );
$page_content = get_content( getAbsoluteFluidTemplateDirectory() . "/page.php", $name );
$conf_content = get_content( getAbsoluteFluidTemplateDirectory() . "/conf.php", $name );


if ( !is_dir( './src' ) ) {
	mkdir( './src' );
}

write_file( "./src/_init.php", $init_content );
write_file( "./src/_pc.php", $_pc_content );
write_file( "./src/_page.php", $_page_content );
write_file( "./src/page.php", $page_content );
write_file( "./src/conf.php", $conf_content );

COPY_RECURSIVE_DIRS( getAbsoluteFluidDirectory() . "/javascript", "./src/" );
COPY_RECURSIVE_DIRS( getAbsoluteFluidDirectory() . "/css/*", "./src/" );


create_directory( "./src/DomainObject" );
$user_content = get_content( getAbsoluteFluidTemplateDirectory() . "/User.php", $name );
write_file( "./src/DomainObject/User.php", $user_content );
