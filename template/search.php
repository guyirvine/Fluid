<?php
require_once '_pc.php';


open_page( '===Name=== Search' );


?>

<div>
	<form id='form' method='POST' onSubmit='runSearch();return false;'>
		<input type='text' id='criteria'>&nbsp;<input type='submit' value='Search'>
	</form>

	<div  id='search-result'>
	</div>


<script>
	function navbar( start, line_count ) {
		url = '===name===_search_result.php?criteria=' + $F( 'criteria' ) + '&start=' + start + '&line_count=' + line_count;
		new Ajax.Updater( 'search-result', url );
		return false;
	}

	function runSearch() {
		navbar( 0, 10 );
	}

</script>

<?php
close_page();
