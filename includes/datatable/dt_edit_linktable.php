<?php
	
	/**************************************
	* edit the link table 		 		  *
	*							 		  *
	* @package 	XIBLOX/includes/datatable *
	* @author	itabix			 		  *
	**************************************/
	
	require_once( "../../../../../wp-load.php" );
	global $wpdb;
	
	$dt_fields = array( 'text', 'textarea', 'select', 'checkbox' );
	
	$tablename = $_GET['id'];
	
	$sql = "SHOW TABLES";
	$results = $wpdb->get_results( $sql, ARRAY_N );
	
?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="lt_main_form">
	<h2>Please check the link table.</h2>
	<input name="tablename" type="hidden" value="<?php echo $tablename; ?>"/>
	
	<table class="lt_edit" width="100%">
		<tr>
			<th></th>
			<th align="left">Linkable Tables</th>
		</tr>
	<?php 
		foreach ( $results as $result ) { 
		
			if ( strpos( $result[0], "xiblox_" ) !== false || strpos( $result[0], $tablename ) !== false ) 
				continue;
				
			$sql = "SELECT link_table FROM xiblox_link_table WHERE table_name = '" . $tablename . "'";
			$res = $wpdb->get_results( $sql, ARRAY_A );
			
			$linkedTable = explode( ",", $res[0]["link_table"] );
			
			for ( $i = 0; $i < count( $linkedTable ); $i++ ) {
				if ( $result[0] == $linkedTable[$i] ) $flagChecked = 1;
			}
			
	?>
		<tr>
			<td>
				<input type="checkbox" name="<?php echo $result[0]; ?>" value="<?php echo $result[0]; ?>" <?php if ( $flagChecked == 1 ) echo "checked"; $flagChecked = 0; ?>>
			</td>
			<td align="left"><?php echo $result[0]; ?></td>
		</tr>
	<?php } ?>
	</table>
	
	<div class="xiUi-widget-overlay" id="saving_msg">
		<img src="<?php echo plugins_url(); ?>/XIBLOX/images/save_tab.gif" />&nbsp;Saving ...
	</div>
	
</form>