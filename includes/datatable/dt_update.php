<?php
	
	/**************************************
	* update data table 		 		  *
	*							 		  *
	* @package 	XIBLOX/includes/datatable *
	* @author	itabix			 		  *
	**************************************/
	
	require_once( "../../../../../wp-load.php" );
	global $wpdb;
	
	$tablename = $_REQUEST['tablename'];
	
	$sql = "SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE table_name = '" . $tablename . "'";
	$results = $wpdb->get_results( $sql, ARRAY_N );

	foreach ( $results as $result ) {
	
		$field = $result[0];
		$post_field = $field.'_option';
		
		$field_type = $_REQUEST[$post_field][1];
		$field_option = $_REQUEST[$post_field][2];
		
		if ( $field_type == 'textarea' && $field_option == '' ) 
			$field_option = '{rows:"2",cols:"20"}';
			
		if ( $field_type != '' || $field_type != 'text' ) {
			$sql = "SELECT count(*) FROM {$tablename}_field WHERE field_name LIKE '" . $field . "'";
			$ret = $wpdb->get_results( $sql, ARRAY_N );
			
			if ( $ret[0][0] != 0 )
				$sql = "UPDATE {$tablename}_field SET field_type = '" . $field_type . "', field_options = '" . $field_option . "' WHERE field_name LIKE '" . $field . "'";
			else {
				$sql = "INSERT INTO {$tablename}_field ( field_name, field_type, field_options ) VALUES ('" . $field . "','" . $field_type . "','" . $field_option . "')";
			}
			
			$wpdb->query( $sql );
		}
		
	}
	
	echo $sql;
?>