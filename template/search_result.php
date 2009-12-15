<?php
require_once '_pc.php';
require_once 'Fluid/Ui/NavBar.php';

function get_search_list( Fluid_Db $db, $user_id, $criteria, $start, $line_count ) {
        $sql = "SELECT t.id, t.name, t.description, t.created, " .
			"FROM ===name===_tbl t " .
               		"JOIN ===name===_access_vw a ON ( t.project_id = a.project_id ) " .
			"WHERE a.user_id = $1 " .
			"AND UPPER( t.description ) LIKE $2 " .
			"GROUP BY t.id, t.name, t.created " .
			"ORDER BY t.created " .
			"";


        $params = array( $user_id, $criteria );
    
        return $db->queryForSearch( $sql, $params, $start, $line_count );
}
$start=$_GET['start'];
$line_count=$_GET['line_count'];


$criteria = str_replace( " ", "%", g( 'criteria' ) );
$criteria = "%" . strtoupper( $criteria ) . "%";
list( $list, $total_count ) = get_search_list( $connection, $GLOBALS['loggedInUser']['id'], $criteria, $start, $line_count );

?>
<div id='fluid-search'>
<div id='fluid-multi-column-list'>
	<table cellspacing='1' cellpadding='0' style='width: 100%;'>
		<tr>
			<td class='heading'>Project</td>
			<td class='heading'>Title</td>
			<td class='heading'>&nbsp;</td>
		</tr>

		<?php foreach( $list as $row ) { ?>
			<tr>
				<td class='title band-1'><?php print $row['name'] ?></td>
				<td class='description band-2'>
					<?php print $row['description'] ?><br>
					<span class='description-note'>Optional Search Note</span><br>
				</td>
				<td class='link'><a href="===name===.php?id=<?php print $row['id'] ?>">View ===Name=== &raquo;</a></td>
			</tr>
		<?php } ?>
		<tr>
			<td class='footer'>&nbsp;</td>
			<td class='footer'>&nbsp;</td>
			<td class='footer'>&nbsp;</td>
		</tr>
	</table>

	<?php Fluid_Ui_NavBar::draw( $start, $line_count, $total_count, $url ) ?>
</div>
</div>
