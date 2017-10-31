<?php
	
	/**************************************
	* update datatable structure 		  *
	*							 		  *
	* @package 	XIBLOX/includes/datatable *
	* @access	from edit local page 	  *
	* @author	itabix			 		  *
	**************************************/
	
	require_once( "../../../../../wp-load.php" );
	global $wpdb;
	
	function ArrayXor( $array_a, $array_b ) {
	
		$union_array = array_merge( $array_a, $array_b );
		$intersect_array = array_intersect( $array_a, $array_b );
		
		return array_diff( $union_array, $intersect_array );
		
	}
	
	// get the changed table name and content
	$orgin_tbname = $_REQUEST['origin_table_name'];
	$new_name = $_REQUEST['table_name'];
	
	if ( $orgin_tbname != $new_name ) {
	
		$sql = "RENAME TABLE {$orgin_tbname} TO {$new_name},{$orgin_tbname}_field TO {$new_name}_field";
		echo $wpdb->query( $sql );
		
	}

	$sql = "SHOW COLUMNS FROM " . $orgin_tbname;
	$columns_arr = $wpdb->get_results( $sql, ARRAY_N );

	$columns = array();

	foreach ( $columns_arr as $column_item ) {
		$columns[0][] = $column_item[0];
		$columns[1][] = $column_item[1];
	}

	print_r($columns);
	
	$table_names = $columns[0];
	$cols_arr = array();
	
	foreach ( $table_names as $col_name ) {
		$cols_arr[] = $col_name;
	}

	$differ = ArrayXor( $_REQUEST['dt_name'], $cols_arr );
	
	print_r($differ);
	
	if ( count( $differ ) != 0 ) {
	
		$sql_arr = array();
		foreach ( $differ as $col ) {
		
			if ( $col != '' ) {
			
				if ( !in_array( $col, $cols_arr ) ) {
				
					$key = array_search( $col, $_REQUEST['dt_name'] );
					$dt_type = $_REQUEST['dt_type'][$key];
					$dt_length = $_REQUEST['dt_length'][$key];
					$dt_default = $_REQUEST['dt_default'][$key];
					
					if ( $dt_type == 'varchar' && $dt_length == '' ) 
						$dt_length = 256;
						
					if ( $dt_type == 'int' && $dt_length == '' ) 
						$dt_length = 11;
						
					if ( $dt_default == '' )
						$dt_default = 'NULL';
						
					$sql_arr[] = 'ADD `' . $col . '` ' . $dt_type . '( ' . $dt_length . ' ) NOT NULL';
					
				} else 
					$sql_arr[] = 'DROP `' . $col . '`';
			}
			
		}
		
		$sql = implode( ',', $sql_arr );
		$sql = 'ALTER TABLE `' . $new_name . '` ' . $sql;
		
		$wpdb->query( $sql );
	}

	$common = array_intersect( $_REQUEST['dt_name'], $cols_arr );

	foreach ( $common as $col ) {
	
		$index1 = array_search( $col, $_REQUEST['dt_name'] );
		$index2 = array_search( $col, $columns[0] );
		
		if ( strpos( $columns[1][$index2], $_REQUEST['dt_type'][$index1]) === false ) {
			$sql = 'ALTER TABLE `' . $new_name . '` MODIFY `' . $col . '` ' . $_REQUEST['dt_type'][$index1] . ' NOT NULL';
			echo $sql;
			$wpdb->query( $sql );
		}
		
	}
?>