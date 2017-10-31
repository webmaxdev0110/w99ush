<?php
	
	/****************************************
	* edit local table page ( xiblox menu ) *
	*									    *
	* @package 	XIBLOX/					    *
	* @type 	hidden					    *
	* @author	itabix					    *
	****************************************/
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	global $wpdb;
	
	$submit = $_POST["submit"];
	
	// get the all tables from local
	$sql = "SHOW TABLES";
	$results = $wpdb->get_results( $sql, ARRAY_N );
	
	if ( count( $results ) != 0 ) {
		$count = 0;
		foreach ( $results as $result ) {
			$table[$count] = $result[0];
			$count++;
		}
	}
	
	// get the table description
	for ( $i = 0; $i < $count; $i++ ) {
	
		$sql = "SELECT description FROM xiblox_description WHERE table_name = '" . $table[$i] . "'";
		$result = $wpdb->get_results( $sql, ARRAY_A );
 		
		$description[$table[$i]] = $result[0]["description"];
		$temp[$table[$i]] = $description[$table[$i]];
		
	}
	
	// save the updated content
	if ( $submit == "Save" ) {
	
		for ( $i = 0; $i < $count; $i++ ) {
		
			$description[$table[$i]] = $_POST[$table[$i]];
			
			if ( $temp[$table[$i]] == "" ) {
			
				if ( $description[$table[$i]] == "" ) 
					continue;
					
				$result = $wpdb->insert( "xiblox_description", array( 'table_name' => $table[$i], 'description' => $description[$table[$i]] ) );
				
				if ( $result == false ) { 
					echo "Please try again. If you have same error, then please contact with admin"; 
					exit(); 
				}
				
			} else {
			
				if ( $temp[$table[$i]] == $description[$table[$i]] ) 
					continue;
					
				$result = $wpdb->query( "UPDATE xiblox_description SET description='" . $description[$table[$i]] . "' WHERE table_name='" . $table[$i] . "'");
				
				if ( $result == false ) { 
					echo "Please try again. If you have same error, then please contact with admin"; 
					exit(); 
				}
				
			}
			
		}
		
	}
?>

