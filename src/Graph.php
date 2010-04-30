<?php
require_once 'Fluid/Date.php';


class Fluid_Graph {

	public $total_width;
	public $total_height;
	public $margin;
	
	public $steps_x_major;
	public $steps_x_major_length;
	public $steps_x_minor;
	public $steps_x_minor_length;

	public $steps_y_major;
	public $steps_y_major_length;
	public $steps_y_minor;
	public $steps_y_minor_length;
	
	public $title;

	public $buffer;
	
	public $currentDataIndex;
	public $colours;
	
	public $scale;
	
	function reset() {
		$this->total_width=800;
		$this->total_height=300;
		$this->margin=50;

		$this->steps_x_major = 5;
		$this->steps_x_minor = 10;
		$this->steps_y_major = 5;
		$this->steps_y_minor = 10;


		$this->steps_x_major_length = 5;
		$this->steps_x_minor_length = 3;
		$this->steps_y_major_length = 5;
		$this->steps_y_minor_length = 3;

		$this->title = 'Quite a large testing Title';

		$this->currentDataIndex = 0;
		$this->colours = array( 'green', 'red', 'blue' );
		
		$this->scale = null;
	}

	function __construct() {
		$this->reset();
	}

	function _open() {
		$buffer = <<<EOF
<?xml version="1.0" encoding="UTF-8" standalone="no"?>

<svg
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:xlink="http://www.w3.org/1999/xlink"
   width="{$this->total_width}"
   height="{$this->total_height}"
   version="1.0">\n
EOF;

		if ( !is_null( $this->scale ) )
			$buffer .= "<g transform='scale({$this->scale})'>\n";

		$this->buffer .= $buffer;
	}
	function _close() {
		if ( !is_null( $this->scale ) )
			$this->buffer .= "</g>\n";

		$this->buffer .= "</svg>";
	}

	function drawAxis() {
		$this->buffer .= "<!-- Axis Bars -->\n" .
						"<line x1='{$this->graph['x']}' y1='{$this->graph['y']}' x2='{$this->graph['x']}' y2='{$this->graph['y_2']}' style='stroke:black' />\n" .
						"<line x1='{$this->graph['x']}' y1='{$this->graph['y']}' x2='{$this->graph['x_2']}' y2='{$this->graph['y']}' style='stroke:black' />\n";
		
	}

	function drawStepsX( $number_of_steps, $height=3 ) {
		if ( empty( $number_of_steps ) )
			return;


		$step_x = $this->graph['width'] / $number_of_steps;
		$y_1 = $this->graph['y'];
		$y_2 = $this->graph['y'] + $height;

		$this->buffer .= "<!-- XSteps -->\n";
		for( $x=$this->graph['x'];$x<$this->graph['x_2'];$x=$x+$step_x ) {
			$this->buffer .= "<line x1='$x' y1='$y_1' x2='$x' y2='$y_2' style='stroke:black' />\n";
		}
		$this->buffer .= "<line x1='{$this->graph['x_2']}' y1='$y_1' x2='{$this->graph['x_2']}' y2='$y_2' style='stroke:black' />\n";
	}

	function drawStepsXMinor() {
		$this->drawStepsX( $this->steps_x_minor, $this->steps_x_minor_length );
	}

	function drawStepsXMajor() {
		$this->drawStepsX( $this->steps_x_major, $this->steps_x_major_length );
	}

	function drawStepsY( $number_of_steps, $width ) {
		if ( empty( $number_of_steps ) )
			return;


		$step_y = $this->graph['height'] / $number_of_steps;
		$x_1 = $this->graph['x'];
		$x_2 = $this->graph['x'] - $width;

		$this->buffer .= "<!-- YSteps -->\n";
		for( $y=$this->graph['y'];$y>$this->graph['y_2'];$y=$y-$step_y ) {
			$this->buffer .= "<line x1='$x_1' y1='$y' x2='$x_2' y2='$y' style='stroke:black' />\n";
		}
		$this->buffer .= "<line x1='$x_1' y1='{$this->graph['y_2']}' x2='$x_2' y2='{$this->graph['y_2']}' style='stroke:black' />\n";
	}

	function drawStepsYMinor() {
		$this->drawStepsY( $this->steps_y_minor, $this->steps_y_minor_length );
	}

	function drawStepsYMajor() {
		$this->drawStepsY( $this->steps_y_major, $this->steps_y_major_length );

	}


