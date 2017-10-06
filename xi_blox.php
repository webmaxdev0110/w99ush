<?php

	/****************************
	* blox page ( xiblox menu ) *
	*							*
	* @package 	XIBLOX/			*
	* @author	itabix			*
	****************************/
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	global $wpdb;
	
	$dir = substr( $_SERVER['SCRIPT_FILENAME'], 0, -15 ) . "/compiled/";
	
	require_once( "includes/blox/blox_script.php" );
	
	// Get license_key 
	$sql = "SELECT * FROM xiblox_license";
	$res = $wpdb->get_results( $sql, ARRAY_A );
	$license_key = $res[0]["license_key"];
	
	
	// change the default jquery ui as the customized one
	
	wp_dequeue_script( 'jquery-ui-dialog' );
	
	
	
	
?>


<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/blox_list.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/ace/ace/ace.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/ace/ace/theme-twilight.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/ace/ace/mode-ruby.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/ace/jquery-ace.min.js"></script>
<!-- -->


<!-- The dialog when "Add New button" is clicked --> 
<div class="modal fade" id="add_dialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add Blox</h4>
      </div>
      <div class="modal-body">
        <table>
	        <tr>
	            <td width="100">Name of the Blox:</td>
	            <td width="150"><input type="text" name="names_tab" id="names_tab" /></td>
	        </tr>
	        <tr>
	            <td>Description</td>
	        </tr>
	        <tr>
	            <td colspan="2">
	            <textarea id="xi_tab_contents"></textarea>
	            </td>
	        </tr>
	        <tr>
	            <td>Content</td>
	        </tr>
	        <tr>
	            <td colspan="2">
	            <textarea id="xi_tab_custom"></textarea>
	            </td>
	        </tr>
	    </table>
		
		<div class="xiUi-widget-overlay" id="saving_msg">
			<img src="<?php echo plugins_url(); ?>/XIBLOX/images/save_tab.gif" />&nbsp;Saving ...
		</div>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="blox_tab_add">Add Tab</button>
      </div>
    </div>
  </div>
</div>
<!-- -->


<!-- Edit dialog when any blox is cliked ( ajax div ) -->
<div class="modal fade" id="edit_dialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Blox</h4>
        <input type="hidden" id="blox_id" />
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="blox_save">Save</button>
        <button type="button" class="btn btn-primary" id="blox_store">Store Version</button>
      </div>
    </div>
  </div>
</div>
<!-- -->

<div class="modal fade" id="login_dialog" tabindex="-1" role="dialog" aria-labelledby="login_dialog_title" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="login_dialog_title">You have to sign up to library server.</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="library_login">
		<h2>You have to sign up to library server.</h2>
		<div class="lib_user">
			<span class="lib_span">Username</span>
			<input type="text" name="username" class="lib_input">
		</div>
		<div class="lib_pwd">
			<span class="lib_span">Password</span>
			<input type="password" name="password" class="lib_input">
		</div>
		<div class="lib_pwd">
			<span class="lib_span">Password(again)</span>
			<input type="password" name="password_again" class="lib_input">
		</div>
		<div class="lib_email">
			<span class="lib_span">Email</span>
			<input type="text" name="email" class="lib_input">
		</div>
		<div class="lib_paypal">
			<span class="lib_span">Paypal Account</span>
			<input type="text" name="paypal_account" class="lib_input">
		</div>
		<div class="lib_license">
			<span class="lib_span">License Key</span>
			<input type="text" name="license_key" class="lib_input" value="<?php echo $license_key; ?>">
		</div>
		
		
		<div class="lib_btn">
			<span class="lib_span"></span>
			<input type="button" value="Sign up" name="library_login" class="lib_input" onclick="RegisterToServer()">
		</div>
	</div>
	<input type="hidden" name="selected_blox">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<!-- -->



