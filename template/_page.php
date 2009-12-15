<?php

require_once( 'page.php' );

function _html_links() {
?>
        <link href="main.css" type="text/css" rel="stylesheet" media="all" />
<?php
}

function _menu_items() {
?>
    <li><a href='Fluid/Auth/BasicLogout.php'>logout</a></li>
    <li>|</li>
    <li><a href='index.php'>home</a></li>
<?php
}

function open_page( $title ) {
    _open_page( '===Name===', $title );
}

function close_page() {
    _close_page();
}
