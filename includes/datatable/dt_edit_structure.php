<?php
	
	/**************************************
	* edit datatable structure		 	  *
	*							 		  *
	* @package 	XIBLOX/includes/datatable *
	* @author	itabix			 		  *
	**************************************/
	
	require_once( "../../../../../wp-load.php" );
	global $wpdb;
	
	$tablename = $_GET['id'];
	
	$sql = "SHOW COLUMNS FROM " . $tablename;
	$results = $wpdb->get_results( $sql, ARRAY_N );
	
?>

<input id="name_form" type="hidden" value="<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_update_structure.php"/>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="dt_edit_tb" style="text-align:left">

	Name of the datatable :
	<input type="text" name="table_name" id="table_name" value="<?php echo $tablename; ?>" />
	
	<input type="hidden" name="origin_table_name" id="origin_table_name" value="<?php echo $tablename; ?>" /></br>
	
	<table class="dt_edit">
		<tr>
			<th>Name</th>
			<th>Type</th>
			<th>Length</th>
			<th>Default</th>
			<th>Operation</th>
		</tr>
		<?php
		foreach ( $results as $result ) {
			if ( $result[0] == 'id' ) {
		?>
		<tr>
			<td>
				id<input type="hidden" name="dt_name[]" value="id" /> 
			</td>
			<td>
				INT<input type="hidden" name="dt_type[]" value="int" />
			</td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<?php
			} else {
		?>
		<tr>
			<td>
				<input type="text" name="dt_name[]" value="<?php echo $result[0]; ?>" />            
			</td>
			<td>
				<select name="dt_type[]" >
					<option value="varchar" <?php if ( strstr( $result[1], 'varchar' ) ) echo 'selected="selected"'; ?>>VARCHAR</option>
					<option value="int" <?php if ( strstr( $result[1], 'int' ) ) echo 'selected="selected"'; ?>>INT</option>
					<option value="text" <?php if ( strstr( $result[1], 'text' ) ) echo 'selected="selected"'; ?>>TEXT</option>
				</select>
			</td>
			<td>
				<input type="text" name="dt_length[]" />            
			</td>
			<td>
				<input type="text" name="dt_default[]" value="<?php echo $result[4]; ?>" />            
			</td>
			<td>
				<a class="delete_col"><img src="<?php echo plugins_url(); ?>/XIBLOX/images/delete.png" title="Delete" width="12" height="12" class="delete_icon"></a>       
			</td>
		</tr>
		<?php
			}
		}
		?>
	</table>
	
	<div class="xiUi-widget-overlay" id="saving_msg">
		<img src="<?php echo plugins_url(); ?>/XIBLOX/images/save_tab.gif" />&nbsp;Saving ...
	</div>

</form>

<input type="button" value="Add Row" id="add_edit_row" />