<script type="text/javascript">

	function EditDialog( id ) {
		jQuery("#edit_dialog").dialog({
			width	:	800,
			height 	: 	600,
			modal	: 	true,
			buttons	: 	[
				{
					text	:	"Save",
					click	:	function() {
						jQuery('.dt_option').each( function( index, element ) {
						
							if ( jQuery(this).val() == 'select' ) {
								var s_string = '{value:"';
								var s_array = [];
								jQuery(this).parent().parent().find('#select_option').find('.opt_names').each( function( index, element ) {
									var opt_name = jQuery(this).val();
									var value = jQuery(this).parent().parent().find('.opt_values').val();
									if ( opt_name != '' && value != '' ) {
										s_array.push( opt_name + ':' + value );
									}
								});
								s_string += s_array.join(';');
								s_string += '"}';
								jQuery(this).parent().parent().find('#field_option').text(s_string);
							}
							else if ( jQuery(this).val() == 'checkbox' ) {
								var check = jQuery(this).parent().parent().find('#check_option').find('.opt_names').val();
								var uncheck = jQuery(this).parent().parent().find('#check_option').find('.opt_values').val();
								if ( check == '' || uncheck == '' ) {
									s_string = '';
								}
								else {
									s_string = '{value:"' + check + ':' + uncheck + '"}';
								}
								jQuery(this).parent().parent().find('#field_option').text(s_string);
								
							}
							else{
								s_string = '';
								jQuery(this).parent().parent().find('#field_option').text(s_string);
							}
						});
						jQuery("#saving_msg").show();
						jQuery(this).attr("disabled","disabled");
						var action = "<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_update.php";
						jQuery.post(
							action, 
							jQuery('#dt_main_form').serialize(),
							function() {
								jQuery('#saving_msg').delay(100).fadeOut('slow'); 
								jQuery(this).removeAttr('disabled');
							})
						;	
					}
				},
				{
					text	:	"Save & Exit",
					click	:	function() {
						jQuery('.dt_option').each( function( index, element ) {
							if ( jQuery(this).val() == 'select' ) {
								var s_string = '{value:"';
								var s_array = [];
								jQuery(this).parent().parent().find('#select_option').find('.opt_names').each( function( index, element ) {
									var opt_name = jQuery(this).val();
									var value = jQuery(this).parent().parent().find('.opt_values').val();
									if ( opt_name != '' && value != '' ) {
										s_array.push( opt_name + ':' + value );
									}
								});
								s_string += s_array.join(';');
								s_string += '"}';
								jQuery(this).parent().parent().find('#field_option').text(s_string);
							}
							else if ( jQuery(this).val() == 'checkbox' ) {
								var check = jQuery(this).parent().parent().find('#check_option').find('.opt_names').val();
								var uncheck = jQuery(this).parent().parent().find('#check_option').find('.opt_values').val();
								if ( check == '' || uncheck == '' ) 
									s_string = '';
								else 
									s_string = '{value:"' + check+':'+uncheck + '"}';
								jQuery(this).parent().parent().find('#field_option').text(s_string);
							}
							else {
								s_string = '';
								jQuery(this).parent().parent().find('#field_option').text(s_string);
							}
						});
						
						jQuery("#saving_msg").show();
						
						jQuery(this).attr("disabled","disabled");
						
						var action = "<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_update.php";
						
						jQuery.post(
							action, 
							jQuery('#dt_main_form').serialize(),
							function() {
								jQuery('#saving_msg').delay(100).fadeOut('slow'); 
								jQuery(this).removeAttr('disabled');
								location.reload();
							}
						);				
					}
				},
				{
					text	:	"Close",
					click	:	function() {
						jQuery(this).dialog( "close" );
					}
				}
			]
		});
		var loading = "<img src=\"<?php echo plugins_url(); ?>/XIBLOX/images/ajax-loader-bar.gif\" />";
		jQuery("#edit_dialog").html(loading);
		jQuery.ajax({
			url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_edit.php?id=" + id,
			type 	: 	"get",
			success	:	function( msg ) {
				jQuery("#edit_dialog").html(msg);
				jQuery('#blox_tab').tabs();
			}
		});
		
	}
	function edit_structure_dialog( id ) {
		jQuery("#edit_structure_dialog").dialog({
			width	:	850,
			height 	: 	600,
			modal	:	true,
			buttons	:	[
				{
					text	:	"Save & Exit",
					click	:	function() {
						jQuery("#saving_msg").show();
						jQuery(this).attr("disabled","disabled");
						var action = "<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_update_structure.php";
						jQuery.post(
							action, 
							jQuery('#dt_edit_tb').serialize(),
							function( msg ) {
								jQuery('#saving_msg').delay(100).fadeOut('slow'); 
								jQuery(this).removeAttr('disabled');
								location.reload();
							}
						);				
					}
				},
				{
					text	:	"Close",
					click	:	function() {
						jQuery( this ).dialog( "close" );
					}
				}
			]
		});
		
		var loading = "<img src=\"<?php echo plugins_url(); ?>/XIBLOX/images/ajax-loader-bar.gif\" />";
		jQuery("#edit_structure_dialog").html(loading);
		
		jQuery.ajax({
			url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_edit_structure.php?id=" + id,
			type 	: 	"get",
			success	:	function( msg ) {
				jQuery("#edit_structure_dialog").html(msg);
				jQuery('#blox_tab').tabs();
			}
		});
		
	}
	function DeleteDialog( id ) {
		if ( confirm( "Do you really delete it?" ) ) {
			jQuery.ajax({
				url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_delete.php?table=" + id,
				type	:	"get",
				success	:	function( msg ) {
					location.reload();
				}
			});
		}	
	}
	
	
	jQuery(function() {
	
		jQuery(document).on("change", ".dt_option", function() {
			if ( jQuery(this).val() == 'select' ) {
				jQuery(this).parent().parent().find('#check_option').hide();
				jQuery(this).parent().parent().find('#select_option').show();
			}
			else if ( jQuery(this).val() == 'checkbox' ) {
				jQuery(this).parent().parent().find('#check_option').show();
				jQuery(this).parent().parent().find('#select_option').hide();
			}
			else {
				jQuery(this).parent().parent().find('#check_option').hide();
				jQuery(this).parent().parent().find('#select_option').hide();
			}
		});
		
		jQuery(document).on( "click", ".opt_add", function() {
			jQuery(this).parent().find('#option_add').append('<tr><td><input type="text" class="opt_names" /></td><td><input type="text" class="opt_values" /></td></tr>');
		});
		
		jQuery(document).on( "click", ".delete_col", function() {
			jQuery(this).parent().parent().remove();
		});
		
		jQuery("#add_row").click( function(e) {
			var row = '<tr><td><input type="text" name="dt_name[]" /></td><td><select name="dt_type[]" ><option value="varchar">VARCHAR</option><option value="int">INT</option><option value="text">TEXT</option></select></td><td><input type="text" name="dt_length[]" /></td><td><input type="text" name="dt_default[]" /></td></tr>';
			jQuery('.dt_create').append(row);
		});
		
		jQuery(document).on( "click", '#add_edit_row', function() {
			var row = '<tr><td><input type="text" name="dt_name[]" /></td><td><select name="dt_type[]" ><option value="varchar">VARCHAR</option><option value="int">INT</option><option value="text">TEXT</option></select></td><td><input type="text" name="dt_length[]" /></td><td><input type="text" name="dt_default[]" /></td></tr>';
			jQuery('.dt_edit').append(row);
		});
		
		jQuery("#blox_lists").dataTable({
			"sPaginationType"	:	"full_numbers"
		});
		
		jQuery("#add_database_button").click( function() {
			jQuery("#add_dialog").dialog({
				width	:	800,
				modal	:	true,
				buttons	:	[
					{
						text	:	"Add Datatable",
						click	:	function() {
							var name = jQuery("#table_name").val();
							if ( name == "" )
								alert("You must enter a table name!");
							else {
								jQuery.post(
									"<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_create.php", 
									jQuery('#dt_add_tb').serialize(), 
									function(){
										location.reload();
									}
								);
							}
						}
					}
				]
			});
		});	
	});
