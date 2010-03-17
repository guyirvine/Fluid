<?php

class Fluid_Date
{
	const FORMAT_DATE = "%d %b %Y";
	const FORMAT_DATETIME = "%d %b %Y %H:%M";
	const DISPLAY_FORMAT_DATETIME = "%e %b %Y %l:%M%P";
	const DISPLAY_FORMAT_TIMESTAMP = "%e %b %Y %l:%M:%S%P";


    const SECONDS_IN_ONE_HOUR = 3600;
    const SECONDS_IN_THREE_HOURS = 10800;
    const SECONDS_IN_ONE_DAY = 86400;

    private $timestamp;
    private $internal_timestamp;
    
    private $localtime;

    static function getMonthNameList() {
        static $list = array( '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
        
        return $list;
    }

	static function getMonthName( $month_number ) {
		$list = self::getMonthNameList();
		return $list[$month_number];
	}

	static function fromString( $string ) {
		$_string = empty( $string ) ? null : strtotime( $string );
		return new Fluid_Date( $_string );
	}

	static function today() {
		list( $tm_sec, $tm_min, $tm_hour, $tm_mday, $tm_mon, $tm_year, $tm_wday, $tm_yday, $tm_isdst ) = localtime();
		$tm_mon += 1;
		$tm_year += 1900;
		$timestamp = mktime( 0, 0, 0, $tm_mon, $tm_mday, $tm_year );


		return new Fluid_Date( $timestamp );
	}


    function __construct ( $timestamp ) {
		$this->timestamp = ( $timestamp == "" ) ? null : $timestamp;
		$this->localtime = null;
		if ( !is_null( $this->timestamp ) ) {
			$this->internal_timestamp = $this->timestamp;
		}

    }


	function isNullDate() {
		return ( is_null( $this->timestamp ) );
	}

	function addMonths( $amount ) {
		list( $tm_sec, $tm_min, $tm_hour, $tm_mday, $tm_mon, $tm_year, $tm_wday, $tm_yday, $tm_isdst ) = localtime( $this->timestamp );
		$tm_mon += ( $amount + 1 );
		$tm_year += 1900;
		$timestamp = mktime( $tm_hour, $tm_min, $tm_sec, $tm_mon, $tm_mday, $tm_year );


		return new Fluid_Date( $timestamp );
			
	}


	function addMonth() {
		return $this->addMonths( 1 );
	}

	function subtractMonths( $amount ) {
		return $this->addMonths( $amount * -1 );
	}

	function subtractMonth() {
		return $this->subtractMonths( 1 );
	}

	function addYears( $amount ) {
		return $this->addMonths( $amount * 12 );
	}
	function addYear() {
		return $this->addYears( 1 );
	}

	function subtractYears( $amount ) {
		return $this->subtractMonths( $amount * 12 );
	}
	function subtractYear() {
		return $this->subtractYears( 1 );
	}


	function setDay( $dayNumber ) {
		list( $tm_sec, $tm_min, $tm_hour, $tm_mday, $tm_mon, $tm_year, $tm_wday, $tm_yday, $tm_isdst ) = localtime( $this->timestamp );
		$tm_mon += 1;
		$tm_mday = $dayNumber;
		$tm_year += 1900;
		$timestamp = mktime( $tm_hour, $tm_min, $tm_sec, $tm_mon, $tm_mday, $tm_year );


		return new Fluid_Date( $timestamp );

	}

	function setYear( $year ) {
		list( $tm_sec, $tm_min, $tm_hour, $tm_mday, $tm_mon, $tm_year, $tm_wday, $tm_yday, $tm_isdst ) = localtime( $this->timestamp );
		$tm_mon += ( $amount + 1 );
		$tm_year = $year;
		$timestamp = mktime( $tm_hour, $tm_min, $tm_sec, $tm_mon, $tm_mday, $tm_year );


		return new Fluid_Date( $timestamp );

	}

/*
3    [tm_mday] => 3
4    [tm_mon] => 3
5    [tm_year] => 105
*/
	function __get($name) {
	if ( $name == 'timestamp' ) {
		return $this->timestamp;
	}

	if ( is_null( $this->localtime ) ) 
		$this->localtime = localtime( $this->timestamp );

        switch( $name ) {
            case 'day':
//                return strftime( "%d", $this->internal_timestamp );
                return $this->localtime[3];
            case 'dayNumber':
//                return (int)strftime( "%d", $this->internal_timestamp );
                return (int)$this->localtime[3];
            case 'monthNumber':
//                return strftime( "%m", $this->internal_timestamp );
                return ( $this->localtime[4] + 1 );
            case 'monthName':
//                return strftime( "%b", $this->internal_timestamp );
		return self::getMonthName( ( $this->localtime[4] + 1 ) );
            case 'year':
//                return strftime( "%Y", $this->internal_timestamp );
		return $this->localtime[5] + 1900;
            case 'shortYear':
                return substr( $this->year, 2 );


            case 'dayOfWeek':
		//file_put_contents( "/tmp/log.date", "Fluid_Date.__get: $name. strftime called\n", FILE_APPEND );
                return strftime( "%u", $this->internal_timestamp );
            case 'dayName':
		//file_put_contents( "/tmp/log.date", "Fluid_Date.__get: $name. strftime called\n", FILE_APPEND );
                return strftime( "%A", $this->internal_timestamp );
            case 'startOfWeek':
		//file_put_contents( "/tmp/log.date", "Fluid_Date.__get: $name. strftime called\n", FILE_APPEND );
		return $this->subtractDays( $this->dayOfWeek );
            case 'longMonthName':
		//file_put_contents( "/tmp/log.date", "Fluid_Date.__get: $name. strftime called\n", FILE_APPEND );
                return strftime( "%B", $this->internal_timestamp );

            default:
                trigger_error( 'Trying to access property: ' . $name );
        }
    }


	function lessThan( Fluid_Date $compare ) {
		return ( $this->internal_timestamp < $compare->timestamp );
	}

	function greaterThan( Fluid_Date $compare ) {
		return ( $this->internal_timestamp > $compare->timestamp );
	}

	function toString() {
		return strftime( "%e %b %Y", $this->timestamp );
	}

	function addDays( $amount ) {
		list( $tm_sec, $tm_min, $tm_hour, $tm_mday, $tm_mon, $tm_year, $tm_wday, $tm_yday, $tm_isdst ) = localtime( $this->timestamp );
		$tm_mon += 1;
		$tm_mday += $amount;
		$tm_year += 1900;
		$timestamp = mktime( $tm_hour, $tm_min, $tm_sec, $tm_mon, $tm_mday, $tm_year );


		return new Fluid_Date( $timestamp );

	}

	function addDay() {
		return $this->addDays( 1 );
	}
	function subtractDays( $amount ) {
		return $this->addDays( $amount * -1 );
	}
	function subtractDay() {
		return $this->subtractDays( 1 );
	}
	
	function subtractWeeks( $amount ) {
		return $this->subtractDays( $amount * 7 );
	}
	function subtractWeek() {
		return $this->subtractWeeks( 1 );
	}
	
	function addWeeks( $amount ) {
		return $this->addDays( $amount * 7 );
	}
	function addWeek() {
		return $this->addWeeks( 1 );
	}
	
	function firstDayOfMonth() {
		return $this->setDay( 1 );
	}
	function startOfMonth() {
		return $this->firstDayOfMonth();
	}
	function lastDayOfMonth() {
		list( $tm_sec, $tm_min, $tm_hour, $tm_mday, $tm_mon, $tm_year, $tm_wday, $tm_yday, $tm_isdst ) = localtime( $this->timestamp );
		$tm_mon += 2;
		$tm_mday = 0;
		$tm_year += 1900;
		$timestamp = mktime( $tm_hour, $tm_min, $tm_sec, $tm_mon, $tm_mday, $tm_year );


		return new Fluid_Date( $timestamp );
	}
	function equal( Fluid_Date $date ) {
		return ( $this->internal_timestamp == $date->timestamp );
	}
	function equals( Fluid_Date $date ) {
		return ( $this->internal_timestamp == $date->timestamp );
	}
	
	function daysBetween( Fluid_Date $date ) {
		return round( abs( $this->internal_timestamp - $date->timestamp ) / self::SECONDS_IN_ONE_DAY );
	}

	function startOfWeek() {
		return $this->subtractDays( $this->dayOfWeek );
	}


	function between( Fluid_Date $dateFrom, Fluid_Date $dateTo ) {
		if ( $dateFrom->timestamp > $dateTo->timestamp ) {
			$fromTimestamp = $dateTo->timestamp;
			$toTimestamp = $dateFrom->timestamp;
		} else {
			$fromTimestamp = $dateFrom->timestamp;
			$toTimestamp = $dateTo->timestamp;
		}
			
		return ( $this->internal_timestamp >= $fromTimestamp &&
					$this->internal_timestamp <= $toTimestamp );
	}
	
	function midnight() {
		list( $tm_sec, $tm_min, $tm_hour, $tm_mday, $tm_mon, $tm_year, $tm_wday, $tm_yday, $tm_isdst ) = localtime( $this->timestamp );
		$tm_mon += 1;
		$tm_year += 1900;
		$timestamp = mktime( 0, 0, 0, $tm_mon, $tm_mday, $tm_year );

		return new Fluid_Date( $timestamp );
	}
}