<!-- main content -->
<div class="container pull-left">
    <?php
        $sql = "SELECT * FROM xiblox_tabs ORDER BY blox_name";
        $results = $wpdb->get_results( $sql, ARRAY_A );
    ?>
	<h2>Blox Management
		<small><a class="add-new-h2" href="javascript:void(0)" id="add_blox_button">Add New</a>
		<a class="add-new-h2" href="javascript:CompileAll('<?php echo count($results); ?>')" id="compile_all">Compile All</a></small>
	</h2>
	<div class="panel panel-default">
	  <div class="panel-body">
	    <?php
			if ( count( $results ) != 0 ) {
		?>
			<table cellpadding="0" cellspacing="0" border="0" class="display" id="blox_lists">
				<thead>
					<tr>
						<th align="left" width="1%"><input type="checkbox" id="tCheck"></th>
						<th align="left" width="2%"></th>
						<th align="left">
							<a >Blox Name</a>
							<a id="collapseAll">Collapse All</a>
							<a id="expandAll">Expand All</a>
						</th>
					</tr>
				</thead>
				<tbody>
			<?php
				foreach ( $results as $result ) {
			?>
					<tr pName="<?php echo $result['parent_blox'];?>" c_count="0" bName="<?php echo $result['blox_name'];?>" isloaded="0">
					<?php
						$sql = "SELECT blox_name FROM xiblox_tabs where parent_blox = '" . $result['blox_name'] . "'";
						$res = $wpdb->get_results( $sql, ARRAY_A );
					?>
						
						<td class="rootPath">
							<input type="checkbox" id="id_<?php echo $result["blox_name"]; ?>" >
						</td>
						<td>
							<div class="circleBase draggable"></div>
						</td>
						<td class="treeview backImg">
							<div class="<?php if ( count($res) > 0 ) echo "hitarea expandable-hitarea parentBlox"; ?>" style="float: left; margin-top: 4px; margin-right: 4px; margin-left: -10px; width: 12px; height: 12px;"  isOpened='0' level='0' onclick="ExpanseS(this)"></div>
							<a href="javascript:EditDialog(<?php echo $result['id']; ?>, <?php echo $result['status']; ?>)" style="<?php if ( count($res) == 0 ) echo "margin-left: -14px;"; ?> text-decoration: none;  <?php if ( $result['status'] == 0 ) echo "color: grey; cursor: text"; ?>" >
							<?php echo $result['blox_name']; ?>
							</a> | 
							<span style="font-family: serif"><?php echo $result['blox_content']; ?></span>
							<input type="hidden" value="<?php echo $result["id"]; ?>">
						</td>
					</tr>
			<?php
				}
			?>
				</tbody>
				
			</table>
		<?php }
		else {
		?>
			<div class="alert alert-warning" role="alert">
			  here are currently no blox created. Click Add New to create a blox.
			</div>
		<?php
		}
		?>
	  </div>
	</div>
</div>

