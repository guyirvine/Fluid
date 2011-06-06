<?php

class Fluid_Ui_NavBar {

	static function write_link( $page, $line_count ) {
		$start_index = ( $page-1 ) * $line_count;
		return "href='' onclick='navbar( $start_index, $line_count );return false;'";
	}

    static function draw( $start, $page_row_count, $total_count, $max_links=5 ) {
        if ( $page_row_count > 0 && (integer)$total_count > (integer)$page_row_count ) {
            print "<td>";

            $tempCount = $start;
            $current_page = 0;
            while( $tempCount > 0 ) {
                $current_page++;
                $tempCount -= $page_row_count;
            }
            $current_page++;

            $page_count = ceil( $total_count / $page_row_count );

            if ( ( $page_count - $current_page ) < $max_links ) {
                $first_page = $page_count - $max_links + 1;
            } else {
                $first_page = $current_page - 2;
            }

            $first_page = ( $first_page < 1 ) ? 1 : $first_page;

            $links = array();
            $curr = $first_page;
            $i = 0;
            while ( $i < $max_links &&
                        $curr <= $page_count ) {
                $links[$i++] = array( "index"=>$curr, "label"=>$curr, "url"=> self::write_link( $curr, $page_row_count ) );
                $curr++;
            }

		$from = $start+1;
		$to = min( $start+$page_row_count, $total_count );
            print "<div id='navbar'>\n" .
                        "<table cellpadding='0' cellspacing='0'>\n" .
                            "<tr>\n" .
                                "<td class='label'>Displaying Results $from to $to of $total_count</td>";

            print "<td class='nav'><a " . self::write_link( 1, $page_row_count ) . ">&laquo;</a></td>";

            if ( $current_page > 1 ) {
                print "<td class='nav'><a " . self::write_link( $current_page-1, $page_row_count ) . ">&lsaquo;</a></td>";
            } else {
                print "<td class='nav'>&nbsp;</td>";
            }

            foreach( $links as $link ) {
                if ( $link["index"] == $current_page ) {
                    print "<td class='nav selected'>" . $link["label"] . "</td>";
                } else {
                    print "<td class='nav'><a " . $link["url"] . ">" . $link["label"] . "</a></td>";
                }
            }

            if ( $current_page < $page_count ) {
                print "<td class='nav'><a " . self::write_link( $current_page+1, $page_row_count ) . ">&rsaquo;</a></td>";
            } else {
                print "<td class='nav'>&nbsp;</td>";
            }

            print "<td class='nav'><a " . self::write_link( $page_count, $page_row_count ) . ">&raquo;</a></td>";

            print "			</tr>\n" .
                        "</table>\n" .
                    "</div>\n" .
                    "";
            print "</td>";
        }

    }

}
