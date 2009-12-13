<?php
require_once 'Fluid/Abstract.php';


abstract class Fluid_Cache
	extends Fluid_Abstract {


	function _Put( $key, $data ) {
		$this->Expire( $key );

		$sql = "INSERT INTO cache_tbl( key, created, data ) VALUES ( $1, $2, $3 ) ";
		$params = array( $key, 
						strftime( "%e %b %Y %r" ), 
						serialize( $data ) );
	
		$this->connection->execute( $sql, $params );
	}


	function Expire() {
		$params = func_get_args();
		$key = call_user_func_array(array($this, "MakeKey"), $params);

		$this->connection->execute( $connection, "DELETE FROM cache_tbl WHERE key = $1", array( $key ) );
	}


	function Get() {
		try {
			$params = func_get_args();
			$key = call_user_func_array(array($this, "MakeKey"), $params);


			$_data = $this->connection->queryForValue( "SELECT data FROM cache_tbl WHERE key = $1", array( $key ) );
			return unserialize( $_data );

		} catch ( NoDataFoundException $e ) {
			$data = call_user_func_array(array($this, "Regenerate"), $params);
		}
		
		
		return $data;
	}







	function MakeKey( Year $year, $id ) {
		return "bu-$id-" . $year->startDate->timestamp;
	}

	function Regenerate( Year $year, $id ) {
		$params['businessunit_id'] = $id;
		$bu = Gcis_Service::get( $connection, $GLOBALS['loggedInUser']['id'], 'IBusinessUnit_WithMobList', $params );
		$mob_list = array();

		$startDate = $year->startDate;
		$endDate = $year->endDate;
		$cache_list = array();
		$cache_list['mob'] = array();
		foreach( $bu->getMobListForDate( $startDate ) as $mob ) {
			$cache_list['mob'][$mob->id] = Cache_Mob::getFromCache( $connection, $mob->id );
		}

		$startTimestamp = $year->startDate->timestamp;
		$endTimestamp = $year->endDate->timestamp;
		$list = array();
		$dm_eaten = 0;
		foreach( $cache_list['mob'] as $mob_id=>$mob_list ) {
			foreach( $mob_list as $row ) {
				if ( $row['timestamp'] >= $startTimestamp &&
					$row['timestamp'] <= $endTimestamp ) {

					if ( !isset( $row['dm_eaten_per_day_all_animals'] ) ) {
						print_r( $row );
						throw new Exception();
					}
					$dm_eaten += $row['dm_eaten_per_day_all_animals'];
					
					if ( !isset( $mob_list[$mob_id] ) &&
							$row['animal_count'] > 0 ) {
						$mob_list[$mob_id] = 1;
					}

				}
			}
		}


		$data['dm_eaten'] = $dm_eaten;
		$data['mob_list'] = $mob_list;
		$key = $this->MakeKey( $year, $id );
		$this->Put( $key, $data );
		
		return $data;
	}



}

