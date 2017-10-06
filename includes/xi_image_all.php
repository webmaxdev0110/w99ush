<?php
	/****************************
	* Push All Selected Items   *
	*		   To Destination	*
	* @package	XIBLOX/includes *
	* @autor	itabix		    *
	****************************/
	
	if ( ! defined( 'ABSPATH' ) ) {
		require_once( "../../../../wp-load.php" );
	}
	
	require_once( "../classes/xi_dbconn_class.php");
	require_once( "../classes/xi_push_class.php" );
	
	
	
	$type= $_GET['type'];
	$value = $_GET['value'];

	// Update pushed status
	$timestamp = date('Y-m-d H:i:s');
	$query = "SELECT * FROM `xiblox_publish_status` WHERE val_id=".$value." AND type_id=".$type;
	if ($wpdb->get_results($query, ARRAY_A)) {
		// already exist
		$query = "UPDATE `xiblox_publish_status` SET `status`='1', `date` = %s WHERE `val_id`=%s AND `type_id`=%s";
		$wpdb->query($wpdb->prepare($query, $timestamp, $value, $type));
	}	
	else {
		// insert
		$query = "INSERT INTO `xiblox_publish_status`(`val_id`, `type_id`, `status`, `date`) VALUES(%s, %s, '1', %s)";
		$wpdb->query($wpdb->prepare($query, $value, $type, $timestamp));
	}

	// end of updating push status
	
	if (isset($_GET['number']) && $_GET['number'] != '') {
		$number = $_GET['number'];	
	}
	else {
		$number = 1;
    }
    
    $hash = base64_decode($_GET['hash']);
	
	// create new instance
	$xi_push = new xiblox_push( $wpdb, $number );
	
	$path = ABSPATH . "wp-content/uploads";
	$xi_push->copy_directory( 0, $path . "/" . $hash, xiblox_push::$destination_path . "wp-content/uploads/" . $hash );
				
?>