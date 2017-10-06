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
	require_once( "../classes/xi_db_push.php" );
	
	global $wpdb;
	
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
	
	// create new instance
	$xi_push = new xiblox_push( $wpdb, $number );
	$xi_db_push = new xiblox_db_push( $wpdb, $number );
	
	if ( ( $value != 'undefined' ) && ( $type != 'undefined' ) ) {
	
		switch ( $type ) {
		
			case 0:
				// copy posts and pages
				if ( $value != "" )
					$xi_push->copy_posts( $value );
				break;
				
			case 1:
				// copy links
				if ( $value != "" )
					$xi_push->copy_links( $value );
				break;
				
			case 2:
				// copy media
				if ( $value != "" )
					$xi_push->copy_attachment( $value , 0);
				break;
				
			case 3:
				// copy theme
				if ( $value != "" )
					$xi_push->copy_theme( $value );
				break;
				
			case 4:
				// copy plugins
				if ( $value != "" )
					$xi_push->copy_plugin( $value );
				break;
				
			case 5:
				// copy users
				if ( $value != "" )
					$xi_push->copy_user( $value, $number );
				break;
				
			case 20: 
				// copy menu
				if ( $value != "" )
					$xi_push->copy_menu( $value );
				break;
				
			case 21: 
				// copy single menu
				if ( $value != "" )
					$xi_push->copy_menu_item_single( $value );
				break;
			case 98:
				$path = ABSPATH . "wp-content/uploads";
				$xi_push->delete_dir( 0, xiblox_push::$destination_path . "wp-content/uploads/" . $hash );
				break;
			case 99:
				$path = ABSPATH . "wp-content/uploads";
				$xi_push->copy_directory( 0, $path . "/" . $hash, xiblox_push::$destination_path . "wp-content/uploads/" . $hash );
				break;
			case 100:
				if ($value != "") {
					$xi_db_push -> truncate_table($value);
				}
				break;
			case 101:
				if ($value != "") {
					$xi_db_push -> migrate_table($value);
				}
				break;
		}
	}
?>