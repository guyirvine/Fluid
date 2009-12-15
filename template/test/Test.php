<?php
require_once '../test/init.php';

class Test_===Name===
	extends PHPUnit_Framework_TestCase
{

	public function setUp() {
		$_POST=array();
		f()->connection->execute( "BEGIN", array() );
		f()->connection->execute( "INSERT INTO ===name===_tbl( id, name ) VALUES ( -1, 'pre-name' )", array() );

	}
	public function tearDown() {
		f()->connection->execute( "ROLLBACK", array() );
	
	}

	public function testUpdate===Name===() {
		$_SERVER['REQUEST_URI'] = '/www/===name===.php';
		$_POST['submit'] = 1;
		$_POST['===name===_id'] = -1;

		$_POST['name'] = 'updated name';


		f()->Ajax()->Run();


		$row = f()->connection->queryForArray( "SELECT id, name FROM ===name===_tbl WHERE id = $1", array( $===name===_id ) );
		$this->assertEquals( 'updated name', $row['name'] );


		$this->assertEquals( "===name===.php?===name===_id=$===name===_id", $GLOBALS['redirect'] );

	}


}