<script>
	var indexP;
	var flagRes = 0;
	
	jQuery("#blox_lists .draggable").draggable({
		helper: "clone",
		opacity: .75,
		refreshPositions: true, // Performance?
		revert: "invalid",
		revertDuration: 300,
		scroll: true
	});
	
	jQuery("#blox_lists .draggable").each(function() {
	
		jQuery(".rootPath").droppable({
			accep	:	".draggable",
			drop	:	function( e, ui ) {
				var objParent = FindParent( ui.draggable.parents("tr") );
				
				InitiateLevelC( objParent );
				ui.draggable.parents("tr").attr("pName", '');
				init();
				makeColor();
				objParent.find("td").eq(2).find("div").attr("isOpened", 1);
				Expanse( objParent.find("td").eq(2).find("div") );
				
				objParent.find("td").eq(2).find("div").attr("isOpened", 0);
				Expanse( objParent.find("td").eq(2).find("div") );
				
				InitiateLevelC( ui.draggable.parents("tr") );
				ui.draggable.parents("tr").find("td").eq(2).find("div").attr("isOpened", 0);
				Expanse( ui.draggable.parents("tr").find("td").eq(2).find("div") );
				
				/***********store into db *******************/
				var sBName = ui.draggable.parents("tr").attr("bName");
				var action = "<?php echo plugins_url(); ?>/XIBLOX/includes/blox/blox_store_change.php";
				jQuery.ajax(
					action,
					{
						type	:	'post',
						data	:	'blox_name=' + sBName + '&parent_blox=""',
						success	: 	function() { }
					}
				);
				/************end store *********************/
				
				/************ Remove +/- Icon if no have child blox ****************/
				var objBloxName = objParent.attr("bName");
				var isChild = FindChild(objBloxName);
				
				if ( isChild == 0 ) 
						objParent.find("td").eq(2).find("div").attr("class", "");
				
				isChild = FindChild(sBName);
				
				if ( isChild == 0 ) 
					ui.draggable.parents("tr").find("td").eq(2).find("div").attr("class", "");
				/************ Remove +/- Icon if no have child blox End ****************/
				sort();
				sort();
			},
			hoverClass	:	"accept",
			over		:	function( e, ui ) { },
			out			: 	function( e, ui ) { }
		});
		jQuery(".draggable").droppable({
			accept	:	".draggable",
			drop	:	function( e, ui ) {
				var droppedEl = ui.draggable.parents("tr");
				var availableRes = available( droppedEl, jQuery(this).parents("tr") );
				if ( availableRes == 1 ) {
					var d_bName = jQuery(this).parents("tr").attr("bName");
					var d_fName = FindParent( droppedEl );
					var objParent = jQuery(this).parents("tr");
					
					if ( objParent.find("td").eq(2).find("div").hasClass("parentBlox") == false ) {
						objParent.find("td").eq(2).find("div").css("margin-left", -10 + 12 * objParent.find("td").eq(2).find("div").attr("level"));
						jQuery(this).parents("tr").find("td").eq(2).find("div").addClass("hitarea expandable-hitarea parentBlox");
					}
					droppedEl.attr("pName", d_bName);
					droppedEl.removeClass("currBlox");
					init();
					makeColor();
					
					/****** Initiate Level **********************************/
					InitiateLevelC( droppedEl );
					//InitiateLevelC(objParent);
					init();
					makeColor();
					
					objParent.find("td").eq(2).find("div").attr("isOpened", 1);
					Expanse( objParent.find("td").eq(2).find("div") );
					objParent.find("td").eq(2).find("div").attr("isOpened", 0);
					Expanse( objParent.find("td").eq(2).find("div") );
					if ( d_fName.find("td").eq(2).find("div").hasClass("parentBlox") == true ) {
						d_fName.find("td").eq(2).find("div").attr("isOpened", 1);
						Expanse( d_fName.find("td").eq(2).find("div") );
						d_fName.find("td").eq(2).find("div").attr("isOpened", 0);
						Expanse( d_fName.find("td").eq(2).find("div") );
					}
					
					/**********End Initiate Level *******************************/
					
					
					flagRes = 0;
					
					var action = "<?php echo plugins_url(); ?>/XIBLOX/includes/blox/blox_store_change.php";
					var sBName = ui.draggable.parents("tr").attr("bName");
					var sPName = d_bName;
					jQuery.ajax(
						action,{
							type	:	'post',
							data	:	'blox_name=' + sBName + '&parent_blox=' + sPName,
							success :   function() { }
					});
				}
				jQuery(this).css("background", "#ccc");
				sort();
				sort();
			},
			hoverClass	:	"accept",
			over		:	function( e, ui ) {
				jQuery(this).css("background", "black");
				if ( jQuery(this).parents("tr").find("td").eq(2).find("div").hasClass("parentBlox") == true ) {
					Expanse( jQuery(this).parents("tr").find("td").eq(2).find("div") );
				}
			},
			out			:	function( e, ui ) {
				jQuery(this).css("background", "#ccc");
			}
		});
	});
</script>