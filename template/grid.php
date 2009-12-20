<?php
require_once '_pc.php';
require_once 'Dao/===Name===.php';


function get_list( Fluid_Db $db, $user_id ) {
        $sql = "SELECT t.id, t.name " .
			"FROM ===name===_tbl t " .
               		"JOIN ===name===_access_vw a ON ( t.id = a.===name===_id ) " .
               "WHERE a.user_id = $1 " .
               "ORDER BY t.id " .
               "";

        
        return $db->queryForResultSet( $sql, array( $user_id ) );
}


$list = get_list( $connection, $user_id );

open_page( '===Name===' );

?>
    <div id='filter-div'>
        Filter: <input type='text' id='filter'>
    </div>

<div  id='fluid-multi-column-list'>
	<table cellspacing='1' cellpadding='0' style='width: 100%;'>
		<tr>
			<td class='heading'>Id</td>
			<td class='heading'>Name</td>
			<td class='heading'>&nbsp;</td>
		</tr>

		<?php foreach( $list as $row ) { ?>
			<tr class='filter-tr'>
				<td class='filter-entry band=1'><?php print $row['id'] ?></td>
				<td class='filter-entry band-2'><?php print $row['name'] ?></td>
				<td><a href="fragment/===name===/$===name===_id" class="action" onclick="showInlinePopup( 'fragment/===name===.php?===name===_id=<?php print $row['id'] ?>' );return false;">edit</a></td>
			</tr>
		<?php } ?>
		<tr>
			<td class='footer'>&nbsp;</td>
			<td class='footer'>&nbsp;</td>
			<td class='footer'>&nbsp;</td>
		</tr>
	</table>
</div>
    
<script>
    new Event.observe( 'filter', 'keyup', function( event ) { run_table_filter() } );
    run_table_filter();


    Event.observe(document, 'keyup', function(event){ if(event.keyCode == Event.KEY_INSERT ) { showInlinePopup( "fragment/===name===.php" ); }});

    function validateForm() {
        message  = '';

        message += test_date_data( 'entered_date', "Entered must be a valid date ( dd Mon YYYY )\n", true );

        if ( message == '' ) { return true; }

        alert( message );
        return false;

    }


	function update===Name===() {
		if ( validateForm() ) {
			fluid_ajax_update( "ajax/===name===.php", $( 'form' ).serialize() );
		}


		return false;
	}
</script>

<?php
close_page();
