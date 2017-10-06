<?php

if ( ! defined( 'ABSPATH' ) ) {
	require_once( "../../../../wp-load.php" );
}

require_once( "../classes/xi_dbconn_class.php");
require_once( "../classes/xi_push_class.php" );

global $wpdb;

$allVals = array();
$allTypes = array();

$sql = "SHOW TABLES;";
$tables = $wpdb->get_results($sql, ARRAY_A);
foreach ($tables as $table) {
	$table = array_values($table);
	$table_name = str_replace($wpdb->prefix, '', $table[0]);
	$allVals[] = $table_name;
	$allVals[] = $table_name;
	$allTypes[] = 100;
	$allTypes[] = 101;
}
// all theme name
$path = get_theme_root();
if ( $handle = @opendir( $path ) ) {
	while ( false !== ( $entry = readdir( $handle ) ) ) {
		if ( is_dir( $path . "/" . $entry ) ) {
			if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
				$name = $entry;
				$allVals[] = $name;
				$allTypes[] = 3;
			}
		}
	}
}

// all plugin name
$path = ABSPATH . "wp-content/plugins/";
if ( $handle = @opendir( $path ) ) {
	while ( false !== ($entry = readdir($handle))) {
		if ( is_dir( $path . "/" . $entry ) ) {
			if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
				$name = $entry;
				$allVals[] = $name;
				$allTypes[] = 4;
			}
		}
	}
}

$allVals[] = 'upload';
$allTypes[] = 99;	// copy upload folder

echo '{"value":'.json_encode($allVals).', "type":'.json_encode($allTypes).'}';

?>