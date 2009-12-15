<?php
require_once '_init.php';


function get( Fluid_Db $db, $user_id, $id ) {
        $sql = "SELECT t.id, " .
			"FROM ===name===_tbl t " .
               		"JOIN ===name===_access_vw a ON ( t.project_id = a.project_id ) " .
               "WHERE a.user_id = $1 " .
	       "AND t.id = $2 " .
               "";

        return $db->queryForArray( $sql, array( $user_id, $id ) );
}


if ( isset( $_GET['===name===_id'] ) ) {
    $row = get( $connection, $user_id, $_GET['===name===_id'] );

    $name = $row['name'];
    $entered = strftime( "%d %b %Y", strtotime( $row['entered'] ) );
    
} else {
    $name = "";
    $entered = strftime( "%d %b %Y" );

}


?>
<div class='inlinePopupTitle'>
  ===Name=== <?php if ( isset( $created ) ) { print ": <span style='font-size: smaller'>$created</span>"; }  ?>
</div>
<div class='inlinePopupContent' style='font-size: 13px;'>
    <form id='form' method="POST" action='===name===.php' onSubmit='return update===Name===();'>
        <?php if ( isset( $_GET['===name===_id'] ) ) { ?>
            <input type='hidden' name='===name===_id' value='<?php print $_GET['===name===_id'] ?>'>
        <?php } ?>

        <fieldset>
            <table style="width: 100%">
                <tr>
					<td class='name' style=''>Date</td>
					<td>
						<input class='date' type="text" name="entered_date" id="entered_date" value='<?php print $entered ?>'/><button type="reset" id="entered_date_trigger">...</button>
					</td>
				</tr>
                <tr><td>&nbsp;</td></tr>

                <tr>
                    <td class='name'>Name</td>
                    <td><input id='name' name='name' type="text" value="<?php print $name ?>" ></td>
				</tr>
                <tr><td>&nbsp;</td></tr>
            </table>
        </fieldset>
        <br>
        
        <table style="width: 100%">
            <tr>
                <td colspan='2' style="text-align: center">
                    <table style="margin: auto">
                        <tr>
                            <?php if ( isset( $_GET['===name===_id'] ) ) { ?>
                            <td><a onclick='confirm( "Confirm delete ?" );' href="===name===.php?delete=<?php print $_GET['===name===_id'] ?>">Delete</a></td>
                            <?php } ?>
                            <td>
                                <input type="hidden" name="submit" value="submit">
                                <input class='submit' type="submit" id="update" name="update" value='Update'>
                            </td>
                            <td><a onclick="return hideInlinePopup( null )" href="">Cancel</a></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </form>
</div>

<script>
    Calendar.setup({
        inputField     :    "entered_date",     // id of the input field
        ifFormat       :    "%d %b %Y",     // format of the input field (even if hidden, this format will be honored)
        showsTime      :    false,            // will display a time selector
        button         :    "entered_date_trigger",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });


</script>
