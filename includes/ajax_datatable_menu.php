<?php

require_once("../../../../wp-load.php");

require_once( "../classes/xi_dbconn_class.php");
require_once( "../classes/xi_push_class.php" );

global $wpdb;
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$aColumns = array( 'term_id','name','slug');
	

	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "term_id";

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
	 * no need to edit below this line
	 */
	
	/* 
	 * Local functions
	 */
	function fatal_error ( $sErrorMessage = '' )
	{
		header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
		die( $sErrorMessage );
	}

	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".intval( $_POST['iDisplayStart'] ).", ".
			intval( $_POST['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	$sOrder = "";
	if ( isset( $_POST['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ )
		{
			if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= "`".$aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ".
					($_POST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	/*
	 * Left Join
	 */

	$sJoin = "LEFT JOIN `".$wpdb->base_prefix."term_taxonomy` ON `".$wpdb->base_prefix."terms`.`term_id` = `".$wpdb->base_prefix."term_taxonomy`.`term_id` WHERE `".$wpdb->base_prefix."term_taxonomy`.`taxonomy` = 'nav_menu'";
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" )
	{
		$sWhere = "AND (";
		$sWhere .= "`".$aColumns[1]."` LIKE '%".( $_POST['sSearch'] )."%' OR ";
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}

	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS `".$wpdb->base_prefix."terms`.`term_id`, `".$wpdb->base_prefix."terms`.`name`, `".$wpdb->base_prefix."terms`.`slug`
		FROM  `".$wpdb->base_prefix."terms`
		$sJoin
		$sWhere
		$sOrder
		$sLimit
		";
	$rResult = $wpdb->get_results($sQuery,ARRAY_A);
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS `".$wpdb->base_prefix."terms`.`term_id`, `".$wpdb->base_prefix."terms`.`name`, `".$wpdb->base_prefix."terms`.`slug`
		FROM  `".$wpdb->base_prefix."terms`
		$sJoin
		$sWhere
		$sOrder
		";
	$aResultFilterTotal = $wpdb->get_results($sQuery,ARRAY_A);
	$iFilteredTotal = count($aResultFilterTotal);
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(*)
		FROM  `".$wpdb->base_prefix."terms`
		LEFT JOIN `".$wpdb->base_prefix."term_taxonomy`
		ON `".$wpdb->base_prefix."terms`.`term_id` = `".$wpdb->base_prefix."term_taxonomy`.`term_id` AND `".$wpdb->base_prefix."term_taxonomy`.`taxonomy` = 'nav_menu'";
	$aResultTotal = $wpdb->get_results($sQuery,ARRAY_A);
	$iTotal = $aResultTotal[0]['COUNT(*)'];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_POST['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
	
	foreach ( $rResult as $aRow )
	{
		$row = array();
		$row[0] = "<input type=\"checkbox\" value=\"".$aRow['term_id']."\" name=\"menu[]\" class = \"menu_check\" onclick =  \"row_click(this,'menu')\"/>";
		$row[1] = $aRow['name'];
		$row[2] = $aRow['slug'];
		
		$id = $aRow['ID'];
		$query = "SELECT * FROM `xiblox_publish_status` WHERE `val_id` LIKE '".$id."' AND `type_id` LIKE '20' AND `status`='1'";
		if ($wpdb->get_results($query, ARRAY_A)) {
			$row[3] = 'Pushed';

		}
		else {
			$row[3] = 'Unpushed';
		}
		$row[3] = '-';
		$output['aaData'][] = $row;
	}
	
	echo json_encode( $output );
?>