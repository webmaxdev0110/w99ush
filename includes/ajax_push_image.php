<?php

if ( ! defined( 'ABSPATH' ) ) {
	require_once( "../../../../wp-load.php" );
}

require_once( "../classes/xi_dbconn_class.php");
require_once( "../classes/xi_push_class.php" );

// all plugins
$plugins = [];
$path = ABSPATH . "wp-content/uploads/";
$everything = scandir($path);
foreach ($everything as $entry) {
    if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
        $name = $entry;
        $plugins[] = base64_encode($entry);
    }
}
echo json_encode($plugins);
?>
