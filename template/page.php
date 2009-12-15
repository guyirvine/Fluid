<?php

function _open_page( $name, $title ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title><?php print $name ?></title>

        <link href="main.css" type="text/css" rel="stylesheet" media="all" />
        <link href="fluid.css" type="text/css" rel="stylesheet" media="all" />
        <script src="javascript/prototype.js" type="text/javascript"></script>
        <script src="javascript/scriptaculous.js" type="text/javascript"></script>
        <script src="javascript/effects.js" type="text/javascript"></script>
        <script src="javascript/dragdrop.js" type="text/javascript"></script>
        <script src="javascript/fluid.js" type="text/javascript"></script>

        <?php _html_links() ?>
    </head>
    <body>
        <div id='inlinePopup' style='z-index: 1000;'></div>
        <div id="menu">
            <ul>
                <?php _menu_items() ?>
            </ul>
        </div>
        <div id="system-menu">
            <ul>
                <li><a href=''>Home</a></li>
                <li>|</li>
            </ul>
        </div>
        <div id="container">
            <?php if ( $title != '' ) { print "<h1>$title</h1>"; } ?>
            <div id="container-body">

<?php
}

function _close_page() {
?>
            </div>
        </div>
    </body>
</html>
<?php
}