</script>

<div id="add_dialog" title="Add Blox">

	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="dt_add_tb">
	
		Name of the datatable ( prefix : xiblox_ ) 
		<input type="text" name="table_name" id="table_name" /></br>
		
		<table class="dt_create">
			<tr>
				<th>Name</th>
				<th>Type</th>
				<th>Length</th>
				<th>Default</th>
			</tr>
			<tr>
				<td>id</td>
				<td>int</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>
					<input type="text" name="dt_name[]" />            
				</td>
				<td>
					<select name="dt_type[]" >
						<option value="varchar">VARCHAR</option>
						<option value="int">INT</option>
						<option value="text">TEXT</option>
					</select>
				</td>
				<td>
					<input type="text" name="dt_length[]" />            
				</td>
				<td>
					<input type="text" name="dt_default[]" />            
				</td>
			</tr>
		</table>
		
    </form>
	
    <input type="button" value="Add Row" id="add_row" />
	
</div>

<div id="edit_dialog" title="Edit Datatable" align="center">
	<img src="<?php echo plugins_url(); ?>/XIBLOX/images/ajax-loader-bar.gif" />
</div>

<div id="edit_structure_dialog" title="Edit Datatable Structure" align="center">
	<img src="<?php echo plugins_url(); ?>/XIBLOX/images/ajax-loader-bar.gif" />
</div>

<div id="view_dialog" title="View Blox" align="center">
	<img src="<?php echo plugins_url(); ?>/XIBLOX/images/ajax-loader-bar.gif" />
</div>

<div class="container pull-left">

	<h2>Edit Local Database Tables &nbsp;
		<small><a class="add-new-h2" href="javascript:void(0)" id="add_database_button">Add New</a></small>
	</h2>
	<div class="panel panel-default">
	  <div class="panel-body">
	    <?php
			$sql = "SHOW TABLES";
			$results = $wpdb->get_results( $sql, ARRAY_N );
			if ( count( $results ) != 0 ) {
		?>
			<form method="post" action="" enctype="multipart/form-data">
			
				<table cellpadding="0" cellspacing="0" border="0" class="table table-hover table-bordered table-striped " id="blox_lists">	
					<thead>
						<tr>
							<th align="left" width = "20%">Table Name</th>
							<th align="center">Description</th>
							<!-- <th align="center">Options</th> -->
							<th align="center">Structure</th>
							</tr>
					</thead>
					
					<tbody>
					<?php
						$i = 0;
						foreach ( $results as $result ) {
					?>
						<tr>
							<td width="15%"><?php echo $result[0]; ?></td>
							<td width="60%" align="center">
								<input type="text" name="<?php echo $result[0]; ?>" value="<?php echo $description[$table[$i]]; ?>" style="width: 100%">
							</td>
							<!--<td width="30%" align="center">
								<a href="javascript:EditDialog('<?php //echo $result[0]; ?>')" class="wrapper-btn"><img class="edit_icon" src="<?php //echo plugins_url(); ?>/XIBLOX/images/edit.png" title="Edit" width="20" height="20"></a>
							</td> -->
							<td width="25%" align="center">
								<a href="javascript:edit_structure_dialog('<?php echo $result[0]; ?>')" class="wrapper-btn"><img class="edit_icon" src="<?php echo plugins_url(); ?>/XIBLOX/images/edit.png" title="Edit" width="20" height="20"></a>
								&nbsp;&nbsp;&nbsp;
								<a href="javascript:DeleteDialog('<?php echo $result[0]; ?>')" class="wrapper-btn"><img src="<?php echo plugins_url(); ?>/XIBLOX/images/delete.png" title="Delete" width="20" height="20" class="delete_icon"></a>
							</td>
						</tr>
					<?php
						$i++;
						}
					?>
					</tbody>
				</table>
				<input type="submit" name="submit" value="Save" class="btn btn-primary">
			</form>
		<?php
			}
		?>
	  </div>
	</div>	
</div>