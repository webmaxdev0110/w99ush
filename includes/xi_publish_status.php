<?php
	/*****************************************
	* Check the publish status to 			 *
	*	confirm other users doesn't push now *
	*										 *
	* @package 	XIBLOX/						 *
	* @author	itabix						 *
	/****************************************/
	
	
	require_once( "../../../../wp-load.php" );
	
	$start 	= $_GET["start"];
	$end 	= $_GET["end"];
	
	if ( $start == 1 ) {
		// get the flag from xiblox_push_status table
		$sql = "SELECT * FROM xiblox_push_status";
		$res = $wpdb->get_results( $sql, ARRAY_A );
		
		$flag = $res[0]["flag"]; // flag to display the publish status
		
		if ( $flag != 1 ) {
		
			echo 0; // no pushing.
			
			// check init query executed ( init query is to set flag as any value; current value is 2 )
			if ( $flag == 0 ) 
				$sql = "INSERT INTO xiblox_push_status ( flag ) VALUES ( 1 )";
			else 
				$sql = "UPDATE xiblox_push_status SET flag = 1";
				
			$wpdb->query( $sql );
			
		} else {
		
			echo 1; // now pushing.
			
		}
	}
	
	if ( $end == 1 ) {
		
		$sql = "UPDATE xiblox_push_status SET flag = 2";
		$wpdb->query( $sql );
		
	}
?>