<?php
require_once "_pc.php";
require_once "Fluid/Ui/List.php";
require_once "Fluid/Date.php";


function get_list( Fluid_Db $db, $user_id ) {
        $sql = "SELECT t.id, t.name, " .
			"FROM ===name===_tbl t " .
               		"JOIN ===name===_access_vw a ON ( t.project_id = a.project_id ) " .
               "WHERE a.user_id = $1 " .
               "ORDER BY t.id " .
               "";

        return $db->queryForResultSet( $sql, array( $user_id ) );
}


function build_row( $row ) {

	$description = nl2br( $row['description'] );
	$===name===_id = $row['id'];

	$created = Fluid_Date::fromString( $row['created'] )->toString();
	$return_description =<<<EOF
$description<br>
<br>
<table style='width: 100%'>
	<tr>
		<td>
<a href="fragment/===name===/$===name===_id" class="action" onclick="showInlinePopup( 'fragment/===name===.php?===name===_id=$===name===_id' );return false;">edit</a>
		</td>
		<td style='text-align: right;color: #777777;' class='unit'>
			$created
		</td>
	</tr>
</table>
EOF;

	$name = $row['name'];
	$offset= $row['offset'];
	$title=fluid_substr_on_word_boundary( $description, 45 );

	return Fluid_Ui_List::build_row( $name, $offset, $title, $return_description, $===name===_id );
}


$_list = get_list( $connection, $user_id );
$list = array();
foreach( $_list as $row )
	$list[] = build_row( $row );


open_page( '===Name===' );


Fluid_Ui_List::draw( $list );

?>
<br>
<a href="fragment/===name===/" class="action" onclick="showInlinePopup( 'fragment/===name===.php' );return false;">Add a New ===Name===</a>
<script>

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


<?php if ( isset( $_GET['id'] ) ) { ?>
    showInlinePopup( 'fragment/===name===.php?id=<?php print $_GET['id'] ?>' );
<?php } ?>
</script>

<?php
close_page();
