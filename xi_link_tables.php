<?php

	/***************************************
	* edit link table page ( xiblox menu ) *
	*									   *
	* @package 	XIBLOX/					   *
	* @type 	hidden					   *
	* @author	itabix					   *
	***************************************/
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	global $wpdb;
	
	if ( !isset($_POST) || !isset($_POST["liveNumber"]) ) {
		echo "<script>
				location.href = '/wp-admin/admin.php?page=XIBLOX/xi_settings.php';
			  </script>";
		exit;
	}
	
	require_once ( "classes/xi_dbconn_class.php" );
	
	$number = $_POST["liveNumber"];
	
	if ( $number == "" )
		$number = $_POST["connNumber"];
	
	// get destination site info
	$sql = "SELECT * FROM xiblox_destination_info WHERE id = '$number'";
	$result = $wpdb->get_results( $sql, ARRAY_A );
	
	$connection_name = $result[0]["connection_name"];
	$destination_url = $result[0]["destination_url"];
	$db_host = $result[0]["db_host"];
	$db_name = $result[0]["db_name"];
	$db_user = $result[0]["db_user"];
	$db_password = $result[0]["db_password"];
	$db_prefix = $result[0]["db_prefix"];
	$destination_path = $result[0]["destination_path"];
	
	$db_conn = new DatabaseManager('mysql', $db_host, $db_user, $db_password, $db_name);
	
	$save = $_POST["save"];
	
	if ( $save == "Save" ) {
	
		$check_table = $_POST["tables"];
		
		// store all tables name to xiblox_check_publish table 
		$sql = "SHOW TABLES LIKE '%'";
		$results = $wpdb->get_results( $sql );
		
		foreach ( $results as $index => $value ) {
		
			foreach ( $value as $table_name ) {
			
				if ( strpos( $table_name, "xiblox_" ) !== false )  
					continue; 
					
				$sql = "SELECT table_name FROM xiblox_check_publish WHERE table_name = '" . $table_name . "'";
				$res = $wpdb->get_results ( $sql, ARRAY_A );
				
				if ( $res[0]['table_name'] == '' ) 
					$res = $wpdb->insert( 'xiblox_check_publish', array( 'table_name' => $table_name , 'check_status' => '' ) );
					
			}
			
		}
		
		// add connection number to checked table
		$sql = "SHOW TABLES LIKE '%'";
		$results = $wpdb->get_results( $sql );
		
		$live_number = $_POST["connNumber"];
		
		foreach ( $results as $index => $value ) {
		
			foreach ( $value as $table_name ) {
			
				if ( strpos( $table_name, "xiblox_" ) !== false ) 
					continue;
					
				for ( $i = 0; $i < count( $check_table ); $i++ ) {
				
					if ( $check_table[$i] == $table_name ) {
					
						$sql = "SELECT conn_num FROM xiblox_check_publish WHERE table_name = '$table_name'";
						$res = $wpdb->get_results( $sql, ARRAY_A );
						
						$connNum = $res[0]["conn_num"];
						
						if ( strpos( $connNum, $live_number ) === false )  
							if ( $connNum == "" ) 
								$connNum = $live_number;
							else 
								$connNum .= "," . $live_number;
							
						$res = $wpdb->query( "UPDATE xiblox_check_publish SET check_status='1', conn_num = '$connNum' WHERE table_name = '" . $table_name . "'");
						$flagChecked = 1;
					}
				}
				
				if ( $flagChecked != 1 ) 
					$res = $wpdb->query( "UPDATE xiblox_check_publish SET check_status='0', conn_num='' WHERE table_name='" . $table_name . "'");
					
				$flagChecked = 0;
				
			}
		}
	}
?>

<link type="text/css" href="<?php echo plugins_url(); ?>/XIBLOX/assets/css/jquery-ui-blox.css" rel="stylesheet" />

<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/jquery-ui.custom.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/jquery.dataTables.js"></script>

