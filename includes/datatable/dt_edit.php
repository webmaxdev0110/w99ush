<?php
	
	/**************************************
	* edit data table 		 		  	  *	
	*							 		  *
	* @package 	XIBLOX/includes/datatable *
	* @author	itabix			 		  *
	**************************************/
	
	require_once( "../../../../../wp-load.php" );
	global $wpdb;
	
	$dt_fields = array( 'text', 'textarea', 'select', 'checkbox' );
	
	$tablename = $_GET['id'];
	
	$sql = "SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE table_name = '".$tablename."'";
	$results = $wpdb->get_results($sql, ARRAY_N);
	
?>
<input id="name_form" type="hidden" value="<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_update.php"/>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="dt_main_form">

	<input name="tablename" type="hidden" value="<?php echo $tablename; ?>"/>
	
	<table class="dt_edit" width="100%">
	<?php
	foreach ( $results as $result ) {
	
		if ( $result[0] != 'id' ) {
		
			$field = $result[0];
			$sql = "SELECT * FROM {$tablename}_field WHERE field_name LIKE '" . $field . "'";
			$field_option = $wpdb->get_results( $sql, ARRAY_A );
	?>
		<tr>
			<td>
				<input type="hidden" name="<?php echo $field; ?>_option[]" value="" /><?php echo $result[0]; ?>
			</td>
			<td>
				<select name="<?php echo $field; ?>_option[]" class="dt_option" >
				<?php
					foreach ( $dt_fields as $dt_field ) {
				?>
						<option value="<?php echo $dt_field ?>" <?php if ( $dt_field == $field_option[0]['field_type'] ) echo 'selected'; ?>><?php echo $dt_field ?></option>
				<?php
					}
				?>
				</select>
			</td>
			<td>
				<textarea id="field_option" name="<?php echo $field; ?>_option[]"><?php echo $field_option[0]['field_options']; ?></textarea>
				<div id="check_option" <?php if ( $field_option[0]['field_type'] != 'checkbox' ) echo 'style="display:none;"'; ?> >
					<table id="check_opt">
						<tr>
							<th>Checked</th>
							<th>Unchecked</th>
						</tr>
						<?php
							if ( $field_option[0]['field_type'] == 'checkbox' ) {
								$s = substr( $field_option[0]['field_options'], 8 );
								$t = substr( $s, 0, -2 );
								$arr = explode( ':', $t );
						?>
						<tr>
							<td><input type="text" class="opt_names" value="<?php echo $arr[0]; ?>" /></td>
							<td><input type="text" class="opt_values" value="<?php echo $arr[1]; ?>" /></td>
						</tr>
						<?php
							} else {
						?>
						<tr>
							<td><input type="text" class="opt_names" /></td><td><input type="text" class="opt_values" /></td>
						</tr>
						<?php
							}
						?>
					</table>
				</div>
				<div id="select_option" <?php if ( $field_option[0]['field_type'] != 'select' ) echo 'style="display:none;"'; ?>>
					<table id="option_add">
						<tr>
							<th>Option Name</th>
							<th>Value</th>
						</tr>
						<?php
						
							if ( $field_option[0]['field_type'] == 'select' ) {
							
								$s = substr( $field_option[0]['field_options'], 8 );
								$t = substr( $s, 0, -2 );
								$arr = explode( ';', $t );
								foreach ( $arr as $a ) {
									$ss = explode( ':', $a );
						?>
						<tr>
							<td><input type="text" class="opt_names" value="<?php echo $ss[0]; ?>" /></td>
							<td><input type="text" class="opt_values" value="<?php echo $ss[1]; ?>" /></td>
						</tr>
						<?php
								}
							}
						?>
						<tr>
							<td><input type="text" class="opt_names" /></td><td><input type="text" class="opt_values" /></td>
						</tr>
					</table>
					<input type="button" class="opt_add" value="Add Option" />
				</div>
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