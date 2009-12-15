<?php

function create_directory( $path ) {
	if ( is_dir( $path ) ) {
	} else {
		mkdir( $path );
		print "$path: created\n";
	}

}

function write_file( $path, $content ) {
	if ( is_file( $path ) ) {
	} else {
		file_put_contents( $path, $content );
		print "$path: created.\n";
	}

}


function get_content( $path, $name ) {
	$content = file_get_contents( $path );
	$content = str_replace( "===name===", $name, $content );
	$content = str_replace( "===Name===", ucfirst( $name ), $content );
	
	return $content;
}


function add_fragment( $name ) {
	create_directory( './src/fragment' );
	write_file( './src/fragment/_init.php', file_get_contents( getAbsoluteFluidTemplateDirectory() . "/fragment/_init.php" ) );
	

	write_file( "./src/fragment/$name.php", get_content( getAbsoluteFluidTemplateDirectory() . "/fragment/edit.php", $name ) );

}

function add_domain_object( $name ) {
	$name = ucfirst( $name );
	create_directory( './src/DomainObject' );
	

	write_file( "./src/DomainObject/$name.php", get_content( getAbsoluteFluidTemplateDirectory() . "/DomainObject.php", $name ) );

}

function add_dao( $name ) {
	create_directory( './src/Dao' );
	

	write_file( "./src/Dao/" . ucfirst( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/Dao.php", $name ) );
}

function add_basic_statechangehandler( $name ) {
	create_directory( './src/StateChangeHandler' );
	

	write_file( "./src/StateChangeHandler/Create" . ucfirst( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/StateChangeHandler/Create.php", $name ) );
	write_file( "./src/StateChangeHandler/Update" . ucfirst( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/StateChangeHandler/Update.php", $name ) );
}


function add_basic_ajaxhandler( $name ) {
	create_directory( './src/AjaxHandler' );
	write_file( "./src/AjaxHandler/" . ucfirst( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/AjaxHandler.php", $name ) );


	create_directory( './src/ajax' );
	write_file( "./src/ajax/_init.php", get_content( getAbsoluteFluidTemplateDirectory() . "/ajax/_init.php", $name ) );
	write_file( "./src/ajax/" . strtolower( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/ajax/ajax.php", $name ) );
}


function add_test( $name ) {
	create_directory( './test' );
	write_file( "./test/init.php", get_content( getAbsoluteFluidTemplateDirectory() . "/test/init.php", $name ) );
	write_file( "./test/Suite.php", get_content( getAbsoluteFluidTemplateDirectory() . "/test/Suite.php", $name ) );


	if ( !is_file( "./test/" . ucfirst( $name ) . ".php" ) ) {
		file_put_contents( "./test/Suite.php", get_content( getAbsoluteFluidTemplateDirectory() . "/test/suite_entry.php", $name ), FILE_APPEND );
	}
	write_file( "./test/" . ucfirst( $name ) . ".php", get_content( getAbsoluteFluidTemplateDirectory() . "/test/Test.php", $name ) );

}

function add_domain_concept( $name ) {
	add_domain_object( $name );
	add_dao( $name );
	add_basic_statechangehandler( $name );
	add_basic_ajaxhandler( $name );

	add_test( $name );
}
