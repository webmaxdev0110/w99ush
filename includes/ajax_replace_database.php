<?php

if ( ! defined( 'ABSPATH' ) ) {
	require_once( "../../../../wp-load.php" );
}

require_once( "../classes/xi_dbconn_class.php");
require_once( "../classes/xi_push_class.php" );



global $wpdb;

$allVals = array('');
$allTypes = array('91');

echo '{"value":'.json_encode($allVals).', "type":'.json_encode($allTypes).'}';

?>