<script>
	jQuery(document).ready(function() {
		jQuery("input[name=check_all").click(function() {
			if ( jQuery(this).attr("checked") )
				jQuery("form").find(".check_table").each(function() {
					jQuery(this).attr("checked", true);
				});
			else
				jQuery("form").find(".check_table").each(function() {
					jQuery(this).attr("checked", false);
				});
		})
	})
	
	function edit_linkTable( id ) {
		jQuery("#edit_dialogLT").dialog({
			width	:	800,
			height	:	600,
			modal	:	true,
			buttons	:	[
				{
					text	:	"Save",
					click	:	function() {
						jQuery("#saving_msg").show();
						jQuery(this).attr("disabled","disabled");
						var action = "<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_update_linktable.php";
						
						jQuery.post(
							action, 
							jQuery('#lt_main_form').serialize(),
							function() {
								jQuery('#saving_msg').delay(100).fadeOut('slow'); 
								jQuery(this).removeAttr('disabled');
							}
						);	
					}
				},
				{
					text	:	"Save & Exit",
					click	:	function() {
						jQuery("#saving_msg").show();
						jQuery(this).attr("disabled","disabled");
						var action = "<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_update_linktable.php";
						
						jQuery.post(
							action, 
							jQuery('#lt_main_form').serialize(),
							function() {
								jQuery('#saving_msg').delay(100).fadeOut('slow'); 
								jQuery(this).removeAttr('disabled');
							}
						);		
						
						setTimeout( function() {
							location.reload();
						}, 2000); 
					}	
				},
				{
					text	:	"Close",
					click	:	function() {
						jQuery(this).dialog("close");
					}
				}
			]
		});
		
		var loading = "<img src=\"<?php echo plugins_url(); ?>/XIBLOX/images/ajax-loader-bar.gif\" />";
		jQuery("#edit_dialogLT").html(loading);
		
		jQuery.ajax({
			url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/datatable/dt_edit_linktable.php?id=" + id,
			type	:	"get",
			success	:	function( msg ) {
				jQuery("#edit_dialogLT").html(msg);
				jQuery('#blox_tab').tabs();
			}
		});
	}
</script>

<div id="edit_dialogLT" title="Edit LinkTable" align="center"></div>

<div id="tables">

	</br>
	
	<h2>XIBLOX - Push to "<?php echo $connection_name; ?>" Database</h2>
	
	<br>
	
	<h3>Tables marked in red have no matching table on "<?php echo $connection_name; ?>" connection</h3>
	
	<form action="" method="post">
	
		<table class="edit-link-table" cellspacing="0">
		
			<tr style="height: 32px;">
				<th id="xi_cb"><input type="checkbox" name="check_all"></th>
				<th id="xi_title" align="left" width="10%">Table Name</th>
				<th id="xi_title" align="left" width="10%">Description</th>
				<th id="xi_title" align="center" width="10%">Link Tables</th>
				<th id="xi_title" align="left">Linked Tables</th>
			</tr>
			
			<tr>
				<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
			</tr>
			<?php 
				$sql = "SHOW TABLES LIKE '%'";
				$results = $wpdb->get_results( $sql );
				
				foreach ( $results as $index => $value ) {
				
					foreach ( $value as $table_name ) {
					
						if ( strpos( $table_name, "xiblox_" ) !== false ) 
							continue;
							
						$sql = "SELECT check_status, conn_num FROM xiblox_check_publish WHERE table_name = '" . $table_name . "'";
						$res = $wpdb->get_results( $sql, ARRAY_A );
						
						if ( ( $res[0]["check_status"] == 1 ) && ( strpos( $res[0]["conn_num"], $number ) !== false ) ) 
							$flagCheck = 1;						
							
						$sql = "SELECT create_time FROM information_schema.tables WHERE table_schema = '" . $db_name . "' AND table_name = '" . $table_name . "' ";
						$res = $db_conn->queryArray( $sql );
						
						if ( count( $res ) == 1 ) 
							$color = "black";
						else { 
							$color = "red"; 
							$flagPublish = 0; 
						}
						
						$sql = "SELECT description FROM xiblox_description WHERE table_name = '" . $table_name . "'";
						$result = $wpdb->get_results( $sql, ARRAY_A );
						
						$description = $result[0]["description"];
			?>
			<tr>
				<th class="check-column">
					<input type="checkbox" class="check_table" value="<?php echo $table_name; ?>" name="tables[]" <?php if ( $flagCheck == 1 ) { $flagCheck = 0; echo "checked"; } ?>/>
				</th>
				<td align="left"><span style="color: <?php echo $color; ?>; font-weight: bold;"><?php echo $table_name; ?></span></td>
				<td align="left"><?php echo $description; ?></td>
				<td align="center">
					<a href="javascript:edit_linkTable('<?php echo $table_name; ?>')">
						<img class="edit_icon" src="<?php echo plugins_url(); ?>/XIBLOX/images/edit.png" title="Edit" width="20" height="20">
					</a>
				</td>
				<td>
				<?php
					$sql = "SELECT link_table FROM xiblox_link_table WHERE table_name = '" . $table_name . "'";
					$result = $wpdb->get_results( $sql, ARRAY_A );
					$linkedTables = substr( $result[0]["link_table"], 0, -1 );
					$linkedTables = str_replace( ",", ", ", $linkedTables );
					echo $linkedTables;
				?>
				</td>
			</tr>
			<tr>
				<td colspan="5" style="border-top:1px solid #DDDDDD;height:1px;"></td>
			</tr>
			<?php 
					} 
				}
			?>
		</table>
		
		<input type="submit" name="save" value="Save">
		<input type="hidden" name="connNumber" value="<?php echo $number; ?>">
		
	</form>
</div>
<?php
	$db_conn = null;
?>