	function drawYAxisNumeric( $min, $max ) {
		$step = ( $max - $min ) / $this->steps_y_major;
		$step_height = $this->graph['height'] / $this->steps_y_major;

		$this->buffer .= "<!-- Y Axis Labels -->\n";
		$x = $this->graph['x']-5;
		$y = $this->graph['y'];
		$dy = $this->labelHeight * ( .4 );
		$this->buffer .= "<text x='$x' y='$y' text-anchor='end' dy='$dy' font-family='Verdana' font-size='{$this->labelHeight}' fill='blue' >$min</text>\n";
		for( $i=1;$i<$this->steps_y_major;$i++ ) {
			$y = $this->graph['y'] - ( $step_height * $i );

			$label = $step * $i;
			$this->buffer .= "<text x='$x' y='$y' text-anchor='end' dy='$dy' font-family='Verdana' font-size='{$this->labelHeight}' fill='blue' >$label</text>\n";
		}
		$this->buffer .= "<text x='$x' y='{$this->graph['y_2']}' text-anchor='end' dy='$dy' font-family='Verdana' font-size='{$this->labelHeight}' fill='blue' >$max</text>\n";

		$this->drawStepsYMajor();
		$this->drawStepsYMinor();
	}


	function drawXAxisNumeric( $min, $max ) {
		$step = ( $max - $min ) / $this->steps_x_major;
		$step_width = $this->graph['width'] / $this->steps_x_major;

		$this->buffer .= "<!-- X Axis Labels -->\n";
		$x = $this->graph['x'];
		$y = $this->graph['y']+5;
		$dy = $this->labelHeight;
		$this->buffer .= "<text x='$x' y='$y' text-anchor='middle' dy='$dy' font-family='Verdana' font-size='{$this->labelHeight}' fill='blue' >$min</text>\n";
		for( $i=1;$i<$this->steps_x_major;$i++ ) {
			$x = $this->graph['x'] + ( $step_width * $i );

			$label = $step * $i;
			$this->buffer .= "<text x='$x' y='$y' text-anchor='middle' dy='$dy' font-family='Verdana' font-size='{$this->labelHeight}' fill='blue' >$label</text>\n";
		}
		$this->buffer .= "<text x='{$this->graph['x_2']}' y='$y' text-anchor='middle' dy='$dy' font-family='Verdana' font-size='{$this->labelHeight}' fill='blue' >$max</text>\n";

		$this->drawStepsXMajor();
		$this->drawStepsXMinor();
	}


	function drawXAxisLabels() {
		if ( empty( $this->x_axis_labels ) )
			throw new Exception( '$this->x_axis_labels must be set' );

		$number_of_labels = count( $this->x_axis_labels );
		$this->steps_x_major = $number_of_labels;
		$this->drawStepsXMajor();


		$this->buffer .= "<!-- XBar Labels -->\n";
		$dy = $this->labelHeight;
		$index = 0;
		$x_1 = $this->graph['x'];
		$step_x = $this->graph['width'] / $number_of_labels;
		$y = $this->graph['y'];
		foreach( $this->x_axis_labels as $label ) {
			$x = round( $x_1 + $step_x * ( $index + .5 ) );

			$this->buffer .= "<text x='$x' y='$y' dy='$dy' text-anchor='middle' font-family='Verdana' font-size='{$this->labelHeight}' fill='blue' >$label</text>\n";
		
			$index++;
		}

	}


	function drawXAxisDateRange( $starttimestamp, $endtimestamp ) {
		$this->buffer .= "<!-- X Bar Date Labels -->\n";

		$startDate = new Fluid_Date( $starttimestamp );
		$endDate = new Fluid_Date( $endtimestamp );
		$days = $endDate->daysBetween( $startDate );


		$dy = $this->labelHeight;
		$y_1 = $this->graph['y'];
		$y_2 = $this->graph['y'] + $this->steps_x_major_length;
		$y = $y_2;

		$x_1 = $this->graph['x'];
		$step_x = $this->graph['width'] / $days;


		for( $i=0;$i<=$days;$i++ ) {
			$currentDate = $startDate->addDays($i );
			if ( $currentDate->day == 1 ) {
				$x = round( $x_1 + $step_x * $i );

				$label = strftime( "%b", $currentDate->timestamp );

				$this->buffer .= "<text x='$x' y='$y' dy='$dy' text-anchor='middle' font-family='Verdana' font-size='{$this->labelHeight}' fill='blue' >$label</text>\n";
				$this->buffer .= "<line x1='$x' y1='$y_1' x2='$x' y2='$y_2' style='stroke:black' />\n";
			}
		}

	}


