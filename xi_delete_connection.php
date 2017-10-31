<?php

	/************************************
	* delete connection ( xiblox menu ) *
	*									*
	* @package 	XIBLOX/					*
	* @type 	hidden					*
	* @author	itabix					*
	************************************/
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	global $wpdb;
	
	$current_user = wp_get_current_user();
	$id = $current_user->ID;
	
	$number = $_POST["liveNumber"];
	
	// delete connection 
	$sql = "DELETE FROM xiblox_destination_info WHERE id = '$number'";
	$res = $wpdb->query( $sql );
	if ( $res != 1 ) {
		echo "<script>
				alert('please try again!');
			  </script>";
		exit();
	}
	
	// update check publish table 
	$sql = "SELECT id, conn_num FROM xiblox_check_publish";
	$res = $wpdb->get_results( $sql, ARRAY_A );
	
	for ( $i = 0; $i < count( $res ); $i++ ) {
		
		if ( $res[$i]["conn_num"] == $number ) {
			
			$sql = "UPDATE xiblox_check_publish SET conn_num = '', check_status = 0 WHERE id = " . $res[$i]["id"];
			$wpdb->query( $sql );
			
		}
		else if ( strpos( $res[$i]["conn_num"], $number ) !== false ) {
		
			$connNum = str_replace( "," . $number, " ", $res[$i]["conn_num"] );
			$sql = "UPDATE xiblox_check_publish SET conn_num = '$connNum' WHERE id = " . $res[$i]["id"];
			$wpdb->query( $sql );
		}
		
	}
	
	// Change Default Push Blox 
	$sql = "SELECT connection_name FROM xiblox_destination_info";
	$res = $wpdb->get_results( $sql, ARRAY_A );
	
	if ( $res[0]["connection_name"] == '' ) 
		$sql = "DELETE  FROM xiblox_tabs WHERE blox_name = 'SitePush'";
	else {
	
		$blox_custom = "||tabs|database:" . $res[0]["connection_name"];
		
		for ( $i = 1; $i < count( $res ); $i++ ) {
			$blox_custom .= "," . $res[$i]["connection_name"];
		}
		
		$blox_custom .= "||";
		$sql = "UPDATE xiblox_tabs SET blox_custom = '" . $blox_custom . "' WHERE blox_name = 'SitePush'";
			
 	}
	
	$res = $wpdb->query( $sql );
	
	// delete connection 
	$sql = "DELETE FROM xiblox_destination_info WHERE id = $number";
	$res = $wpdb->query( $sql );
	
	echo "<script>
			location.href='" . site_url() . "/wp-admin/admin.php?page=XIBLOX/xi_settings.php';
		  </script>";
?>