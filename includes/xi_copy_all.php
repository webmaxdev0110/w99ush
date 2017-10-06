<?php
	/****************************
	* Push All To Destination   *
	*						    *
	* @package	XIBLOX/includes *
	* @autor	itabix		    *
	****************************/
	
	if ( ! defined( 'ABSPATH' ) ) {
		require_once( "../../../../wp-load.php" );
	}
	
	require_once( "../classes/xi_dbconn_class.php");
	require_once( "../classes/xi_push_class.php" );
	require_once( "../classes/xi_db_push.php" );

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

	global $wpdb;
	
	// create new instance
	$xi_push = new xiblox_push( $wpdb, $number );
	$xi_db_push = new xiblox_db_push( $wpdb, $number );
	
	switch( $type ) {
		case 0:
			// copy posts and pages
			if ( $value != "" )
				$xi_push->copy_only_posts( $value );
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
			// copy plugin
			if ( $value != "" )
				$xi_push->copy_plugin( $value );
			break;
			
		case 5:
			// copy users
			if ( $value != "" )
				$xi_push->copy_user( $value );
			break;
			
		case 6:
			// copy selected table
			if ( $value != "" )
				$xi_push->copy_table( $value );
			break;
			
		case 7:
			// delete all database tables
			$xi_push->delete_all_posts();
			break;
			
		case 71:
			// delete all database tables except option, user, usermeta table
			$xi_push->delete_posts();
			break;
			
		case 8:
			// delete directory
			if ( $value != "" ) {
				$destination_path = xiblox_push::$destination_path;
				if ( strpos( $value, $destination_path ) !== false )
					$xi_push->delete_dir( 0, $value );	//deleting file mode
			}
			
			break;
			
		case 9:
			// copy the database and the directory inside wp-content
			echo "<b>Copying all data to other database</b><br>";
			$xi_push->copy_db();
			break;
			
		case 91:
			// copy only the database
			$xi_push->copy_db_except_wpcontent();
			break;
		case 20: 
			// copy menu
			if ( $value != "" )
				$xi_push->copy_menu( $value );
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
		default:
			break;
	}
?>