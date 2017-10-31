<?php
	
	/**************************************
	* update linktable	 		 		  *
	*							 		  *
	* @package 	XIBLOX/includes/datatable *
	* @author	itabix			 		  *
	**************************************/
	
	require_once( "../../../../../wp-load.php" );
	global $wpdb;
	$tablename = $_REQUEST['tablename'];
	
	$sql = "SELECT count(table_name) as cnt FROM xiblox_link_table";
	$result = $wpdb->get_results( $sql, ARRAY_N );
	
	if ( $result[0]["cnt"] > 1 ) 
		$result = $wpdb->query( "UPDATE xiblox_link_table SET link_table='" . $query . "' WHERE table_name='" . $tablename . "'" );
	else {
		$sql = "SHOW TABLES";
		$results = $wpdb->get_results( $sql, ARRAY_N );
		
		foreach ( $results as $result ) {
		
			if ( strpos( $result[0], "xiblox_" ) !== false ) 
				continue; 
			
			$res = $wpdb->insert( "xiblox_link_table", array( 'table_name' => $result[0], 'link_table' => "" ) );
		}
		
		$query = "";
		
		$sql = "SHOW TABLES";
		$results = $wpdb->get_results( $sql, ARRAY_N );
		
		foreach ( $results as $result ) {
		
			$linkTable = $_REQUEST[$result[0]];
			
			if ( $linkTable != "" ) 
				$query .= $linkTable . ",";
				
		}
		
		$result = $wpdb->query( "UPDATE xiblox_link_table SET link_table='" . $query . "' WHERE table_name='" . $tablename . "'" );
	}
?>