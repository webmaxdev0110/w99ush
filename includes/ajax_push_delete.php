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
	if ($table_name !== 'options') {
		$allVals[] = $table_name;
		$allTypes[] = 100;
	}
}

$allVals[] = 'upload';
$allTypes[] = 98;	// copy upload folder

echo '{"value":'.json_encode($allVals).', "type":'.json_encode($allTypes).'}';

?>