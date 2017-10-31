<?php
	
	/**************************************
	* create data table 		 		  *
	*							 		  *
	* @package 	XIBLOX/includes/datatable *
	* @access	from edit local page	  *
	* @author	itabix			 		  *
	**************************************/
	
	require_once( "../../../../../wp-load.php" );
	global $wpdb;

	$table_name = $_REQUEST['table_name'];
	$table_name = "xiblox_" . $table_name;
	
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $table_name . '` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,';
	  
	foreach ( $_REQUEST['dt_name'] as $key => $dt_name ) {
	
		$dt_type = $_REQUEST['dt_type'][$key];
		$dt_length = $_REQUEST['dt_length'][$key];
		$dt_default = $_REQUEST['dt_default'][$key];
		
		if ( $dt_type == 'varchar' && $dt_length == ' ')
			$dt_length = 256;
			
		if (  $dt_type == 'int' && $dt_length == '' )
			$dt_length = 11;
			
		if ( $dt_default == '' )
			$dt_default = 'NULL';
			
		$sql .= '`' . $dt_name . '` ' . $dt_type . '(' . $dt_length . ') NOT NULL,';
	}

	$sql .= 'PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
	';

	$wpdb->query( $sql );
?>