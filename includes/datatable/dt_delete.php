<?php
	
	/**************************************
	* delete data table 		 		  *
	*							 		  *
	* @package 	XIBLOX/includes/datatable *
	* @author	itabix			 		  *
	**************************************/
	
	require_once( "../../../../../wp-load.php" );
	global $wpdb;
	
	$tablename = $_GET['table'];

	$sql = "DROP TABLE IF EXISTS $tablename";
	$result = $wpdb->query( $sql );

	$sql = "DROP TABLE IF EXISTS {$tablename}_field";
	$result = $wpdb->query( $sql );
	
?>