	function drawTitle() {
		$x = $this->graph['width'] / 2 + $this->graph['x'];
		$y = $this->graph['y_2'] - 2;
		$this->buffer .= "<!-- Title -->\n" .
						"<text x='$x' y='$y' text-anchor='middle' font-family='Verdana' font-size='{$this->titleHeight}' fill='blue' >{$this->title}</text>\n";
	}


	function drawLine( $id, $list ) {
		$color = $this->colours[$this->currentDataIndex++];
		$buffer = "<path id='$id' style='fill:none;stroke:$color;stroke-width:1;' d='M ";

		foreach( $list as $row ) {
			$x = $this->graph['x'] + $row[0] * $this->graph['width'];
			$y = $this->graph['y'] - $row[1] * $this->graph['height'];

			$buffer .= "$x,$y ";
		}
		$buffer .= "' />\n";


		$this->buffer .= "<!-- Draw Line -->\n" . 
						$buffer;
	}

	function drawBar( $amount ) {
		$color = $this->colours[$this->currentDataIndex++];

		$height = $amount * $this->graph['height'];
		$y = $this->graph['y'] - $height;

		$bar_width = $this->graph['width'] / $this->steps_x_major;

		$split = 8;
		$margin = $bar_width / $split;
		
		$width = $margin * ( $split - 2 );
		$x = $this->graph['x']+$margin;


		$this->buffer .= "<!-- Draw Bar -->\n" . 
						"<rect y='$y' x='$x' height='$height' width='$width' style='fill:$color;fill-opacity:1;stroke:black' />\n";

	}


	public function init() {
		$this->graph['x'] = $this->margin;
		$this->graph['y'] = $this->total_height - $this->margin;
		$this->graph['width'] = $this->total_width - ( $this->margin * 2 );
		$this->graph['height'] = $this->total_height - ( $this->margin * 2 );

		$this->graph['x_2'] = $this->graph['x'] + $this->graph['width'];
		$this->graph['y_2'] = $this->graph['y'] - $this->graph['height'];


		$this->titleHeight=$this->margin * .5;
		$this->labelHeight=$this->margin * .3;


		$this->buffer = "";


		return $this->buffer;
	}


	public function setupLineGraph() {
		$this->_open();
		$this->drawAxis();
		$this->drawTitle();

	}


	public function setupBarGraph() {
		$this->_open();
		$this->drawAxis();

		$this->drawXAxisLabels();
		$this->drawStepsYMajor();
		$this->drawStepsYMinor();


		$this->drawTitle();

	}


	static function demo_line_graph() {
		$line_1 = array( array( .075, .1 ), array( .2, .6 ), array( .4, .4 ), array( .5, .8 ), array( .7, .2 ) );
		$line_2 = array( array( .075, .6 ), array( .2, .4 ), array( .4, .8 ), array( .5, .2 ), array( .7, .1 ) );

		$graph = new Graph();
			$graph->total_width=600;
		$graph->init();
			$graph->steps_x_major = 10;
			$graph->steps_x_minor = 20;
		$graph->setupLineGraph();
			$graph->drawYAxisNumeric( 0, 100 );
			$graph->drawXAxisNumeric( 0, 100 );

			$graph->drawLine( 'line_1', $line_1 );
			$graph->drawLine( 'line_2', $line_2 );
		$graph->_close();
		return $graph->buffer;

	}


	static function demo_line_date_graph() {
		$line_1 = array( array( .075, .1 ), array( .2, .6 ), array( .4, .4 ), array( .5, .8 ), array( .7, .2 ) );
		$line_2 = array( array( .075, .6 ), array( .2, .4 ), array( .4, .8 ), array( .5, .2 ), array( .7, .1 ) );

		$graph = new Graph();
			$graph->total_width=600;
		$graph->init();
		$graph->setupLineGraph();
			$graph->drawYAxisNumeric( 0, 100 );
			$graph->drawXAxisDateRange( strtotime( "1 Jun 2009" ), strtotime( "1 May 2010" ) );

			$graph->drawLine( 'line_1', $line_1 );
			$graph->drawLine( 'line_2', $line_2 );
		$graph->_close();
		return $graph->buffer;

	}


	static function demo_bar_graph() {
		$graph = new Graph();
		$graph->init();
			$graph->x_axis_labels = array( 'Jaim', 'John', 'speaker' );
		$graph->setupBarGraph();
			$graph->drawYAxisNumeric( 0, 100 );
			$graph->drawBar( .7 );
		$graph->_close();
		return $graph->buffer;
	}

}
