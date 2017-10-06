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

	$type= $_GET['type'];
	$value = $_GET['value'];

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
	$state = false;

	if (in_array($type, array(0,1,2))) {
		$state = $xi_push -> check_state( $value );
	}
	else if ($type == 5) {
		$state = $xi_push -> check_user_state($value);
	}
	else if ($type == 10) {
		$state = $xi_push -> check_blox_state($value);
	}

	if ($state == true) {
		$status = 0;
	}
	else {
		$status = 1;
	}

	$timestamp = date('Y-m-d H:i:s');
	$query = "SELECT * FROM `xiblox_publish_status` WHERE val_id=".$value." AND type_id=".$type;
	if ($wpdb->get_results($query, ARRAY_A)) {
		// already exist
		$query = "UPDATE `xiblox_publish_status` SET `status`=%s, `date` = %s WHERE `val_id`=%s AND `type_id`=%s";
		$wpdb->query($wpdb->prepare($query, $status, $timestamp, $value, $type));
	}	
	else {
		// insert
		$query = "INSERT INTO `xiblox_publish_status`(`val_id`, `type_id`, `status`, `date`) VALUES(%s, %s, %s, %s)";
		$wpdb->query($wpdb->prepare($query, $value, $type, $status, $timestamp));
	}

	
	// switch( $type ) {
	
	// 	case 0:

	// 		// copy posts and pages
	// 		if ( $value != "" )
	// 			$state = $xi_push->copy_only_posts( $value );
	// 		break;

	// 	case 1:
	// 		// copy links
	// 		if ( $value != "" )
	// 			$state = $xi_push->copy_links( $value );
	// 		break;
			
	// 	case 2:
	// 		// copy media
	// 		if ( $value != "" )
	// 			$xi_push->copy_attachment( $value );
	// 		break;
			
	// 	case 3:
	// 		// copy theme
	// 		if ( $value != "" )
	// 			$xi_push->copy_theme( $value );
	// 		break;
			
	// 	case 4:
	// 		// copy plugin
	// 		if ( $value != "" )
	// 			$xi_push->copy_plugin( $value );
	// 		break;
			
	// 	case 5:
	// 		// copy users
	// 		if ( $value != "" )
	// 			$xi_push->copy_user( $value );
	// 		break;
			
	// 	case 6:
	// 		// copy selected table
	// 		if ( $value != "" )
	// 			$xi_push->copy_table( $value );
	// 		break;
			
	// 	case 7:
	// 		// delete all database tables
	// 		$xi_push->delete_all_posts();
	// 		break;
			
	// 	case 71:
	// 		// delete all database tables except option, user, usermeta table
	// 		$xi_push->delete_posts();
	// 		break;
			
	// 	case 8:
	// 		// delete directory
	// 		if ( $value != "" ) {

	// 			$destination_path = xiblox_push::$destination_path;
				
	// 			if ( strpos( $value, $destination_path ) !== false )
					
	// 				$xi_push->delete_dir( 0, $value );	//deleting file mode
					
	// 		}
			
	// 		break;
			
	// 	case 9:
	// 		// copy the database and the directory inside wp-content
	// 		echo "<b>Copying all data to other database</b><br>";
	// 		$xi_push->copy_db();
	// 		break;
			
	// 	case 91:
	// 		// copy only the database
	// 		$xi_push->copy_db_except_wpcontent();
	// 		break;
			
	// 	case 10:
	// 		// copy the blox ( xiblox tab )
	// 		if ( $value != "" ) 
	// 			$xi_push->copy_blox( $value );
	// 		break;
			
	// 	default:
	// 		break;
	// }
?>