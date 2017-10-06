<?php
	/*****************************
	* Site Push Page			 *
	* 							 *
	* @version  1.30			 *
	* @package	XIBLOX/			 *
	* @autuor 	itabix			 *
	*****************************/
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	$php = '<?php ';
	
	// substitute connection number ( table : xiblox_destination_info, field : ID )
	for ( $i = 0; $i < count( $number ); $i++ ) {
		$php .= '$number[' . $i . '] = ' . $number[$i] . ';';
	}
	
	// change the connection name for class name ( table : xiblox_destination_info, field: connection_name )
	// substitute connection name
	for ( $i = 0; $i < count( $connection_name ); $i++ ) {
		$connection_name[$i] = str_replace( "-", "_", $connection_name[$i] );
		$connection_name[$i] = str_replace( "'", "_", $connection_name[$i] );
		$php .= '$liveName[' . $i . '] = "' . $connection_name[$i] . '";';
	}
	
	// substitute error variable
	if ( $error != "" ) {
		for ( $i = 0; $i < count( $error ); $i++ ) {
			$php .= '$error[' . $i . '] = "' . $error[$i] . '";';
		}
	} else 
		$php .= '$error = "";';
		
	for ( $i = 0; $i < count( $pubType ); $i++ ) {
		$php .= '$pubType[' . $i . '] = "' . $pubType[$i] . '";';
	}
	
	$php .= '
		if ( ( count($error) > 0 ) && ( $error != "" ) ) {
			for ( $i = 0; $i < count( $error ); $i++ )
				echo "<script> alert(\'Fail to connect to \'".$error[$i]."\' live server. Please confirm in Settings page\'); </script>";
		}
		
		@session_start();
		
		global $wpdb;
		
		$prefix = $wpdb->prefix;
		
		/**
		 * Begin Check Function
		 * @start
		 */
		 
		require_once( ABSPATH . "wp-content/plugins/XIBLOX/includes/xi_license_function.php" );
		require_once( ABSPATH . "wp-content/plugins/XIBLOX/classes/xi_dbconn_class.php");
		
		wp_dequeue_script( "jquery-ui-tabs" );
		wp_dequeue_script( "jquery-ui-dialog" );
		
		/**
		 * @end
		 */
		
		
		// get the license key
		$sql = "SELECT * FROM xiblox_license";
		$res = $wpdb->get_results( $sql, ARRAY_A );
		
		$license_key = $res[0]["license_key"];
		$local_key = $res[0]["local_key"];
		
		$results = GetLicense( $license_key, $local_key );
		$results["status"] = "Active"; // temporary expression
		
		if ( $_SESSION["select_post"] == "" )
			$_SESSION["select_post"] = $liveName[0];
		if ( $_SESSION["select_page"] == "" )
			$_SESSION["select_page"] = $liveName[0];
		if ( $_SESSION["select_link"] == "" )
			$_SESSION["select_link"] = $liveName[0];
		if ( $_SESSION["select_media"] == "" )
			$_SESSION["select_media"] = $liveName[0];
		if ( $_SESSION["select_theme"] == "" )
			$_SESSION["select_theme"] = $liveName[0];
		if ( $_SESSION["select_plugin"] == "" )
			$_SESSION["select_plugin"] = $liveName[0];
		if ( $_SESSION["select_user"] == "" )
			$_SESSION["select_user"] = $liveName[0];
		if ( $_SESSION["select_table"] == "" )
			$_SESSION["select_table"] = $liveName[0];
		if ( $_SESSION["select_menu"] == "" )
			$_SESSION["select_menu"] = $liveName[0];
		if ( $_SESSION["select_blox"] == "" )
			$_SESSION["select_blox"] = $liveName[0];
		if ( $_SESSION["select_unpushed"] == "" )
			$_SESSION["select_unpushed"] = $liveName[0];
		
		if ( $results["status"] != "Active" ) {
		?>

		<script type="text/javascript" >
		
			function CheckLicense() {
				
				var license = jQuery("#check_license").val();
				
				if ( license == "" ) {
					alert("Please put the license key!");
					return false;
				} else {
					jQuery("#ajax_field").hide();
					jQuery("#ajax_field").html("Checking...");
					jQuery("#ajax_field").fadeIn();
					jQuery.ajax({
						url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_check_license.php",
						type	:	"post",
						data	:	"key=" + license,
						success	:	function( msg ) {
							jQuery("#ajax_field").hide();
							jQuery("#ajax_field").html(msg);
							jQuery("#ajax_field").fadeIn();
						}
					});
				}
			}
		</script>
		
		<div class="license-wrap">
            <h3>Please put your license key.</h3>
            <div style="padding: 7px">
                <label>License Key : </label>
                <input type="text" id="check_license" name="license_key">
                <input type="button" name="check_license" id="check_license" class = "button-primary" value = "Check License" onclick = "CheckLicense()">
                <div id="ajax_field"></div>
            </div>
			<div style = "clear:both"></div>
		</div>
		<?php
		} else {
		
			// Save license key to database  
			$sql = "SELECT * FROM xiblox_license";
			$res = $wpdb->get_results( $sql, ARRAY_A );
			
			if ( $res[0]["license_key"] == "" ) {
				$sql = "insert into xiblox_license ( license_key ) values ( \'" . $license_key . "\' )";
				$res = $wpdb->query( $sql );
			} else {
				$sql = "update xiblox_license set license_key = \'" . $license_key . "\'";
			}
			
		?>
		
		<div class="wrap">
			<h2>XIBLOX Push</h2>
			
			<link rel="stylesheet" href="<?php echo plugins_url(); ?>/XIBLOX/assets/css/jquery-ui-blox.css" type="text/css" media="all">
			<link rel="stylesheet" href="<?php echo plugins_url(); ?>/XIBLOX/assets/css/jquery.treeview.css" type="text/css" media="all">

			<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/jquery-ui.custom.js"></script>
			<script src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/jquery.cookie.js" type="text/javascript"></script>
			<script src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/jquery.treeview.js" type="text/javascript"></script>

			<script type="text/javascript">
			
				jQuery(document).ready( function() {
					
					// Save selected connection
					jQuery("input[name=selectAll").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_all=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					jQuery("input[name=selectPost").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_post=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					jQuery("input[name=selectPage").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_page=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					jQuery("input[name=selectLink").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_link=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
				
					jQuery("input[name=selectMedia").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_media=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					jQuery("input[name=selectTheme").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_theme=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					jQuery("input[name=selectPlugin").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_plugin=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					jQuery("input[name=selectUser").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_user=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
				
					jQuery("input[name=selectTable").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_table=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					jQuery("input[name=selectMenu").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_menu=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					jQuery("input[name=selectBlox").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_blox=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					jQuery("input[name=selectUnpushed").change(function() {
						
						var selected = jQuery(this).val();
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?select_unpushed=" + selected,
							type	:	"get",
							success	:	function() {}
						});
					});
					
					// Change Event on Menu tab
					jQuery("select[id=sel_menu]").change( function() {
					
						var selMenu = "show_" + jQuery(this).val();
						jQuery(this).parent().find("table[class=showMenuItem]").each( function() {
						
							var getMenu = jQuery(this).attr("id");
							if ( selMenu == getMenu ) 
								jQuery(this).attr("style", "background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px; display: table");
							else 
								jQuery(this).attr("style", "background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px; display: none");
								
						});
						
					});
					
					<?php for ( $i =0; $i < count( $liveName ); $i++ ) { ?>
					
					jQuery("div[id=pub_<?php echo $liveName[$i]; ?>]").each( function() {
					
						jQuery(this).find("input[type=checkbox]").change( function() {
							
							// watch the change event to display the selected item on top yellow bar
							setTimeout( function() {
								var post_no = jQuery("#posts").find("#pub_<?php echo $liveName[$i]; ?>").find(".post_check:checked").length;
								var page_no = jQuery("#pages").find("#pub_<?php echo $liveName[$i]; ?>").find(".page_check:checked").length;
								var link_no = jQuery("#links").find("#pub_<?php echo $liveName[$i]; ?>").find(".links_check:checked").length;
								var media_no = jQuery("#media").find("#pub_<?php echo $liveName[$i]; ?>").find(".attach_check:checked").length;
								var theme_no = jQuery("#themes").find("#pub_<?php echo $liveName[$i]; ?>").find(".themes_check:checked").length;
								var plugin_no = jQuery("#plugins").find("#pub_<?php echo $liveName[$i]; ?>").find(".plugin_check:checked").length;
								var user_no = jQuery("#users").find("#pub_<?php echo $liveName[$i]; ?>").find(".user_check:checked").length;
								var table_no = jQuery("#tables").find("#pub_<?php echo $liveName[$i]; ?>").find(".table_check:checked").length;
								var menu_no = jQuery("#menus").find("#pub_<?php echo $liveName[$i]; ?>").find(".menu_check:checked").length;
								var blox_no = jQuery("#blox").find("#pub_<?php echo $liveName[$i]; ?>").find(".blox_check:checked").length;
								
								jQuery("#no_post_<?php echo $liveName[$i]; ?>").html(post_no);
								jQuery("#no_page_<?php echo $liveName[$i]; ?>").html(page_no);
								jQuery("#no_link_<?php echo $liveName[$i]; ?>").html(link_no);
								jQuery("#no_media_<?php echo $liveName[$i]; ?>").html(media_no);
								jQuery("#no_theme_<?php echo $liveName[$i]; ?>").html(theme_no);
								jQuery("#no_plugin_<?php echo $liveName[$i]; ?>").html(plugin_no);
								jQuery("#no_user_<?php echo $liveName[$i]; ?>").html(user_no);
								jQuery("#no_table_<?php echo $liveName[$i]; ?>").html(table_no);
								jQuery("#no_menu_<?php echo $liveName[$i]; ?>").html(menu_no);
								jQuery("#no_blox_<?php echo $liveName[$i]; ?>").html(blox_no);
							}, 1);
						});
					});
					
					<?php } ?>
	
					jQuery("#pages").find("ul[id=xi_navigation]").each( function() {
						jQuery(this).treeview({
							control		:	"#treecontrol",
							animated	:	"fast",
							collapsed	:	false,
							unique		:	true, 
							persist		:	"cookie"
						});
					});
	
					var flagSame = 0;
					var flagEnd = 0;
					var cntPub = "<?php echo count($number); ?>";
					
					if ( cntPub > 1  ) {
					
						var confirmF = "<?php echo $pubType[0]; ?>";
						if ( confirmF != "" ) {
							jQuery("#xi_all").find("li").each( function() {
								if ( flagEnd != 1 ) {
									<?php for ( $i = 0; $i < count( $pubType ); $i++ ) { ?>
										if ( jQuery(this).find("a").attr("id") == "xi_<?php echo $pubType[$i]; ?>" ) 
											flagSame = 1;
									<?php } ?>
									if ( ( flagSame != 1 ) && ( jQuery(this).find("a").attr("id") != "xi_publish" ) ) 
										jQuery(this).attr("style", "display: none");
									flagSame = 0;
									if ( jQuery(this).find("a").attr("id") == "xi_blox" ) 
										flagEnd = 1;
								} else 
									flagEnd = 0;
							});
						}
						
						// if several connection exist, switch the corresponding div 
						jQuery("input:radio[id=selectLive]").each( function() {
							jQuery(this).on("change", function() {
								var number = jQuery(this).attr("index");
								jQuery("#number").val(number);
								jQuery(this).parent().parent().find("#pub_" + this.value).attr("style", "display: block");
								<?php for ( $i = 0; $i < count( $number ); $i++ ) { ?>
								if ( this.value != "<?php echo $liveName[$i]; ?>" ) 
									jQuery(this).parent().parent().find("#pub_<?php echo $liveName[$i]; ?>").attr("style", "display: none");
								<?php } ?>
							});
						});
						
						/**
						 * switch the clicked tab div
						 * @start
						 */
						 
						// event to click the push tab
						jQuery("#xi_publish").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
						}) 
						 
						// event to click the post tab
						jQuery("#xi_posts").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#posts").find("input:radio[name=selectPost]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the page tab
						jQuery("#xi_pages").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#pages").find("input:radio[name=selectPage]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the link tab
						jQuery("#xi_links").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#links").find("input:radio[name=selectLink]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the media tab
						jQuery("#xi_media").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#media").find("input:radio[name=selectMedia]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the theme tab
						jQuery("#xi_themes").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#themes").find("input:radio[name=selectTheme]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the plugin tab
						jQuery("#xi_plugins").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#plugins").find("input:radio[name=selectPlugin]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the user tab
						jQuery("#xi_users").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#users").find("input:radio[name=selectUser]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the table tab
						jQuery("#xi_tables").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#tables").find("input:radio[name=selectTable]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the menu tab
						jQuery("#xi_menus").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#menus").find("input:radio[name=selectMenu]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the blox tab
						jQuery("#xi_blox").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#blox").find("input:radio[name=selectBlox]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						// event to click the unpushed tab
						jQuery("#xi_unpushed").click( function() {
							jQuery("#pub").attr("style", "display: block");
							jQuery("#pubBlox").attr("style", "display: none");
							var num = jQuery("#unpushed").find("input:radio[name=selectUnpushed]:checked").attr("index");
							jQuery("#number").val(num);
						});
						
						/**
						 * @end
						 */
					}
					
					/**
					 * watch the checkall event on every tab
					 * @start
					 */
					
					// event to check all post
					jQuery("input[id=checkall]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".post_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all page
					jQuery("input[id=checkall_page]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".page_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
							
						});
					});
					
					// event to check all link
					jQuery("input[id=checkall_link]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".links_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all media
					jQuery("input[id=checkall_attachment]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".attach_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all theme
					jQuery("input[id=checkall_themes]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".themes_check").each(function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all plugin
					jQuery("input[id=checkall_plugin]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".plugin_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all user
					jQuery("input[id=checkall_users]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".user_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all table
					jQuery("input[id=checkall_tables]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".table_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all menu
					jQuery("input[id=checkall_menus]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".menu_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all menu item
					jQuery("input[id=checkall_menuItems]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".menuItem_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all blox
					jQuery("input[id=checkall_blox]").each( function() {
						jQuery(this).click( function() {
							var obj = jQuery(this);
							jQuery(this).parent().parent().parent().parent().parent().find(".blox_check").each( function() {
								if ( obj.prop("checked") == false )
									jQuery(this).prop("checked", false);
								else 
									jQuery(this).prop("checked", true);
							});
						});
					});
					
					// event to check all custom tab
					jQuery("#xi_all").find("li[id=custom]").each( function() {
						jQuery(this).click( function() {
							jQuery("#pub").attr("style", "display: none");
							jQuery("#pubBlox").attr("style", "display: block");
						});
					});
					
					// Initiate progressing bar
					jQuery("#spaceused1").progressBar({ 
						barImage	:	"<?php echo plugins_url(); ?>/XIBLOX/images/progress.png", 
						boxImage	:	"<?php echo plugins_url(); ?>/XIBLOX/images/progressbar.png"
					});
					
					// event to click "Push All Selected Items" button
					jQuery("#pub").click( function() {
					
						var msg = "<?php echo $liveName[0]; ?> ";
						
						<?php for ( $i = 1; $i < count( $liveName ); $i++ ) { ?>
							msg = msg + "and " + "<?php echo $liveName[$i]; ?>";
						<?php } ?>
						
						var result = confirm( "Warning - you are about to push all selected items to " + msg );
						
						if ( result == true ) {
							jQuery.ajax({
								type	:	"get",
								url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_publish_status.php?start=1", // check other users are using this plugin on same host
								success	:	function( val ) {
									
									var process_flag = 0;
									
									if ( val == 1 ) {
									
										var confirm_res = confirm("It appears that some other user has started a push, would you like to cancel that?");
										
										if ( confirm_res ) {
											process_flag = 1;
										}
									} else 
										process_flag = 1;
									
									
									if ( process_flag == 1 ) {
										<?php 
											for ( $i = 0; $i < count( $liveName ); $i++ ) { 
										?>
											var allVals_<?php echo $liveName[$i]; ?> = [];
											var allTypes_<?php echo $liveName[$i]; ?> = [];
										<?php 
											} 
											for ( $i = 0; $i < count( $liveName ); $i++ ) { 
											
											// substitute all values and types to the parameters
										?>
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".post_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(0);
											});
											
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".page_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(0);
											});
											
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".links_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(1);
											});
											
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".attach_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(2);
											});
												
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".themes_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(3);
											});
											
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".plugin_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(4);
											});
											
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".user_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(5);
											});
											
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".table_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(6);
											});
											
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".menu_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(20);
											});
											
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".menuItem_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(21);
											});
											
											jQuery("#tabs").find("div[id=pub_<?php echo $liveName[$i]; ?>]").find(".blox_check:checked").each( function() {
												allVals_<?php echo $liveName[$i]; ?>.push(jQuery(this).val());
												allTypes_<?php echo $liveName[$i]; ?>.push(10);
											});
											<?php } ?>
											
											var allVals = []; 	// push item name
											var allTypes = [];	// push item type
											var allNums = [];	// push connection number
											var total = 0;
											var step;
											var allValsLen = new Array();
											var flagEmpty = 0;
											
											<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
											
											allVals[<?php echo $i; ?>] = allVals_<?php echo $liveName[$i]; ?>;
											
											allTypes[<?php echo $i; ?>] = allTypes_<?php echo $liveName[$i]; ?>;
											
											allNums[<?php echo $i; ?>] = <?php echo $number[$i]; ?>;
											
											total += allVals_<?php echo $liveName[$i]; ?>.length;
											
											allValsLen[<?php echo $i; ?>] = allVals_<?php echo $liveName[$i]; ?>.length;
											
											if ( allVals_<?php echo $liveName[$i]; ?>.length != 0 ) 
												flagEmpty ++;
											<?php } ?>
											
											step = 100 / total;
											
											if ( flagEmpty == 0 ) {
												alert("You must select at least one post!");
												jQuery("div.xiUi-dialog").attr("id","xiblox");
											} else {
												jQuery( "#dialog" ).dialog({
													width	:	650,
													height	:	400,
													modal	:	true
												});
												
												jQuery("#xi_status").show();
												jQuery("#ajax_field").html("");
												jQuery("#spaceused1").progressBar(0);
												jQuery("#pub").attr("disabled","disabled");
												jQuery("#pub_all").attr("disabled","disabled");
												jQuery("#pub_all_post").attr("disabled","disabled");
												jQuery("#copy_all").attr("disabled","disabled");
												jQuery("#delete_all").attr("disabled","disabled");
												
												// proceed to copy selected item to the push site
												DoCopy( 0, 0, allValsLen, allVals, allTypes, allNums, step, 0, 0 );
											}
									}
								}
							});
						}
					});  
					
					// event to click "Push All To Destination" button
					jQuery("#pub_all").click( function() {
						var connName = jQuery("#selectAll").val();
						var result = confirm("Warning - you are about to push all site content to " + connName);
						if ( result == true ) {
							jQuery.ajax({
								type	:	"get",
								url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_publish_status.php?start=1", // check other users are using this plugin on same host
								success	:	function( val ) {
									
									var process_flag = 0;
									
									if ( val == 1 ) {
									
										var confirm_res = confirm("It appears that some other user has started a push, would you like to cancel that?");
										
										if ( confirm_res ) {
											process_flag = 1;
										}
									} else 
										process_flag = 1;
									
									
									if ( process_flag == 1 ) {
									
										jQuery( "#dialog" ).dialog({
											width	:	650,
											height	:	400,
											modal	:	true
										});
										
										jQuery("#xi_status").show();
										jQuery("#ajax_field").html("");
										jQuery("#spaceused1").progressBar(0);
										
										var allVals = [
										<?php
											
											// all posts ID
											$myposts = get_posts( array( 
												"posts_per_page" => -1,
												"post_status" => "any" )
											);
											foreach ( $myposts as $post ) : setup_postdata( $post );
												echo "\'" . $post->ID . "\',";
											endforeach;
											
											// all pages ID
											$myposts = get_pages();
											foreach ( $myposts as $post ) : setup_postdata( $post );
													echo "\'" . $post->ID . "\',";
											endforeach;
											
											// all links ID
											$sql = "SELECT * FROM " . $wpdb->base_prefix . "links";
											$links = $wpdb->get_results( $sql, ARRAY_A );
											if ( is_array( $links ) ) {
												foreach ( $links as $link ) {
													echo "\'" . $link["link_id"] . "\',";
												}
											}
											
											// all media ID
											$args = array(
												"post_type" => "attachment",
												"post_mime_type" => null,
												"numberposts" => -1
											);
											
											$myposts = get_posts( $args );
											foreach ( $myposts as $post ) : setup_postdata( $post ); 
												echo "\'" . $post->ID . "\',";
											endforeach;
											
											// all theme name
											$path = get_theme_root();
											if ( $handle = @opendir( $path ) ) {
												while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'" . $name . "\',";
														}
													}
												}
											}
											
											// all plugin name
											$path = ABSPATH . "wp-content/plugins/";
											if ( $handle = @opendir( $path ) ) {
												while ( false !== ($entry = readdir($handle))) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'" . $name . "\',";
														}
													}
												}
											}
											
											// all valid user ID
											$sql = "SELECT * FROM " . $wpdb->prefix . "users WHERE user_email != \'\'";
											$result = $wpdb->get_results( $sql, ARRAY_A );
											foreach ( $result as $post ) : 
													echo "\'" . $post["ID"] . "\',";
											endforeach;
											
											// all selected table and linked table name
											$sql = "SELECT table_name FROM xiblox_check_publish WHERE check_status = 1";
											$results = $wpdb->get_results( $sql );
											foreach ( $results as $index => $value ) {
												foreach ( $value as $tableName ) {
													echo "\'" . $tableName . "\',";
													
													$sql = "SELECT link_table FROM xiblox_link_table WHERE table_name = \'" . $tableName . "\'";
													$res = $wpdb->get_results( $sql, ARRAY_A );
													
													$linkArray = $res[0]["link_table"];
													$linkTable = explode( ",", $linkArray );
													
													for ( $j = 0; $j < count( $linkTable ); $j++ ) {
														echo "\'" . $linkTable[$j] . "\',";
													}
												}
											}
											
											// all menu name
											$sql = "SELECT b.name FROM " . $prefix . "term_taxonomy a LEFT JOIN " . $prefix . "terms b ON a.term_id = b.term_id WHERE a.taxonomy = \'nav_menu\'";
											$res = $wpdb->get_results( $sql, ARRAY_A );
											for ( $i = 0; $i < count( $res ); $i++ ) {
												$menu[$i] = $res[$i]["name"];
												echo "\'" . $menu[$i] . "\',";
											}
											
											// all blox name
											$sql = "SELECT blox_name FROM xiblox_tabs WHERE status = 1";
											$results = $wpdb->get_results( $sql );
											foreach ( $results as $index => $value ) {
												foreach ( $value as $bloxName ) {
													echo "\'" . $bloxName . "\',";
												}
											}
										?>
										];
										
										var allTypes = [
										<?php
											
											// all posts
											$myposts = get_posts( array( 
												"posts_per_page"  => -1,
												"post_status" => "any"
												)
											);
											foreach ( $myposts as $post ) : setup_postdata( $post );
													echo "\'0\',";
											endforeach;
											
											// all pages
											$myposts = get_pages();
											foreach ( $myposts as $post ) : setup_postdata( $post );
													echo "\'0\',";
											endforeach;
											
											// all links
											$sql = "SELECT * FROM " . $wpdb->base_prefix . "links";
											$links = $wpdb->get_results( $sql, ARRAY_A );
											if ( is_array( $links ) ) {
												foreach ( $links as $link ) {
													echo "\'1\',";
												}
											}
											
											// all media
											$args = array(
												"post_type" => "attachment",
												"post_mime_type" => null,
												"numberposts" => -1
											);
											$myposts = get_posts( $args );
											foreach ( $myposts as $post ) : setup_postdata( $post ); 
												echo "\'2\',";
											endforeach;
											
											// all themes
											$path = get_theme_root();
											if ( $handle = @opendir( $path ) ) {
											   while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'3\',";
														}
													}
												}
											}
											
											// all plugins
											$path = ABSPATH . "wp-content/plugins/";
											if ( $handle = @opendir( $path ) ) {
											   while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'4\',";
														}
													}
												}
											}
											
											// all valid users
											$sql = "SELECT * FROM " . $wpdb->prefix . "users WHERE user_email != \'\'";
											$result = $wpdb->get_results( $sql, ARRAY_A );
											foreach ( $result as $post ) : 
												echo "\'5\',";
											endforeach;
											
											// all selected table and linked tables
											$sql = "SELECT table_name FROM xiblox_check_publish WHERE check_status = 1";
											$results = $wpdb->get_results( $sql );
											foreach ( $results as $index => $value ) {
												foreach ( $value as $tableName ) {
													echo "\'6\',";
													
													$sql = "SELECT link_table FROM xiblox_link_table WHERE table_name = \'" . $tableName . "\'";
													$res = $wpdb->get_results( $sql, ARRAY_A );
													
													$linkArray = $res[0]["link_table"];
													$linkTable = explode( ",", $linkArray );
													
													for ( $j = 0; $j < count( $linkTable ); $j++ ) {
														echo "\'6\',";
													}
												}
											}
											
											// all menus
											$sql = "SELECT b.name FROM " . $prefix . "term_taxonomy a LEFT JOIN " . $prefix . "terms b ON a.term_id = b.term_id WHERE a.taxonomy = \'nav_menu\'";
											$res = $wpdb->get_results( $sql, ARRAY_A );
											for ( $i = 0; $i < count($res); $i++ ) {
												$menu[$i] = $res[$i]["name"];
												echo "\'20\',";
											}
											
											// all blox
											$sql = "SELECT blox_name FROM xiblox_tabs WHERE status = 1";
											$results = $wpdb->get_results( $sql );
											foreach ( $results as $index => $value ) {
												foreach ( $value as $bloxName ) {
													echo "\'10\',";
												}
											}
										?>
										];
										
										var total = allVals.length;
										var step = 100 / total;
										
										if ( allVals.length == 0 )
											alert("You must select at least one post!");
										else {
											jQuery("#pub").attr("disabled","disabled");
											jQuery("#pub_all").attr("disabled","disabled");
											jQuery("#pub_all_post").attr("disabled","disabled");
											jQuery("#copy_all").attr("disabled","disabled");
											jQuery("#delete_all").attr("disabled","disabled");
											
											// proceed to copy
											DoPostCopy(0, 0, allVals.length, allVals, allTypes, step, 0, 0);
										}
									}
								}
							});
						}
					});
					
					jQuery("div.xiUi-dialog").attr("id","xiblox");
					
					// event to click "Push all Except Posts To Destination" button	
					jQuery("#pub_all_post").click( function() {
						var connName = jQuery("#selectAll").val();
						var result = confirm("Warning - you are about to push all non-blog site content to " + connName);
						if ( result == true ) {
							jQuery.ajax({
								type	:	"get",
								url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_publish_status.php?start=1", // check other users are using this plugin on same host
								success	:	function( val ) {
								
									var process_flag = 0;
									
									if ( val == 1 ) {
									
										var confirm_res = confirm("It appears that some other user has started a push, would you like to cancel that?");
										
										if ( confirm_res ) {
											process_flag = 1;
										}
									} else 
										process_flag = 1;
									
									if ( process_flag == 1 ) {
									
										jQuery( "#dialog" ).dialog({
											width	:	650,
											height	:	400,
											modal	:	true
										});
										
										jQuery("#xi_status").show();
										jQuery("#ajax_field").html("");
										jQuery("#spaceused1").progressBar(0);
										
										var allVals = [
										<?php
										
											// all pages
											$myposts = get_pages();
											foreach ( $myposts as $post ) : setup_postdata( $post );
													echo "\'" . $post->ID . "\',";
											endforeach;
											
											// all links
											$sql = "SELECT * FROM " . $wpdb->base_prefix . "links";
											$links = $wpdb->get_results( $sql, ARRAY_A );
											if ( is_array( $links ) ) {
												foreach ( $links as $link ) {
													echo "\'" . $link["link_id"] . "\',";
												}
											}
											
											// all media
											$args = array (
												"post_type" => "attachment",
												"post_mime_type" => null,
												"numberposts" => -1
											);
											$myposts = get_posts( $args );
											foreach ( $myposts as $post ) : setup_postdata( $post ); 
												echo "\'" . $post->ID . "\',";
											endforeach;
											
											// all theme
											$path = get_theme_root();
											if ( $handle = @opendir( $path ) ) {
												while ( false !== ($entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'" . $name . "\',";
														}
													}
												}
											}
											
											// all plugins
											$path = ABSPATH . "wp-content/plugins/";
											if ( $handle = @opendir( $path ) ) {
												while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'" . $name . "\',";
														}
													}
												}
											}
											
											// all users
											$sql = "SELECT * FROM " . $wpdb->prefix . "users WHERE user_email != \'\'";
											$result = $wpdb->get_results( $sql, ARRAY_A );
											foreach ( $result as $post ) : 
												echo "\'" . $post["ID"] . "\',";
											endforeach;
											
											// all selected tables and linked tables
											$sql = "SELECT table_name FROM xiblox_check_publish WHERE check_status = 1";
											$results = $wpdb->get_results( $sql);
											foreach ( $results as $index => $value ) {
												foreach ( $value as $tableName ) {
													echo "\'" . $tableName . "\',";
													
													$sql = "SELECT link_table FROM xiblox_link_table WHERE table_name = \'" . $tableName . "\'";
													$res = $wpdb->get_results( $sql, ARRAY_A );
													
													$linkArray = $res[0]["link_table"];
													$linkTable = explode( ",", $linkArray );
													
													for ( $j = 0; $j < count( $linkTable ); $j++ ) {
														echo "\'" . $linkTable[$j] . "\',";
													}
												}
											}
											
											// all menus
											$sql = "SELECT b.name FROM " . $prefix . "term_taxonomy a LEFT JOIN " . $prefix . "terms b ON a.term_id = b.term_id WHERE a.taxonomy = \'nav_menu\'";
											$res = $wpdb->get_results( $sql, ARRAY_A );
											for ( $i = 0; $i < count( $res ); $i++ ) {
												$menu[$i] = $res[$i]["name"];
												echo "\'" . $menu[$i] . "\',";
											}
											
											// all blox
											$sql = "SELECT blox_name FROM xiblox_tabs WHERE status = 1";
											$results = $wpdb->get_results( $sql );
											foreach ( $results as $index => $value ) {
												foreach ( $value as $bloxName ) {
													echo "\'" . $bloxName . "\',";
												}
											}
										?>
										];
										var allTypes = [
										<?php
										
											// all pages
											$myposts = get_pages();
											foreach ( $myposts as $post ) : setup_postdata( $post );
												echo "\'0\',";
											endforeach;
											
											// all links
											$sql = "SELECT * FROM " . $wpdb->base_prefix . "links";
											$links = $wpdb->get_results( $sql, ARRAY_A );
											if ( is_array( $links ) ) {
												foreach ( $links as $link ) {
													echo "\'1\',";
												}
											}
											
											// all media
											$args = array(
												"post_type" => "attachment",
												"post_mime_type" => null,
												"numberposts" => -1
											);
											$myposts = get_posts( $args );
											foreach ( $myposts as $post ) : setup_postdata( $post ); 
												echo "\'2\',";
											endforeach;
											
											// all themes
											$path = get_theme_root();
											if ( $handle = @opendir( $path ) ) {
											   while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'3\',";
														}
													}
												}
											}
											
											// all plugins
											$path = ABSPATH . "wp-content/plugins/";
											if ( $handle = @opendir( $path ) ) {
											   while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'4\',";
														}
													}
												}
											}
											
											// all valid users
											$sql = "SELECT * FROM " . $wpdb->prefix . "users WHERE user_email != \'\'";
											$result = $wpdb->get_results( $sql, ARRAY_A );
											foreach ( $result as $post ) : 
													echo "\'5\',";
											endforeach;
											
											// all selected tables and linked tables
											$sql = "SELECT table_name FROM xiblox_check_publish WHERE check_status = 1";
											$results = $wpdb->get_results( $sql );
											foreach ( $results as $index => $value ) {
												foreach ( $value as $tableName ) {
													echo "\'6\',";
													
													$sql = "SELECT link_table FROM xiblox_link_table WHERE table_name = \'" . $tableName . "\'";
													$res = $wpdb->get_results( $sql, ARRAY_A );
													
													$linkArray = $res[0]["link_table"];
													$linkTable = explode( ",", $linkArray );
													
													for ( $j = 0; $j < count( $linkTable ); $j++ ) {
														echo "\'6\',";
													}
												}
											}
											
											// all menus
											$sql = "SELECT b.name FROM " . $prefix . "term_taxonomy a LEFT JOIN " . $prefix . "terms b ON a.term_id = b.term_id WHERE a.taxonomy = \'nav_menu\'";
											$res = $wpdb->get_results( $sql, ARRAY_A );
											for ( $i = 0; $i < count( $res ); $i++ ) {
												$menu[$i] = $res[$i]["name"];
												echo "\'20\',";
											}
											
											// all blox
											$sql = "SELECT blox_name FROM xiblox_tabs WHERE status = 1";
											$results = $wpdb->get_results( $sql );
											foreach ( $results as $index => $value ) {
												foreach ( $value as $bloxName ) {
													echo "\'10\',";
												}
											}
										?>
										];
										
										var total = allVals.length;
										var step = 100/total;
										
										if ( allVals.length == 0 )
											alert("You must select at least one post!");
										else {
											jQuery("#pub").attr("disabled","disabled");
											jQuery("#pub_all").attr("disabled","disabled");
											jQuery("#pub_all_post").attr("disabled","disabled");
											jQuery("#copy_all").attr("disabled","disabled");
											jQuery("#delete_all").attr("disabled","disabled");
											
											// proceed to copy
											DoPostCopy( 0, 0, allVals.length, allVals, allTypes, step, 0, 0 );
										}
									} 
								}
							});
						}
					});
					
					
					// event to click the "Copy Blog - Only Database" button
					jQuery("#copy_all_except_content").click( function() {
						var connName = jQuery("#selectAll").val();
						var result = confirm("Warning - you are about to copy this site to " + connName);
						if ( result == true ) {
							jQuery.ajax({
								type	:	"get",
								url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_publish_status.php?start=1", 
								success	:	function( val ) {
								
									var process_flag = 0;
									
									if ( val == 1 ) {
									
										var confirm_res = confirm("It appears that some other user has started a push, would you like to cancel that?");
										
										if ( confirm_res ) {
											process_flag = 1;
										}
									} else 
										process_flag = 1;
									
									
									if ( process_flag == 1 ) {
									
										jQuery("#dialog").dialog({
											width	:	650,
											height	:	400,
											modal	:	true
										});
										
										jQuery("#xi_status").show();
										jQuery("#ajax_field").html("");
										jQuery("#spaceused1").progressBar(0);
										
										var allVals = [
										<?php
											echo "\'\',"; // copying db mode	type:9
										?>
										];
										var allTypes = [
										<?php
											echo "\'91\',"; // copying db mode	type:9
										?>
										];
										var total = allVals.length;
										var step = 100/total;
										
										if ( allVals.length == 0 )
											alert("You must select at least one post!");
										else {
											jQuery("#pub").attr("disabled","disabled");
											jQuery("#pub_all").attr("disabled","disabled");
											jQuery("#pub_all_post").attr("disabled","disabled");
											jQuery("#copy_all").attr("disabled","disabled");
											jQuery("#copy_all_except_content").attr("disabled","disabled");
											jQuery("#delete_all").attr("disabled","disabled");
											
											DoPostCopy( 0, 0, allVals.length, allVals, allTypes, step, 0, 0 );
										}
									} 
								}
							});
						}
					});
					
					// event to click the "Copy Blog - All (Delete existing content)" button
					jQuery("#copy_all").click( function() {
						var connName = jQuery("#selectAll").val();
						var result = confirm("Warning - you are about to copy this site to " + connName);
						if ( result == true ) {
							jQuery.ajax({
								type	:	"get",
								url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_publish_status.php?start=1",
								success	:	function( val ) {
									
									var process_flag = 0;
									
									if ( val == 1 ) {
									
										var confirm_res = confirm("It appears that some other user has started a push, would you like to cancel that?");
										
										if ( confirm_res ) {
											process_flag = 1;
										}
									} else 
										process_flag = 1;
									
									
									if ( process_flag == 1 ) {
									
										jQuery("#dialog").dialog({
											width	:	650,
											height	:	400,
											modal	:	true
										});
										
										jQuery("#xi_status").show();
										jQuery("#ajax_field").html("");
										jQuery("#spaceused1").progressBar(0);
										var allVals = [
										<?php
											echo "\'\',"; // for deleting mode type:7
											for ( $i = 0; $i < count( $number ); $i++ ) {
											
												$sql = "SELECT * FROM xiblox_destination_info WHERE id = " . $number[$i];
												$result = $wpdb->get_results( $sql, ARRAY_A );
												
												$destination = $result[0]["destination_path"];
												$path = $destination . "wp-content/themes/";
												
												if ( $handle = @opendir( $path ) ) {
													while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) { 
																$name = $entry;
																if ( $name != "twentyeleven" )
																	echo "\'".$path.$name."\',";
															}
														}
													}
												}
												
												$path = $destination . "wp-content/plugins/";
												
												if ( $handle = @opendir( $path ) ) {
													while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
																$name = $entry;
																if ( $name != "akismetBlock" )
																	echo "\'" . $path . $name . "\',";	//type:8
															}
														}
													}
												}
											}
											
											echo "\'\',"; // copying db mode	type:9
											
											$args = array(
												"post_type" => "attachment",
												"post_mime_type" => null,
												"numberposts" => -1
											);
											$myposts = get_posts( $args );
											foreach ( $myposts as $post ) : setup_postdata( $post ); 
												echo "\'" . $post->ID . "\',"; //type:2
											endforeach;
											
											$path = get_theme_root();
											if ( $handle = @opendir( $path ) ) {
												while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'" . $name . "\',"; //type:3
														}
													}
												}
											}
											
											$path = ABSPATH . "wp-content/plugins/";
											if ( $handle = @opendir( $path ) ) {
											   while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'" . $name . "\',"; //type:4
														}
													}
												}
											}
											echo "\'\'";
										?>
										];
										var allTypes = [
										<?php
											echo "\'7\',"; // for deleting mode type:7
											
											for ( $i = 0; $i < count( $number ); $i++ ) {
												$sql = "SELECT * FROM xiblox_destination_info WHERE id = " . $number[$i];
												$result = $wpdb->get_results( $sql, ARRAY_A );
												
												$destination = $result[0]["destination_path"];
												$path = $destination . "wp-content/themes/";
												
												if ( $handle = @opendir( $path ) ) {
												   while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) { 
																$name = $entry;
																if ( $name != "twentyeleven" )
																	echo "\'8\',";
															}
														}
													}
												}
												
												$path = $destination . "wp-content/plugins/";
												
												if ( $handle = @opendir( $path ) ) {
												   while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
																$name = $entry;
																if ( $name != "akismetBlock" )
																	echo "\'8\',";	//type:8
															}
														}
													}
												}
											}
											echo "\'9\',"; // copying db mode	type:9
											
											$args = array(
												"post_type" => "attachment",
												"post_mime_type" => null,
												"numberposts" => -1
											);
											$myposts = get_posts( $args );
											foreach ( $myposts as $post ) : setup_postdata( $post ); 
												echo "\'2\',"; //type:2
											endforeach;
											
											$path = get_theme_root();
											if ( $handle = @opendir( $path ) ) {
											   while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'3\',"; //type:3
														}
													}
												}
											}
											
											$path = ABSPATH . "wp-content/plugins/";
											if ( $handle = @opendir( $path ) ) {
											   while ( false !== ( $entry = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry ) ) {
														if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
															$name = $entry;
															echo "\'4\',"; //type:4
														}
													}
												}
											}
											echo "\'\'";
										?>
										];
										
										var total = allVals.length;
										var step = 100/total;
										
										if ( allVals.length == 0 ) 
											alert("You must select at least one post!");
										else {
											jQuery("#pub").attr("disabled","disabled");
											jQuery("#pub_all").attr("disabled","disabled");
											jQuery("#pub_all_post").attr("disabled","disabled");
											jQuery("#copy_all").attr("disabled","disabled");
											jQuery("#copy_all_except_content").attr("disabled","disabled");
											jQuery("#delete_all").attr("disabled","disabled");
											
											DoPostCopy( 0, 0, allVals.length, allVals, allTypes, step, 0, 0 );
										}
									} 
								}
							});
						}
					});
					
					jQuery("#delete_all").click( function() {
						var connName = jQuery("#selectAll").val();
						var result = confirm("Warning - you are about to delete all content from  " + connName);
						
						if ( result == true ) {
							jQuery.ajax({
								type	:	"get",
								url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_publish_status.php?start=1",
								success	:	function( val ) {
								
									var process_flag = 0;
									
									if ( val == 1 ) {
									
										var confirm_res = confirm("It appears that some other user has started a push, would you like to cancel that?");
										
										if ( confirm_res ) {
											process_flag = 1;
										}
									} else 
										process_flag = 1;
									
									
									if ( process_flag == 1 ) {
									
										jQuery("#dialog").dialog({
											width	:	650,
											height	:	400,
											modal	:	true
										});
										
										jQuery("#xi_status").show();
										jQuery("#ajax_field").html("");
										jQuery("#spaceused1").progressBar(0);
										
										var allVals = [
										<?php
											echo "\'\',"; // for deleting mode type:71
											for ( $i = 0; $i < count( $number ); $i++ ) {
												$sql = "SELECT * FROM xiblox_destination_info WHERE id = " . $number[$i];
												$result = $wpdb->get_results( $sql, ARRAY_A );
												
												$destination = $result[0]["destination_path"];
												$path = $destination . "wp-content/themes/";
												
												if ( $handle = @opendir( $path ) ) {
												   while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( (strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) { 
																$name = $entry;
																if ( $name != "twentyeleven" )
																	echo "\'" . $path . $name."\',";
															}
														}
													}
												}
												
												$path = $destination . "wp-content/plugins/";
												
												if ( $handle = @opendir( $path ) ) {
												   while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
																$name = $entry;
																if ( $name != "akismetBlock" )
																	echo "\'" . $path . $name . "\',";	//type:8
															}
														}
													}
												}
												
												$path = $destination . "wp-content/uploads/";
												
												if ( $handle = @opendir( $path ) ) {
												   while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
																$name = $entry;
																if ( $name != "akismetBlock" )
																	echo "\'" . $path . $name . "\',";	//type:8
															}
														}
													}
												}
											}
											
										?>
										];
										var allTypes = [
										<?php
											echo "\'71\',"; // for deleting mode type:71
											for ( $i = 0; $i < count( $number ); $i++ ) {
												$sql = "SELECT * FROM xiblox_destination_info WHERE id = " . $number[$i];
												$result = $wpdb->get_results( $sql, ARRAY_A );
												
												$destination = $result[0]["destination_path"];
												$path = $destination . "wp-content/themes/";
												
												if ( $handle = @opendir( $path ) ) {
												   while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) { 
																$name = $entry;
																if ( $name != "twentyeleven" )
																	echo "\'8\',";
															}
														}
													}
												}
												
												$path = $destination . "wp-content/plugins/";
												
												if ( $handle = @opendir( $path ) ) {
												   while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
																$name = $entry;
																if ( $name != "akismetBlock" )
																	echo "\'8\',";	//type:8
															}
														}
													}
												}
												
												$path = $destination . "wp-content/uploads/";
												
												if ( $handle = @opendir( $path ) ) {
												   while ( false !== ( $entry = readdir( $handle ) ) ) {
														if ( is_dir( $path . "/" . $entry ) ) {
															if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
																$name = $entry;
																if ( $name != "akismetBlock" )
																	echo "\'8\',";	//type:8
															}
														}
													}
												}
											}			
										?>
										];
										
										var total = allVals.length;
										var step = 100/total;
										
										if ( allVals.length == 0 )
											alert("You must select at least one post!");
										else {
											jQuery("#pub").attr("disabled","disabled");
											jQuery("#pub_all").attr("disabled","disabled");
											jQuery("#pub_all_post").attr("disabled","disabled");
											jQuery("#copy_all").attr("disabled","disabled");
											jQuery("#copy_all_except_content").attr("disabled","disabled");
											jQuery("#delete_all").attr("disabled","disabled");
											
											DoPostCopy( 0, 0, allVals.length, allVals, allTypes, step, 0, 0 );
										}
									}
								}
							});
						}
					});
					
					jQuery("#xi_status").click( function() {
						jQuery( "#dialog" ).dialog({
							width	:	650,
							height	:	400,
							modal	:	true
						});
					});
					
					// save the session value
					jQuery("#tabs").tabs({
					   select	:	function( event, ui ) {
						   jQuery.ajax({
								url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_save_session.php?tab=" + ui.index,
								method	:	"get",
								success	:	function( msg ) { }
						   });
					  }
					});
					
					// select the clicked tab when refresh the page
					jQuery("#tabs").tabs("select", <?php $index = $_SESSION["tab_index"]; 
						if ( $index == "" )
							echo 0;
						else 
							echo $index;
						?> 
					);
					
					if ( jQuery("#tabs .xiUi-tabs-active").attr("id") == "custom" ) {
						jQuery("#pub").attr("style", "display: none");
						jQuery("#pubBlox").attr("style", "display: block");
					}
				});
				
				/**
				 * toggle the check status
				 */
				function ReverseLink( obj ) {
					
					var s_id = obj.value.replace( " ", "_" );
					
					jQuery(obj).parent().parent().parent().find("input[id=" + s_id + "]").prop("checked", !jQuery(obj).parent().parent().parent().find("input[id=" + s_id + "]").prop("checked"));
				} 
				
				/**
				 * copy the selected item
				 */
				function DoCopy( progress, iterator, limit, allvals, alltypes, allNums, step, flagCnt, flagWarning ) {
					var number = jQuery("#number").val();
					var flagEnd = <?php echo count($liveName); ?>;
					if ( flagCnt < flagEnd ) {
						jQuery.ajax({
							url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_copy.php?type=" + alltypes[flagCnt][iterator] + "&value=" + allvals[flagCnt][iterator] + "&number=" + allNums[flagCnt],
							method	:	"get",
							success	:	function( msg ) {
							
								if ( msg.indexOf("Warning</b>:") >= 0 ) 
									flagWarning = 1;
									
								var cont = jQuery("#ajax_field").html();
								
								jQuery("#ajax_field").html( cont + msg + "<br />" );
								
								jQuery("#ajax_field").stop().animate({ 
									scrollTop	:	jQuery("#ajax_field")[0].scrollHeight
								},800);
								
								iterator ++;
								
								if ( iterator >= limit[flagCnt] ) {
									iterator = 0;
									flagCnt ++;
								}
								progress = progress + step;
								
								jQuery("#spaceused1").progressBar(progress);
								
								DoCopy( progress, iterator, limit, allvals, alltypes, allNums, step, flagCnt, flagWarning ); 
								
							}
						});	
					} else {
						var cont = jQuery("#ajax_field").html();
						
						jQuery("#spaceused1").progressBar(100);
						jQuery("#ajax_field").html(cont + "Completed!");
						jQuery("#pub").removeAttr("disabled");
						jQuery("#pub_all").removeAttr("disabled");
						jQuery("#pub_all_post").removeAttr("disabled");
						jQuery("#copy_all").removeAttr("disabled");
						jQuery("#delete_all").removeAttr("disabled");
						jQuery("#spaceused1_percentText").html("Completed");
						jQuery(".post_check").removeAttr("checked");
						jQuery(".page_check").removeAttr("checked");
						jQuery(".links_check").removeAttr("checked");
						jQuery(".attach_check").removeAttr("checked");
						jQuery(".themes_check").removeAttr("checked");
						jQuery(".plugin_check").removeAttr("checked");
						jQuery(".user_check").removeAttr("checked");
						jQuery(".table_check").removeAttr("checked");
						jQuery(".menu_check").removeAttr("checked");
						jQuery(".menuItem_check").removeAttr("checked");
						jQuery(".blox_check").removeAttr("checked");
						jQuery("#checkall").removeAttr("checked");
						jQuery("#checkall_page").removeAttr("checked");
						jQuery("#checkall_link").removeAttr("checked");
						jQuery("#checkall_attachment").removeAttr("checked");
						jQuery("#checkall_themes").removeAttr("checked");
						jQuery("#checkall_plugin").removeAttr("checked");
						jQuery("#checkall_users").removeAttr("checked");
						jQuery("#checkall_tables").removeAttr("checked");
						jQuery("#checkall_menus").removeAttr("checked");
						jQuery("#checkall_menuItems").removeAttr("checked");
						jQuery("#checkall_blox").removeAttr("checked");
						
						<?php for ( $i = 0; $i < count($liveName); $i++ ) { ?>
							jQuery("#no_post_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_page_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_link_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_media_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_theme_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_plugin_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_user_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_table_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_menu_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_blox_<?php echo $liveName[$i]; ?>").html(0);
						<?php } ?>
						
						jQuery(".xiUi-dialog-titlebar-close").attr( "onclick", "PageRefresh();" );
						
						if ( flagWarning == 1 ) 
							alert("Errors occurred during the Push");
						else {
							jQuery.ajax({
								type	:	"get",
								url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_publish_status.php?end=1", // check other users are using this plugin on same host
								success	:	function( val ) {
									location.reload();
								}
							});
							
						}
						
					}
					
					jQuery("div.xiUi-dialog").attr("id", "xiblox");
				}
				
				/**
				 * refresh the page
				 */
				function PageRefresh() {
					location.reload();
				} 
				
				// copy the selected post item
				function DoPostCopy( progress, iterator, limit, allvals, alltypes, step, flagWarning, flagError ) {
				
					var radioButtons = jQuery("input:radio[name=\'selectAll\']");
					
					<?php if ( count($number) > 1 ) { ?>
						var number = radioButtons.filter(\':checked\').attr("indexNum");
					<?php } else { ?>
						var number = <?php echo $number[0]; ?>;
					<?php } ?>
					
					if ( iterator < limit ) {
						jQuery.ajax({
							url		: 	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_copy_all.php?type=" + alltypes[iterator] + "&value=" + allvals[iterator] + "&number=" + number,
							method	:	"get",
							success	:	function( msg ) {
								if ( msg.indexOf("Warning</b>:") >= 0 ) flagWarning = 1;
								if ( msg.indexOf("Fatal error: Maximum execution time") >= 0 ) flagError = 1;
								var cont = jQuery("#ajax_field").html();
								jQuery("#ajax_field").html(cont + msg + "<br />");
								jQuery("#ajax_field").stop().animate({ scrollTop: jQuery("#ajax_field")[0].scrollHeight},800)
								iterator ++;
								progress = progress + step;
								jQuery("#spaceused1").progressBar(progress);
								DoPostCopy( progress, iterator, limit, allvals, alltypes, step, flagWarning, flagError );
							}
						});
					}
					else {
						var cont = jQuery("#ajax_field").html();
						jQuery("#spaceused1").progressBar(100);
						jQuery("#ajax_field").html(cont+"Completed!");
						jQuery("#pub").removeAttr("disabled");
						jQuery("#pub_all").removeAttr("disabled");
						jQuery("#pub_all_post").removeAttr("disabled");
						jQuery("#copy_all").removeAttr("disabled");
						jQuery("#delete_all").removeAttr("disabled");
						jQuery("#spaceused1_percentText").html("Completed");
						jQuery(".post_check").removeAttr("checked");
						jQuery(".page_check").removeAttr("checked");
						jQuery(".links_check").removeAttr("checked");
						jQuery(".attach_check").removeAttr("checked");
						jQuery(".themes_check").removeAttr("checked");
						jQuery(".plugin_check").removeAttr("checked");
						jQuery(".user_check").removeAttr("checked");
						jQuery(".table_check").removeAttr("checked");
						jQuery(".menu_check").removeAttr("checked");
						jQuery(".menuItem_check").removeAttr("checked");
						jQuery(".blox_check").removeAttr("checked");
						jQuery("#checkall").removeAttr("checked");
						jQuery("#checkall_page").removeAttr("checked");
						jQuery("#checkall_link").removeAttr("checked");
						jQuery("#checkall_attachment").removeAttr("checked");
						jQuery("#checkall_themes").removeAttr("checked");
						jQuery("#checkall_plugin").removeAttr("checked");
						jQuery("#checkall_users").removeAttr("checked");
						jQuery("#checkall_tables").removeAttr("checked");
						jQuery("#checkall_menus").removeAttr("checked");
						jQuery("#checkall_menuItems").removeAttr("checked");
						jQuery("#checkall_blox").removeAttr("checked");
						<?php for ( $i = 0; $i < count($liveName); $i++ ) { ?>
							jQuery("#no_post_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_page_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_link_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_media_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_theme_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_plugin_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_user_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_table_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_menu_<?php echo $liveName[$i]; ?>").html(0);
							jQuery("#no_blox_<?php echo $liveName[$i]; ?>").html(0);
						<?php } ?>
						if ( flagWarning == 1 )
							alert("Errors occurred during the Push");
						else {
							if ( flagError == 1 )
								alert("Please check php ini file or proceed to push using Tables tab.")
							else {
								jQuery.ajax({
									type	:	"get",
									url		:	"<?php echo plugins_url(); ?>/XIBLOX/includes/xi_publish_status.php?end=1", // check other users are using this plugin on same host
									success	:	function( val ) {
										location.reload();
									}
								});
								
							}
						}
					}
					jQuery("div.xiUi-dialog").attr("id","xiblox");
				}

				function Preview( src, width, height ) {
					if ( width > 800 )
						img_width = 800;
					else
						img_width = width;
					
					if ( height > 600 )
						img_height = 600;
					else
						img_height = height;
						
					jQuery( "#preview_image" ).dialog({
							width	:	img_width,
							height	:	img_height,
							modal	:	true
					});
					jQuery("#pre_img").attr("src",src);
					jQuery("#pre_img").attr("width",width);
					jQuery("#pre_img").attr("height",height);
					
					jQuery("div.xiUi-dialog").attr("id","xiblox");
				} 
			</script> 
			<script type="text/javascript" src="<?php echo plugins_url(); ?>/XIBLOX/assets/js/jquery.progressbar.js"></script> 

			<div id="xi_select_status">
			<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
				<span style="font-weight: bold; font-size: 15px;"><?php echo $liveName[$i]; ?></span> : Number of selected <b>Posts:</b> <span id="no_post_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<b>Pages:</b> <span id="no_page_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<b>Links:</b> <span id="no_link_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<b>Media:</b> <span id="no_media_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<b>Themes:</b> <span id="no_theme_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<b>Plugins:</b> <span id="no_plugin_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<b>Users:</b> <span id="no_user_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<b>Tables:</b> <span id="no_table_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<b>Menus:</b> <span id="no_menu_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<b>Blox:</b> <span id="no_blox_<?php echo $liveName[$i]; ?>">0</span>&nbsp;&nbsp;
				<br />
			<?php } ?>
			</div> 
			</br>

			<div id="xi_post_form" style="width:100%;float:left">
			
				<form name="form1" method="post" action="<?php echo str_replace( "%7E", "~", $_SERVER["REQUEST_URI"]); ?>">
				
					<div id="xiblox">
					
						<div id="tabs">
						
							<ul id="xi_all">
							
								<li><a href="#publish" id="xi_publish">Push</a></li>
								<li><a href="#posts" id="xi_posts">Posts</a></li>
								<li><a href="#pages" id="xi_pages">Pages</a></li>
								<li><a href="#links" id="xi_links">Links</a></li>
								<li><a href="#media" id="xi_media">Media</a></li>
								<li><a href="#themes" id="xi_themes">Themes</a></li>
								<li><a href="#plugins" id="xi_plugins">Plugins</a></li>
								<li><a href="#users" id="xi_users">Users</a></li>
								<li><a href="#tables" id="xi_tables">Tables</a></li>
								<li><a href="#menus" id="xi_menus">Menu</a></li>
								<li><a href="#blox" id="xi_blox">Blox</a></li>
								<li><a href="#unpushed" id="xi_unpushed">Unpushed</a></li>
								
								<?php
									$sql = "SELECT * FROM xiblox_tabs WHERE menu = 4";
									$results = $wpdb->get_results( $sql, ARRAY_A );
									
									if ( count( $results ) != 0 ) {
									
										$index = 0;
										
										foreach ( $results as $result ) {
										
											if ( strpos( "' . $args . '", $result["blox_name"]) == 0 ) 
												continue;
												
											echo "<li id=\'custom\' bloxName=\'" . $result["blox_name"] . "\'><a href=\'#custom_tab" . $result["id"] . "\'>" . $result["blox_name"] . "</a></li>";
											
											$index ++;
										}
									}
								 ?>
							</ul>
							
							<div id="publish">
							
								<?php if ( count( $number ) > 1 ) { ?>
								Select Target Site:
								
								<br />
								
								<div>
								
									<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
									<input type="radio" name="selectAll" indexNum="<?php echo $number[$i]; ?>" id="selectAll" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_all"] == $liveName[$i] && $_SESSION["select_all"] != "" ) echo "checked"; if ( $_SESSION["select_all"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									
									<br />
									<?php } ?>
								</div>
								
								<?php } else { ?>
								
								<input type="hidden" name="selectAll" indexNum="<?php echo $number[0]; ?>" id="selectAll" value="<?php echo $liveName[0]; ?>">
								<?php } ?>
								
								<p><input type="button" name="pub_all_submit" value="Push All To Destination" class="button-primary" id="pub_all" /></p>
								<p><input type="button" name="pub_all_post_submit" value="Push All Except Posts To Destination" class="button-primary" id="pub_all_post" /></p>
								<p><input type="button" name="copy_all" value="Copy Blog - All ( Delete existing content )" class="button-primary" id="copy_all" /></p>
								<p><input type="button" name="copy_all_except_content" value="Copy Blog - Only Database" class="button-primary" id="copy_all_except_content" /></p>
								<p><input type="button" name="delete_all" value="Delete All From Destination Blog" class="button-primary" id="delete_all" /></p>
							</div>
							
							
							
							
							
							
							
							
							<div id="posts"> 
							
								<h3>&nbsp;&nbsp;&nbsp;Posts&nbsp;&nbsp;&nbsp;
									<span><a id="xi_add_new" href="post-new.php?post_type=post" target="_blank" >Add New</a></span>
								</h3>
								
								<select name="category">
									<option value="">Show All</option>
								<?php
									$category = get_categories();
									
									foreach ( $category as $cat ) {
									
										$selected = "";
										
										if ( isset( $_POST["category"] ) ) {
										
											$term_id = $_POST["category"];
											
											if ( $term_id == $cat->term_id )
												$selected = "selected=\'selected\'";
										}	
										
										echo "<option value=\'" . $cat->term_id . "\' " . $selected . ">" . $cat->name . "</option>";
									}
								?>
								</select>&nbsp;
								
								<input id="post-query-submit" class="button-secondary" type="submit" value="Filter" name="filter">
								</br></br>
								
								<?php if ( count( $number ) > 1 ) { ?>
								Select Target Site:
								<br />
								
								<div>
								
								<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
									<input type="radio" name="selectPost" id="selectLive" index="<?php echo $number[$i]; ?>" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_post"] == $liveName[$i] && $_SESSION["select_post"] != "" ) echo "checked"; if ( $_SESSION["select_post"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									<br />
								<?php } ?>
								</div>
								
								<br />
								
								<?php } 
								
									for ( $i = 0; $i < count( $number ); $i++ ) { 
										$un_post_cnt[$i] = 0;
								?>
								
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_post"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
								
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
									
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall"></th>
											<th id="xi_title" align="left" width="20%">Post Name</th>
											<th id="xi_categories" align="left" width="20%">Category</th>
											<th id="xi_categories" align="left" width="20%">Attachment</th>
											<th id="xi_categories" align="left" width="20%">Push Status</th>
											<th id="xi_categories" align="left" width="20%">Push Date</th>
										</tr>
										
										<?php
											global $post;
											$tmp_post = $post;
											
											if ( isset( $_POST["category"] ) )
												$category = $_POST["category"];
											else
												$category = "";
											
											// custom post types
											$args = array( "public" => true, "_builtin" => false );
											$output = "names"; // names or objects, note names is the default
											$operator = "and"; // "and" or "or"
											$post_types = get_post_types( $args, $output, $operator ); 
											
											$cptCount = 1;
											$cpt[0] = "post";
											
											foreach ( $post_types  as $post_type ) {
											
												$cpt[$cptCount] = $post_type;
												$cptCount ++;
											}
											
											// get destination information
											$sql = "SELECT * FROM xiblox_destination_info WHERE id = " . $number[$i];
											$result = $wpdb->get_results( $sql, ARRAY_A );
											$destination_url = $result[0]["destination_url"];
											$db_host = $result[0]["db_host"];
											$db_name = $result[0]["db_name"];
											$db_user = $result[0]["db_user"];
											$db_password = $result[0]["db_password"];
											$db_prefix = $result[0]["db_prefix"];
											$destination_path = $result[0]["destination_path"];
											
											$db_conn = new DatabaseManager("mysql", $db_host, $db_user, $db_password, $db_name);
											
											$global_db_conn[$i] = $db_conn;
											
											for ( $postCount = 0; $postCount < $cptCount; $postCount++ ) {
											
												if ( $cpt[$postCount] == "options" ) 
													continue;
													
												$myposts = get_posts(array(
													"category" => $category,
													"post_status" => "publish",
													"post_type" => $cpt[$postCount],
													"posts_per_page" => -1,
													"orderby" => "title", 
													"order" => "ASC")
												);
												
												foreach ( $myposts as $post ) : setup_postdata( $post ); 
												
													global $wpdb;
													$baseurl = get_site_url();
													$tmp1url = str_replace( "www.", "", $baseurl );
													
													$sql = "SELECT a.post_modified FROM " . $prefix . "posts a WHERE post_title = \'" . $db_conn->Res( $post->post_title) . "\' AND post_name = \'" . $db_conn->Res( $post->post_name) . "\' AND post_status = \'" . $post->post_status . "\' AND post_type = \'" . $post->post_type . "\'";
													$res = $db_conn->queryArray( $sql );
													
													if ( count( $res ) > 0 ) {
													
														$livePostModified = $res[0]["post_modified"];
														
														if ( $livePostModified > $post->post_modified ) { 
															$flagLive = 1; 
															$color = "red"; 
														}
														
														if ( $livePostModified == $post->post_modified ) { 
															$flagEqual = 1; 
															$color = "black"; 
														}
														
														if ( $livePostModified < $post->post_modified ) { 
															$flagStaging = 1; 
															$color = "blue"; 
														}
														
													} else { 
													
														$color = "blue"; 
														$flagStaging = 1; 
													}
													
												?>
										<tr>
											<td colspan="6" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $post->ID; ?>" name="posts[]" class = "post_check" <?php
														if ( strcmp( $post->post_status , "pending" ) == 0 || strcmp( $post->post_status ,"draft" ) == 0 )
															echo "style=\'display:none;\'";
													?>/>
											</th>
											<td align="left">
												<a href="<?php echo get_edit_post_link( $post->ID ); ?> ">
													<b style="color: <?php echo $color; ?>">
													
													<?php 
														the_title(); 
														
														if ( $cpt[$postCount] != "post" ) 
															echo "(" . $cpt[$postCount] . ")"; 
															
														if ( strcmp( $post->post_status ,"pending" ) == 0 || strcmp( $post->post_status ,"draft" ) == 0 ) {
															$flagDraft = 1;
															echo " <span style=\'color:#000\'>" . $post->post_status . "</span>";
														}
														
														// register to unpushed item
														if ( $color == "blue" ) {
															$un_post_cnt[$i] = 1;
														}
													?>
													</b>
												</a>
												<?php
													$link = get_page_link( $post->ID );
													echo "<a target = \'_blank\' href=\'" . $link . "\' id=\'xi_preview_page\'>Preview&nbsp;&nbsp;&nbsp;</a>";
												?>
											</td>
											<td align="left">
												<a>
													<b>
													<?php 
														$cat = get_the_category( $post->ID );
														
														foreach ( $cat as $key => $cats ) {
															echo $cats->name;
															if ( $key < ( count( $cat ) - 1 ) )
																echo ",";
														}
													 ?>
													</b>
												</a>
											</td>
											<td align="left">
											<?php
												$args = array(
													"post_type" => "attachment",
													"numberposts" => null,
													"post_status" => null,
													"post_parent" => $post->ID
												);
												
												$attachments = get_posts($args);
												if ( $attachments ) 
													echo count( $attachments ) . " Attachments";
												else
													echo "No attachments";
											?>
											</td>
											<td align="left">
											<?php
												$sql = "SELECT a.id FROM " . $prefix . "posts a WHERE post_title = \'" . $db_conn->Res( $post->post_title) . "\'";
												$res = $db_conn->queryArray( $sql );
												if ( count( $res ) > 0 ) {
													if ( $flagDraft != 1 ) {
														if ( $flagLive == 1 ) echo "Destination is Newer";
														if ( $flagEqual == 1 ) echo "Pushed";
														if ( $flagStaging == 1 ) echo "Unpushed";
													}
												} else echo "Unpushed";
											?>
											</td>
											<td>
											<?php
												$sql = "SELECT a.post_modified FROM " . $prefix . "posts a WHERE post_title = \'" . $db_conn->Res( $post->post_title ) . "\'";
												$res = $db_conn->queryArray( $sql );
												if ( count( $res ) > 0 ) {
													if ( ( $flagDraft != 1 ) && ( $flagStaging != 1 ) ) 
														echo $res[0]["post_modified"];
												}
												$flagDraft = 0;
												$flagLive = 0;
												$flagEqual = 0;
												$flagStaging = 0;
												$color = "black";
											?>
											</td>
										</tr>
										<?php endforeach; 
										}
										$db_conn = null;
										?>
									</table>
								</div>
								
								<?php } ?>
								
							</div>
							
							
							
							
							<div id="pages">
							
								<h3>&nbsp;&nbsp;&nbsp;Pages&nbsp;&nbsp;&nbsp;
									<span><a id="xi_add_new" href="post-new.php?post_type=page" target="_blank" >Add New</a></span>
								</h3>
								
								<?php if ( count( $number ) > 1 ) { ?>
								Select Target Site:
								<br />
								
								<div>
									<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
									
									<input type="radio" name="selectPage" id="selectLive" index="<?php echo $number[$i]; ?>" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_page"] == $liveName[$i] && $_SESSION["select_page"] != "" ) echo "checked"; if ( $_SESSION["select_page"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									
									<br />
									
									<?php } ?>
								</div>
								
								<br />
								
								<?php 
									}
									
									for ( $i = 0; $i < count( $number ); $i++ ) {
									
										$un_page_cnt[$i] = 0;
										
										$db_conn = $global_db_conn[$i];
								?>
								
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_page"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
								
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0" >
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_page"></th>
											<th id="xi_title" align="left" id="treecontrol">Page Name</th>
										</tr>
									</table>
									
									<ul id="xi_navigation" class="xi_page_tree">
										<?php
											$myposts = get_pages();
											$pages_count = count( $myposts );
											$tags = array();
											
											for ( $j = 0; $j < count( $myposts ); $j++ ) {
											
												setup_postdata( $myposts[$j] );
												$post = $myposts[$j];
												$post_parent = $post->post_parent;
												
												if ( ( $j + 1 ) < $pages_count )
													$next_parent = $myposts[$j + 1]->post_parent;
												else
													$next_parent = 0;
										?>
										<li>
											<input type="checkbox" value="<?php echo $post->ID; ?>" name="posts[]" class = "page_check" <?php
												if ( strcmp( $post->post_status ,"pending") == 0 || strcmp( $post->post_status, "draft" ) == 0 )
													echo "style=\'display:none;\'"; ?>/>
											<a href="<?php echo get_edit_post_link( $post->ID ); ?>">
												<b>
												<?php 
													$sql = "SELECT a.post_modified FROM " . $prefix . "posts a WHERE post_type = \'page\' AND post_title = \'" . $db_conn->Res( $post->post_title ) . "\' AND post_name = \'" . $db_conn->Res( $post->post_name ) . "\' AND post_parent=\'" . $db_conn->Res( $post->post_parent ) . "\' AND post_status = \'" . $post->post_status . "\'";
													$res = $db_conn->queryArray( $sql );
													
													if ( count( $res ) > 0 ) {
													
														$liveDate = $res[0]["post_modified"];
														
														if ( $liveDate > $post->post_modified ) {
															$color = "red"; 
															$flagPublish = 1; 
														}
														
														if ( $liveDate < $post->post_modified ) { 
															$color = "blue"; 
															$flagPublish = 0; 
														}
														
														if ( $liveDate == $post->post_modified ) { 
															$color = "black"; 
															$flagPublish = 1; 
														}
													} else { 
														$color = "blue"; 
														$flagPublish = 0;
													}
												
													echo "<span style=\'color: " . $color . "\'>" . $post->post_title . "</span>";
												
													if ( strcmp( $post->post_status, "pending" ) == 0 || strcmp( $post->post_status, "draft") == 0 )
														echo " - (<span style=\'color:#000\'>" . $post->post_status . "</span>)";
													
													
												?>
												</b>
											</a>&nbsp;&nbsp;&nbsp;
											<?php
												$link = get_page_link( $post->ID );
												echo "<a target = \'_blank\' href=\'" . $link . "\' id=\'xi_preview_page\'>Preview&nbsp;&nbsp;&nbsp;</a>";
												
												if ( $flagPublish == 1 ) {
													if ( $color == "red" ) echo "Destination is Newer&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
													if ( $color == "black" ) echo "Pushed&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
												} else {
													echo "Unpushed&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
													$un_page_cnt[$i] = 1;
												}
													
												if ( $flagPublish == 1 )  
													echo $liveDate; 
													
												$color = "";
												$flagPublish = 0;
												
												if ( $post_parent != $next_parent ) {
												
													if ( in_array( $next_parent, $tags ) ) {
													
														$key = array_search( $next_parent, $tags );
														$tag_count = count( $tags ) - $key;
														echo "</li>";
														for ( $k = 0; $k < $tag_count; $k++ ) {
															echo "</ul></li>";
															array_pop( $tags );
														}
													} else {
														array_push( $tags, $post_parent );
														echo "<ul>";
													}
												} else
													echo "</li>";
											}	
											?>
											<?php $post = $tmp_post; ?>
									</ul>
								</div>
								<?php 
									}
									$db_conn = null;
								?>
							</div>
							
							
							
							
							<div id="links">
							
								<h3>&nbsp;&nbsp;&nbsp;Links&nbsp;&nbsp;&nbsp;
									<span><a id="xi_add_new" href="link-add.php" target="_blank" >Add New</a></span>
								</h3>
								
								<?php if ( count( $number ) > 1 ) { ?>
								Select Target Site:
								<br />
								
								<div>
								
								<?php 
									for ( $i = 0; $i < count( $liveName ); $i++ ) { 
								?>
									<input type="radio" name="selectLink" id="selectLive" index="<?php echo $number[$i]; ?>" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_link"] == $liveName[$i] && $_SESSION["select_link"] != "" ) echo "checked"; if ( $_SESSION["select_link"] == "" && $i == 0 ) echo "checked"; ?>> 
										<?php echo $liveName[$i]; ?>
									<br />
								<?php 
									} 
								?>
								</div>
								
								<br />
								
								<?php 
									}
									
									for ( $i = 0; $i < count( $number ); $i++ ) {
									
										$un_link_cnt[$i] = 0;
										
										$db_conn = $global_db_conn[$i];
										
								?>
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_link"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
								
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_link"></th>
											<th id="xi_title" align="left">Link Name</th>
											<th id="xi_categories" align="left">Category</th>
											<th id="xi_categories" align="left">Push Status</th>
										</tr>
										<?php
											$sql = "SELECT * FROM " . $wpdb->base_prefix . "links ORDER BY `link_name` asc";
											$links = $wpdb->get_results( $sql, ARRAY_A );
											if ( is_array( $links ) ) {
												foreach ( $links as $link ) {
										?>
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $link["link_id"]; ?>" name="links[]" class = "links_check" />
											</th>
											<td align="left">
												<a href="<?php echo "link.php?action=edit&link_id=" . $link["link_id"]; ?>">
													<b>
													<?php 
														$sql = "SELECT a.link_id FROM " . $prefix . "links a WHERE link_name = \'" . $db_conn->Res( $link["link_name"] ) . "\'";
														$res = $db_conn->queryArray( $sql );
														
														if ( count( $res ) > 0 ) 
															$flagLink = 1;
														else 
															$flagLink = 0;
															
														if( empty( $link["link_name"] ) )
															echo "<no-title>";
														else { 
															if ( $flagLink == 0 )
																echo "<span style=\'color: blue\'>" . $link["link_name"];
															else 
																echo $link["link_name"];
														} 
														
													?>
													</b>
												</a>
											</td>
											<td align="left">
												<b>
												<?php $cat = get_the_terms( $link["link_id"], "link_category" );
												if ( is_array( $cat ) ) {
													foreach ( $cat as $key => $cats ) {
														echo $cats->name;
														if ( $key < ( count( $cat ) - 1 ) )
															echo ",";
													}
												}
												?>
												</b>
											</td>
											<td>
											<?php
												$sql = "SELECT a.link_id FROM " . $prefix . "links a WHERE link_name = \'" . $db_conn->Res( $link["link_name"] ) . "\'";
												$res = $db_conn-> queryArray( $sql );
												
												if ( count( $res ) > 0 ) {
													echo "Pushed";
												} else {
													echo "Unpushed";
													$un_link_cnt[$i] = 1;
												}
											?>
											</td>
										</tr>
										<?php
												}
											} else {
										?>
										<tr>
											<td colspan = "4">No Links</td>
										</tr>
										<?php
											}
										?>
									</table>
									
								</div>
								
								<?php
									}
									$db_conn = null;
								?>
							</div>
							
							
							
							<div id="media">
							
								<h3>&nbsp;&nbsp;&nbsp;Media&nbsp;&nbsp;&nbsp;
									<span><a id="xi_add_new" href="media-new.php" target="_blank" >Add New</a></span>
								</h3>
								
								<?php if ( count( $number ) > 1 ) { ?>
								Select Target Site:
								<br />
								
								<div>
								<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
									<input type="radio" name="selectMedia" id="selectLive" index="<?php echo $number[$i]; ?>" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_media"] == $liveName[$i] && $_SESSION["select_media"] != "" ) echo "checked"; if ( $_SESSION["select_media"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									<br />
								<?php } ?>
								</div>
								<br />
								
								<?php 
									}
									
									for ( $i = 0; $i < count( $number ); $i++ ) {
										
										$un_media_cnt[$i] = 0;
										
										$db_conn = $global_db_conn[$i];
										
								?>
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_media"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
								
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_attachment"></th>
											<th id="xi_category" align="left" width="20%">Thumbnail</th>
											<th id="xi_title" align="left" width="20%">Attachment Name</th>
											<th id="xi_category" align="left" width="20%">Attached To</th>
											<th id="xi_category" align="left" width="20%">Push Status</th>
											<th id="xi_category" align="left">Push Date</th>
										</tr>
										<?php
											$args = array(
												"post_type" => "attachment",
												"post_mime_type" => null,
												"orderby" => "title",
												"order" => "ASC",
												"numberposts" => -1
											);
											$myposts = get_posts( $args );
											
											foreach ( $myposts as $post ) : setup_postdata( $post ); 
											
												$post_title = $db_conn->Res( $post->post_title );
												$post_name = $db_conn->Res( $post->post_name );
												
												$sql = "SELECT a.post_modified FROM " . $prefix . "posts a WHERE post_title = \'" . $post_title . "\' AND post_name = \'" . $post_name . "\'";
												$res = $db_conn->queryArray( $sql );
												
												if ( count( $res ) > 0 ) {
												
													$livePostModified = $res[0]["post_modified"];
													if ( $livePostModified > $post->post_modified ) $color = "red"; 
													if ( $livePostModified <= $post->post_modified ) $color = "black"; 
												} else 
													$color = "blue"; 
											?>
										<tr>
											<td colspan="6" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $post->ID; ?>" name="attachments[]" class = "attach_check" />
											</th>
											<td align="left">
											<?php
												$src_32 = wp_get_attachment_image_src( $post->ID, array(16, 16), true );
												echo "<img style=\'width: 150px; height: 150px\' src=\'" . $src_32[0] . "\'  alt = \'Attachment\' />";
											?>
											</td>
											<td align="left">
												<a href="<?php echo get_edit_post_link( $post->ID); ?>">
													<b style="color: <?php echo $color; ?>">
													<?php 
														$title = the_title(); 
														$src = wp_get_attachment_image_src( $post->ID, full );
														$ext = pathinfo( $src[0], PATHINFO_EXTENSION );
														$ext = strtolower( $ext );
													?>
													</b>
												</a>&nbsp;&nbsp;&nbsp;&nbsp;
												<?php
													if ( $ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif" || $ext == "bmp" ) {
												?>
												<a href="javascript:Preview(\'<?php echo $src[0]; ?>\', \'<?php echo $src[1]; ?>\', \'<?php echo $src[2]; ?>\')" id="xi_preview_img">Preview</a>
												<?php
													}
												?>
											</td>
											<td align="left">
											<?php $id = $post->post_parent; 
												if ($id == 0)
													echo "<i style=\'color:#777\'>none</i>";
												else {
													$attach_post = get_post( $id );
													echo $attach_post->post_title;
												}
											?>
											</td>
											<td>
											<?php
												$sql = "SELECT a.guid FROM " . $prefix . "posts a WHERE post_title = \'" . $post_title . "\' AND post_name = \'" . $post_name . "\'";
												$res = $db_conn->queryArray( $sql );
												if ( count( $res ) > 0 ) {
													$guid = $res[0]["guid"];
													$ext = pathinfo( $guid, PATHINFO_EXTENSION );
													$ext = strtolower( $ext );
													
													if ( $color == "red" ) 
														echo "Destination is Newer";
														
													if ( $color == "black" ) 
														echo "Pushed";
														
													if ( $color == "blue" ) {
													
														echo "Unpushed";
														$un_media_cnt[$i] = 1;
													}
														
												}
												else {
													echo "Unpushed";
													$un_media_cnt[$i] = 1;
												}
											?>
											</td>
											<td>
											<?php
												$sql = "SELECT a.post_modified FROM " . $prefix . "posts a WHERE post_title = \'" . $db_conn->Res( $post_title ) . "\' AND post_name = \'" . $post_name . "\'";
												$res = $db_conn->queryArray( $sql );
												
												if ( count( $res ) > 0 ) {
												
													if ( ( $flagDraft != 1 ) && ( $color != "blue" ) ) 
													
														echo $res[0]["post_modified"];
												}
												
												$flagDraft = 0;
												$color = "black";
											?>
											</td>
										</tr>
										<?php
											endforeach;
										?>
									</table>
									
								</div>
								
								<?php 
									}
									$db_conn = null;
								?>
							</div>
							
							
							
							
							<div id="themes">
							
								<h3>&nbsp;&nbsp;&nbsp;Themes</h3>
								
								<?php if ( count( $number ) > 1 ) { ?>
								Select Target Site:
								<br />
								<div>
								
								<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
									<input type="radio" name="selectTheme" id="selectLive" index="<?php echo $number[$i]; ?>" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_theme"] == $liveName[$i] && $_SESSION["select_theme"] != "" ) echo "checked"; if ( $_SESSION["select_theme"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									<br />
								<?php } ?>
								</div>
								
								<br />
								
								<?php 
									}
									
									for ( $i = 0; $i < count( $number ); $i++ ) { 
									
										$un_theme_cnt[$i] = 0;
								?>
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_theme"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
								
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_themes"></th>
											<th id="xi_title" align="left">Theme Name</th>
											<th id="xi_title" align="left">Push Status</th>
											<th id="xi_title" align="left">Push Date</th>
										</tr>
										<?php
										
											$path = get_theme_root();
											$countEntry = 0;
											if ( $handle = @opendir( $path ) ) {
												while ( false !== ( $entry_old = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry_old ) ) {
														if ( ( strcmp( $entry_old, "." ) != 0 ) && ( strcmp( $entry_old, ".." ) != 0 ) ) {
															$entry[$countEntry] = $entry_old;
															$countEntry ++;
														}
													}
												}
											}
											closedir($handle);
											$countEntry = 0;
											natcasesort($entry);
										
											foreach ($entry as $elem) {
										?>
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $elem; ?>" name="themes[]" class = "themes_check" />
											</th>
											<td align="left">
												<b>
												<?php 
													$sql = "SELECT * FROM xiblox_destination_info WHERE id = " . $number[$i];
													$result = $wpdb->get_results( $sql, ARRAY_A );
													
													$destination_path = $result[0]["destination_path"];
													$destination_path .= "wp-content/themes";
													
													if ( $liveHandle = @opendir( $destination_path ) ) {
													
														while ( false !== ( $liveEntry = readdir( $liveHandle ) ) ) {
														
															if ( is_dir( $destination_path . "/" . $liveEntry ) ) {
															
																if ( ( strcmp( $destination_path, "." ) != 0 ) && ( strcmp( $destination_path, ".." ) != 0 ) ) {
																
																	if ( $elem == $liveEntry ) { 
																	
																		$liveCreateDate = date("Y-m-d H:i:s", filemtime( $destination_path . "/" . $liveEntry ));
																		$stagingCreateDate = date("Y-m-d H:i:s", filemtime( $path . "/" . $elem ));
																		
																		if ( $stagingCreateDate > $liveCreateDate ) { 
																		
																			$flagPublish = 0; 
																			$liveCreateDate = ""; 
																			$flagBlue = 1; 
																		}
																		
																		if ( $stagingCreateDate == $liveCreateDate ) { 
																		
																			$flagBlack = 1; 
																			$flagPublish = 1; 
																		}
																		
																		if ( $stagingCreateDate < $liveCreateDate ) { 
																		
																			$sql = "SELECT item_name FROM xiblox_new_item WHERE item_name = \'" . $liveEntry . "\' AND type = \'theme\'";
																			$result1 = $wpdb->get_results( $sql, ARRAY_A );
																			
																			if ( $result1[0]["item_name"] != "" ) {
																			
																				$flagBlack = 1;
																				$flagPublish = 1;
																			} else {
																				$flagPublish = 1;
																				$flagRed = 1; 
																			}
																		}
																		
																		$flagEqual = 1;
																	}
																}
															}
														}
														
														if ( $flagEqual != 1 ) {
															$flagPublish = 0; 
															$flagExist = 1; 
														}
													} 
													
													if ( $flagBlue == 1 ) {
													
														echo "<span style=\'color: blue\'>" . $elem . "</span>";
														$un_theme_cnt[$i] = 1;
													}
													
													if ( $flagEqual == 0 ) {
														echo "<span style=\'color: blue\'>" . $elem . "</span>";
														$un_theme_cnt[$i] = 1;
													}
														
													if ( $flagBlack == 1 ) 
														echo $elem;
												?>
												</b>
											</td>
											<td>
											<?php
												if ( $flagExist == 1 )
													echo "Doesn\'t Exist";
												else {
													if ( $flagPublish == 1 ) {
														if ( $flagRed == 1 ) 
															echo "Destination is Newer";
														else 
															echo "Pushed";
													}
													else 
														echo "Unpushed";
												}
												$flagExist = 0;
												$flagPublish = 0;
												$flagRed = 0;
												$flagBlue = 0;
												$flagBlack = 0;
												$flagEqual = 0;
											?>
											</td>
											<td>
											<?php
												echo $liveCreateDate;
												$liveCreateDate = "";
											?>
											</td>
											
										</tr>
										<?php
											$countEntry ++;
										}
										?>
									</table>
								</div>
								
								<?php } ?>
								
							</div>
							
							
							
							
							<div id="plugins">
							
								<h3>&nbsp;&nbsp;&nbsp;Plugins</h3>
								
								<?php if ( count( $number ) > 1 ) { ?>
								Select Target Site:
								
								<br />
								<div>
								
								<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
									<input type="radio" name="selectPlugin" id="selectLive"  index="<?php echo $number[$i]; ?>" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_plugin"] == $liveName[$i] && $_SESSION["select_plugin"] != "" ) echo "checked"; if ( $_SESSION["select_plugin"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									<br />
								<?php } ?>
								</div>
								
								<br />
								
								<?php 
									} 
									
									for ( $i = 0; $i < count( $number ); $i++ ) {
										
										$un_plugin_cnt[$i] = 0;
								?>
								
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_plugin"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
								
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_plugin"></th>
											<th id="xi_title" align="left" width="30%">Plugin Name</th>
											<th id="xi_title" align="left" width="30%">Push Status</th>
											<th id="xi_title" align="left">Push Date</th>
										</tr>
										<?php
											$path = ABSPATH . "wp-content/plugins/";
											$countEntry = 0;
											
											if ( $handle = @opendir( $path ) ) {
											
												while ( false !== ( $entry_old = readdir( $handle ) ) ) {
												
													if ( is_dir( $path . "/" . $entry_old ) ) {
													
														if ( ( strcmp( $entry_old, "." ) != 0 ) && ( strcmp( $entry_old, ".." ) != 0 ) ) {
														
															$entry_plugin[$countEntry] = $entry_old;
															$countEntry ++;
														}
													}
												}
											}
											closedir( $handle );
											$countEntry = 0;
											
											natcasesort($entry_plugin);
											
											foreach ( $entry_plugin as $elem ) {
										?>
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $elem; ?>" name="plugins[]" class = "plugin_check" />
											</th>
											<td align="left">
												<b>
													<?php 
														$sql = "SELECT * FROM xiblox_destination_info WHERE id = " . $number[$i];
														$result = $wpdb->get_results( $sql, ARRAY_A );
														
														$destination_path = $result[0]["destination_path"];
														$destination_path .= "wp-content/plugins";
														
														if ( $liveHandle = @opendir( $destination_path ) ) {
														
															while ( false !== ( $liveEntry = readdir( $liveHandle ) ) ) {
															
																if ( is_dir( $destination_path . "/" . $liveEntry ) ) {
																
																	if ( ( strcmp( $liveEntry, "." ) != 0 ) && ( strcmp( $liveEntry, ".." ) != 0 ) ) {
																	
																		if ( $elem == $liveEntry ) { 
																		
																			$liveCreateDate = date("Y-m-d H:i:s", filemtime( $destination_path . "/" . $liveEntry) ) ;
																			
																			$stagingCreateDate = date("Y-m-d H:i:s", filemtime( $path . "/" . $elem ) );
																			
																			if ( $stagingCreateDate > $liveCreateDate ) { 
																			
																				$flagPublish = 0; 
																				$liveCreateDate = ""; 
																				$flagBlue = 1; 
																				
																			}
																			
																			if ( $stagingCreateDate == $liveCreateDate ) {  
																			
																				$flagBlack = 1; 
																				$flagPublish = 1; 
																				
																			}
																			
																			if ( $stagingCreateDate < $liveCreateDate ) { 
																			
																				$sql = "SELECT item_name FROM xiblox_new_item WHERE item_name = \'" . $liveEntry . "\' AND type = \'plugin\'";
																				$result1 = $wpdb->get_results( $sql, ARRAY_A );
																				
																				if ( $result1[0]["item_name"] != "" ) {
																				
																					$flagBlack = 1;
																					$flagPublish = 1;
																					
																				} else {
																				
																					$flagPublish = 1;
																					$flagRed = 1; 
																					
																				}
																				
																			}
																			
																			$flagEqual = 1;
																			
																		}
																	}
																} 
															}
															
															if ( $flagEqual != 1 ) {
															
																$flagPublish = 0; 
																$flagBlue = 1; 
																$flagExist = 1; 
																
															}
														} else 
															echo $elem;
															
														if ( $flagBlue == 1 ) {
															echo "<span style=\'color: blue\'>" . $elem . "</span>";
															$un_plugin_cnt[$i] = 1;
														}
															
														if ( $flagRed == 1 ) 
															echo "<span style=\'color: red\'>" . $elem . "</span>";
															
														if ( $flagBlack == 1 ) 
															echo $elem;
															
													?>
												</b>
											</td>
											<td>
												<?php
													if ( $flagExist == 1 ) 
														echo "Doesn\'t Exist";
													else {
														if ( $flagPublish == 1 ) {
															if ( $flagRed == 1 ) 
																echo "Destination is newer";
															else 
																echo "Pushed";
														}
														else 
															echo "Unpushed";
													}
													$flagExist = 0;
													$flagPublish = 0;
													$flagRed = 0;
													$flagBlue = 0;
													$flagBlack = 0;
													$flagEqual = 0;
												?>
											</td>
											<td>
												<?php
													echo $liveCreateDate;
													$liveCreateDate = "";
												?>
											</td>
										</tr>
										<?php
											$countEntry ++;
										}
										?>
									</table>
									
								</div>
								
								<?php } ?>
							</div>
							
							
							
							
							
							<div id="users">
							
								<h3>&nbsp;&nbsp;&nbsp;Users&nbsp;&nbsp;&nbsp;
									<span><a id="xi_add_new" href="user-new.php" target="_blank" >Add New</a></span>
								</h3>
								
								<?php if ( count( $number ) > 1 ) { ?>
								Select Target Site:
								<br />
								
								<div>
								
								<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
									<input type="radio" name="selectUser" id="selectLive" index="<?php echo $number[$i]; ?>"  value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_user"] == $liveName[$i] && $_SESSION["select_user"] != "" ) echo "checked"; if ( $_SESSION["select_user"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									<br />
								<?php } ?>
								</div>
								
								<br />
								
								<?php 
									} 
									
									for ( $i = 0; $i < count( $number ); $i++ ) {
										
										$un_user_cnt[$i] = 0;
										
										$db_conn = $global_db_conn[$i];
										
								?>
								
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_user"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
								
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_users"></th>
											<th id="xi_title" align="left" width="20%">Username</th>
											<th id="xi_categories" align="left" width="20%">User Email</th>
											<th id="xi_categories" align="left" width="20%">Role</th>
											<th id="xi_categories" align="left" width="20%">Push Status</th>
											<th id="xi_categories" align="left" width="20%">Push Date</th>
										</tr>
										<?php
											global $post;
											$tmp_post = $post;
											
											$sql = "SELECT * FROM " . $wpdb->prefix . "users WHERE user_email != \'\'";
											$result = $wpdb->get_results( $sql, ARRAY_A );
											foreach ( $result as $post ) :  ?>
										<tr>
											<td colspan="6" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $post["ID"]; ?>" name="users[]" class = "user_check" />
											</th>
											<td align="left">
												<a href="user-edit.php?user_id=<?php echo $post["ID"]; ?>">
												<?php
													$sql = "SELECT a.user_registered FROM " . $prefix . "users a WHERE user_login = \'" . $db_conn->Res($post["user_login"]) . "\'";
													$res = $db_conn->queryArray( $sql );
													
													if ( count( $res ) > 0 ) {
													
														$registeredDate = $res[0]["user_registered"];
														
														if ( $registeredDate > $post["user_registered"] ) { 
														
															$color = "red"; 
															$flagPublish = 1; 
														}
														
														if ( $registeredDate < $post["user_registered"] ) { 
															$color = "blue"; 
															$flagPublish = 0; 
														}
														
														if ( $registeredDate == $post["user_registered"] ) { 
															$color = "black"; 
															$flagPublish = 1; 
														}
													} else { 
														$color = "blue"; 
														$flagPublish = 0; 
													}
												?>
												<b style="color: <?php echo $color; ?>"><?php echo $post["user_login"]; ?></b>
												</a>
											</td>
											<td align="left">
												<b><?php echo $post["user_email"]; ?></b>
											</td align="left">
											<td align="left">
											<?php	
												$user = new WP_User( $post["ID"] );
												if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
													foreach ( $user->roles as $role ) {
														echo $role;
													}
												}
											?>
											</td>
											<td>
											<?php
												if ( $color == "red" ) 
													echo "Destination is newer";
													
												if ( $color == "blue" ) {
													
													echo "Unpushed";
													$un_user_cnt[$i] = 1;
												}
													
												if ( $color == "black" ) 
													echo "Pushed";
											?>
											</td>
											<td>
											<?php
												if ( $flagPublish == 1 ) 
													echo $registeredDate;
												$color = "";
												$flagPublish = 0;
											?>
											</td>
										</tr>
										<?php endforeach; ?>
									</table>
								</div>
								
								<?php 
									} 
									$db_conn = null;
								?>
							</div>
							
							
							
							
							<div id="tables">
							
								<h3>&nbsp;&nbsp;&nbsp;Tables</h3>
								
								<?php if ( count( $number ) > 1 ) { ?>
								Select Target Site:
								<br />
								
								<div>
								
								<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
									<input type="radio" name="selectTable" id="selectLive" index="<?php echo $number[$i]; ?>" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_table"] == $liveName[$i] && $_SESSION["select_table"] != "" ) echo "checked"; if ( $_SESSION["select_table"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									<br />
								<?php } ?>
								</div>
								
								<br />
								
								<?php 
									}
									
									for ( $i = 0; $i < count( $number ); $i++ ) {
										
										$un_table_cnt[$i] = 0;
										
										$db_conn = $global_db_conn[$i];
								?>
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_table"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
								
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_tables"></th>
											<th id="xi_title" align="left">Table Name</th>
											<th id="xi_title" align="left">Description</th>
											<th id="xi_title" align="left">Push Status</th>
											<th id="xi_title" align="left">Push Date</th>
										</tr>
										
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<?php 
											$sql = "SHOW TABLES LIKE \'%\'";
											$results = $wpdb->get_results( $sql );
											$countEntry = 0;
											
											foreach ( $results as $index => $value ) {
											
												foreach ( $value as $tableName_old ) {
												
													$tableName_new[$countEntry] = $tableName_old;
													$countEntry ++;
												}
											}
											
											natcasesort( $tableName_new );
											
											foreach ( $tableName_new as $tableName ) {
											
												if ( strpos( $tableName, "xiblox_" ) !== false ) 
													continue;
													
												$sql = "SELECT check_status, conn_num FROM xiblox_check_publish WHERE table_name = \'" . $db_conn->Res( $tableName ) . "\'";
												$res = $wpdb->get_results( $sql, ARRAY_A );
												
												if ( $res[0]["check_status"] != 1 ) 
													continue;
													
												$a = $res[0]["conn_num"];
												$b = $number[$i];
												
												if ( strpos( "$a", "$b" ) === false ) 
													continue; 
												
												$sql = "SELECT link_table FROM xiblox_link_table WHERE table_name = \'" . $db_conn->Res( $tableName ) . "\'";
												$res = $wpdb->get_results( $sql, ARRAY_A );
												
												if ( $res[0]["link_table"] != "" ) {
													$linkedTable = explode( ",", $res[0]["link_table"] );
												}
												
												$sql = "SELECT update_time FROM information_schema.tables WHERE table_schema = \'" . $db_name . "\' AND table_name = \'" . $tableName . "\' ";
												$res = $db_conn->queryArray( $sql );
												
												if ( count( $res ) > 0 ) {
												
													$createDateLive = $res[0]["update_time"];
													
													$sql = "SELECT update_time FROM information_schema.tables WHERE table_schema=\'" . DB_NAME . "\' AND table_name=\'" . $tableName . "\' ";
													$result = $wpdb->get_results( $sql, ARRAY_A );
													
													$createDateStaging = $result[0]["update_time"];
													
													if ( $createDateLive > $createDateStaging ) {
													
														$sql = "SELECT item_name FROM xiblox_new_item WHERE item_name = \'" . $db_conn->Res( $tableName ) . "\' AND type = \'table\'";
														$res = $wpdb->get_results( $sql, ARRAY_A );
														
														if ( $res[0]["item_name"] != "" ) 
															$color = "black";
														else 
															$color = "red"; 
															
														$flagPublish = 1;
														
													}
													
													if ( $createDateLive < $createDateStaging ) { 
													
														$color = "blue"; 
														$flagPublish = 0;
														
													}
													
													if ( $createDateLive == $createDateStaging ) { 
													
														$color = "black"; 
														$flagPublish = 1; 
														
													}
													
												} else {
												
													$color = "blue"; 
													$flagExist = 1; 
												}	
											?>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $tableName; ?>" name="tables[]" class = "table_check" onclick="ReverseLink(this);"/>
											</th>
											<td align="left"><span style="color: <?php echo $color; ?>"><?php echo $tableName; ?></span></td>
											<td align="left">
											<?php
												$sql = "SELECT description FROM xiblox_description WHERE tableName = \'" . $db_conn->Res( $tableName ) . "\'";
												$result = $wpdb->get_results($sql, ARRAY_A);
												echo $result[0]["description"];
											?>
											</td>
											<td align="left">
											<?php
												if ( $flagExist == 1 ) 
													echo "Doesn\'t exist"; 
												else {
												
													if ( $color == "red" ) 
														echo "Destination is newer";
														
													if ( $color == "blue" ) {
														
														echo "Unpushed";
														$un_table_cnt[$i] = 1;
													}
													
													if ( $color == "black" ) 
														echo "Pushed";
												}
											?>
											</td>
											<td align="left">
											<?php
											
												if ( $flagPublish == 1 ) 
													echo $createDateLive;
													
												$color = "";
												$flagPublish = 0;
												$flagExist = 0;
											?>
											</td>
										</tr>
										<tr>
											<td colspan="5" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<?php
										
											if ( count( $linkedTable ) >= 1 ) {
											
												for ( $j = 0; $j < ( count( $linkedTable ) - 1 ); $j++ ) {
												
													$sql = "SELECT create_time FROM information_schema.tables WHERE table_schema=\'" . $db_name . "\' AND table_name=\'" . $linkedTable[$j] . "\' ";
													$res = $db_conn->queryArray( $sql );
													
													if ( count( $res ) > 0 ) {
													
														$createDateLive = $res[0]["create_time"];
														
														$sql = "SELECT create_time  FROM information_schema.tables  WHERE table_schema=\'" . DB_NAME . "\' AND table_name=\'" . $linkedTable[$j] . "\' ";
														$result = $wpdb->get_results( $sql, ARRAY_A );
														
														$createDateStaging = $result[0]["create_time"];
														
														if ( $createDateLive > $createDateStaging ) {
														
															$sql = "SELECT item_name FROM xiblox_new_item WHERE item_name = \'" . $db_conn->Res( $linkedTable[$j] ) . "\' AND type = \'table\'";
															$res = $wpdb->get_results( $sql, ARRAY_A );
															
															if ( $res[0]["item_name"] != "" ) 
																$color="black";
															else 
																$color = "red"; 
															$flagPublish = 1; 
														}
														
														if ( $createDateLive < $createDateStaging ) { 
															$color = "blue"; 
															$flagPublish = 0; 
														}
														
														if ( $createDateLive == $createDateStaging ) { 
															$color = "black"; 
															$flagPublish = 1; 
														}
													} else {
														$color = "blue"; 
														$flagExist = 1; 
													}
										?>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $linkedTable[$j]; ?>" name="tables[]" id="<?php echo $tableName; ?>" class ="table_check"  disabled/>
											</th>
											<td align="left"><span style="color: <?php echo $color; ?>"><?php echo $linkedTable[$j]; ?></span></td>
											<td align="left">
											<?php
												$sql = "SELECT description FROM xiblox_description WHERE tableName = \'" . $db_conn->Res( $linkedTable[$j] ) . "\'";
												$result = $wpdb->get_results( $sql, ARRAY_A );
												echo $result[0]["description"];
											?>
											</td>
											<td align="left">
											<?php
												if ( $flagExist == 1 ) 
													echo "Doesn\'t exist"; 
												else {
												
													if ( $color == "red" ) 
														echo "Destination is newer";
														
													if ( $color == "blue" ) {
													
														echo "Unpushed";
														$un_table_cnt[$i] = 1;
													}
														
													if ( $color == "black" ) 
														echo "Pushed";
												}
											?>
											</td>
											<td align="left">
											<?php
												if ( $flagPublish == 1 ) echo $createDateLive;
												$color = "";
												$flagPublish = 0;
												$flagExist = 0;
											?>
											</td>
										</tr>
										<tr>
											<td colspan="5" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<?php	} 
											}
											$linkedTable = "";
										?>
										<?php } ?>
									</table>
									
								</div>
								<?php 
									}
									$db_conn = null;
								?>
							</div>
							
							
							
							
							
							<div id="menus">
							
								<h3 style="margin-bottom: 10px;">&nbsp;&nbsp;&nbsp;Menu&nbsp;&nbsp;&nbsp;
									<span><a id="xi_add_new" href="nav-menus.php" target="_blank" >Add New</a></span>
								</h3>
								
								<?php
									$sql = "SELECT b.name, b.term_id FROM ".$prefix."term_taxonomy a LEFT JOIN " . $prefix . "terms b ON a.term_id=b.term_id WHERE a.taxonomy = \'nav_menu\'";
									$res = $wpdb->get_results( $sql, ARRAY_A );
									for ( $i = 0; $i < count( $res ); $i++ ) {
										$menu[$i] = $res[$i]["name"];
										$menuId[$i] = $res[$i]["term_id"];
									}
									$countMenu = count($res);
								?>
								<?php if ( count($number) > 1 ) { ?>
								
								Select Target Site:
								<br />
								
								<div>
								
								<?php for ( $i = 0; $i < count( $liveName ); $i++ ) { ?>
									<input type="radio" name="selectMenu" id="selectLive" index="<?php echo $number[$i]; ?>" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_menu"] == $liveName[$i] && $_SESSION["select_menu"] != "" ) echo "checked"; if ( $_SESSION["select_menu"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									<br />
								<?php } ?>
								</div>
								
								<br />
								
								<?php 
									}
									
									for ( $i = 0; $i < count( $number ); $i++ ) {
										
										$db_conn = $global_db_conn[$i];
										
								?>
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_menu"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
									
									<table style="background:#FFFFFF;width:30%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_menus"></th>
											<th id="xi_title" align="left" width="50%">Menu Name</th>
											<th id="xi_title" align="left" width="49%">Push Status</th>
										</tr>
										<tr>
											<td colspan="2" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<?php
											for ( $j = 0; $j < $countMenu; $j++ ) {
											
												$flagExistMenu[$j] = 0;
												$term_id[$j] = 0;
												
												$sql = "SELECT b.term_id FROM " . $prefix . "term_taxonomy a LEFT JOIN " . $prefix . "terms b ON a.term_id = b.term_id WHERE a.taxonomy = \'nav_menu\' AND b.name=\'" . $db_conn->Res( $menu[$j] ) . "\'";
												$res = $db_conn->queryArray( $sql );
												
												$term_id[$j] = $res[0]["term_id"];
												
												if ( $term_id[$j] != "" ) 
													$flagExistMenu[$j] = 1;
										?>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $menu[$j]; ?>" name="menu[]" class = "menu_check" onclick="ReverseLink(this);"/>
											</th>
											<td align="left"><span style="color: <?php echo $color; ?>"><?php echo $menu[$j]; ?></span></td>
											<td align="left">
											<?php
												if ( $flagExistMenu[$j] == 0 ) 
													echo "Doesn\'t exist"; 
												else 
													echo "Exists";
											?>
											</td>
										</tr>
										<?php } ?>
									</table>
									<br>
									Select Menu:
									<select id="sel_menu">
										<?php for ( $menuCount = 0; $menuCount < $countMenu; $menuCount++ ) { ?>
										<option value="<?php echo $menu[$menuCount]; ?>"><?php echo $menu[$menuCount]; ?></option>
										<?php } ?>
									</select>
									<br><br>
									<?php 
										for ( $menuCount = 0; $menuCount < $countMenu; $menuCount++ ) { 
										
											$sql = "SELECT term_id, slug FROM " . $wpdb->prefix . "terms WHERE name=\'" . $db_conn->Res( $menu[$menuCount] ) . "\'";
											$arr = $wpdb->get_results( $sql, ARRAY_A );
											
											$menu_id = $arr[0]["term_id"];
											$items = wp_get_nav_menu_items( $menu_id );
									?>
									<table class="showMenuItem" id="show_<?php echo $menu[$menuCount]; ?>" style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px; <?php if ( $menuCount != 0 ) echo "display: none"; ?>" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall_item" id="checkall_menuItems" <?php if ( $flagExistMenu[$menuCount] == 0 ) echo "disabled"; ?>></th>
											<th id="xi_title" align="left" width="25%">Menu Item</th>
											<th id="xi_title" align="left" width="25%">Item Type</th>
											<th id="xi_title" align="left" width="20%">Push Status</th>
											<th id="xi_title" align="left">Push Date</th>
										</tr>
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<?php
											foreach ( $items as $key1 => $value1 ) {
											
												$flagExist = 1;
												$pushDate = "";
												$flagCat = 0;
												$isCat = "";
												$regId = "";
												$isReged = "";
												
												foreach ( $value1 as $key2 => $value2 ) {
												
													if ( $key2 == "title" ) $title = $value2;
													if ( $key2 == "guid" ) $guid = $value2;
													if ( $key2 == "type_label" ) $type = $value2;
													if ( $key2 == "object_id" ) $object_id = $value2;
													if ( $key2 == "ID" ) $id = $value2;
													if ( $key2 == "object" ) $object = $value2;
													if ( $key2 == "menu_item_parent" ) $parentId = $value2;
												}
												
												if ( $object == "category" )  { 
												
													$flagCat = 1; 
													$cat = get_term_by( \'id\', $object_id, \'category\' ); 
												} else {
													$post = get_post( $object_id, ARRAY_A );
													$currDate = $post["post_modified"];
													$post_type = $post["post_type"];
													$post_title = $post["post_title"];
													$post_name = $post["post_name"];
													$post_parent = $post["post_parent"];
												}
												
												if ( $flagCat != 1 ) {
												
													$regPost = get_post( $id, ARRAY_A );
													
													$sql = "SELECT id FROM " . $prefix . "posts WHERE post_title = \'" . $db_conn->Res( $regPost["post_title"] ) . "\' AND post_type = \'" . $db_conn->Res( $regPost["post_type"] ) . "\' AND post_name = \'" . $db_conn->Res( $regPost["post_name"] ) . "\'";
													$res = $db_conn->queryArray( $sql );
													
													$regId = $res[0]["id"];
													
													if ( $regId != "" ) {
													
														if ( $term_id[$menuCount] != "" ) {
														
															$sql = "SELECT a.object_id FROM " . $prefix . "term_relationships a LEFT JOIN " . $prefix . "term_taxonomy b ON b.term_taxonomy_id = a.term_taxonomy_id WHERE a.object_id = $regId AND b.term_id = " . $db_conn->Res( $term_id[$menuCount] );
															$res = $db_conn->queryArray( $sql );
															
															$isReged = $res[0]["object_id"];
															
															if ( $isReged != "" ) {
															
																$sql = "SELECT post_modified FROM " . $prefix . "posts WHERE post_title = \'" . $db_conn->Res( $post_title ) . "\' AND post_type = \'" . $db_conn->Res( $post_type ) . "\' AND post_name = \'" . $db_conn->Res( $post_name ) . "\' AND post_parent = \'" . $post_parent . "\'";
																$res = $db_conn->queryArray( $sql );
																
																$pushDate = $res[0]["post_modified"];
																
																if ( $pushDate == "" ) { 
																
																	$flagExist = 0; 
																	$color = "black"; 
																	
																} else {
																
																	if ( $currDate == $pushDate ) 
																		$color = "black";
																		
																	if ( $currDate > $pushDate ) 
																		$color = "blue";
																		
																	if ( $currDate < $pushDate ) 
																		$color = "red";
																}
																
															} else { 
																$flagExist = 1; 
																$color = "blue"; 
															}
														} 
													} else 
														$flagExist = 0;
														
												} else {
												
													$sql = "SELECT term_id FROM " . $prefix . "terms WHERE name = \'" . $db_conn->Res( $cat->name ) . "\'";
													$res = $db_conn->queryArray( $sql );
													
													$isCat = $res[0]["term_id"];
													
													if ( $isCat != "" ) {
													
														$sql = "SELECT post_id FROM " . $prefix . "postmeta WHERE meta_key = \'_menu_item_object_id\' AND meta_value = $isCat";
														$res = $db_conn->queryArray( $sql );
														
														/* possible several post_id */
														$regCat = "";
														
														if ( $res != "" ) {
														
															foreach ( $res as $row ) {
															
																$regPostId = $row["post_id"];
																
																$sql1 = "SELECT a.object_id FROM " . $prefix . "term_relationships a LEFT JOIN " . $prefix . "term_taxonomy b ON a.term_taxonomy_id = b.term_taxonomy_id WHERE a.object_id = $regPostId AND b.term_id = " . $term_id[$menuCount];
																$res1 = $db_conn->queryArray( $sql1 );
																
																if ( $res1[0]["object_id"] != "" ) 
																	$regCat = "exist";
															}
														}
													}
												}
										?>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $id . ":" . $type . ":" . $object_id . ":" . $parentId . ":" . $term_id[$menuCount]; ?>" name="menuItem[]" class = "menuItem_check" onclick="ReverseLink(this);" <?php if ( $flagExistMenu[$menuCount] == 0 ) echo "disabled"; ?>/>
											</th>
											<td align="left"><?php  if ( $flagExistMenu[$menuCount] != 0 ) { if ( $flagCat != 1 ) { ?>
												<span style="color: <?php echo $color; ?>"><?php } } echo $title; ?></span>
											</td>
											<td align="left"><?php echo $type; ?></td>
											<td align="left">
											<?php  
												if ( $flagExistMenu[$menuCount] != 0 ) { 
												
													if ( $flagCat != 1 ) { 
													
														if ( $flagExist == 0 ) 
															echo "Doesn\'t Exist"; 
														else { 
														
															if ( $color == "blue" ) {
																echo "Unpushed"; 
																$unpushed_menu[$i][$un_menu_cnt[$i]] = $title;
																$un_menu_cnt[$i] ++;
															}
																
															if ( $color == "red" ) 
																echo "Destination is Newer"; 
																
															if ( $color == "black" ) 
																echo "Pushed";  
														} 
														
													} else { 
													
														if ( $regCat != "" ) 
															echo "Pushed"; 
														else 
															echo "Unpushed"; 
													} 
												}  
											?>
											</td>
											<td align="left">
											<?php 
											if ( $flagExistMenu[$menuCount] != 0 ) { 
												if ( $flagCat != 1 ) {  
													if ( $flagExist == 1 ) { 
														if ( ( $color == "black" ) || ( $color == "red" ) ) 
															echo $pushDate; 
													}
												} 
											}?></td>
										</tr>
										<?php } ?>
									</table>
									<?php } ?>
								</div>
								<?php 
									}
									$db_conn = null;
								?>
							</div>
		
		
		
		
							<div id="blox">
							
								<h3>&nbsp;&nbsp;&nbsp;Blox</h3>
								
								<?php if ( count($number) > 1 ) { ?>
								
								Select Target Site:
								<br />
								
								<div>
								
								<?php for ( $i = 0; $i < count($liveName); $i++ ) { ?>
									<input type="radio" name="selectBlox" id="selectLive" index="<?php echo $number[$i]; ?>" value="<?php echo str_replace( " ", "_", $liveName[$i] ); ?>" <?php if ( $_SESSION["select_blox"] == $liveName[$i] && $_SESSION["select_blox"] != "" ) echo "checked"; if ( $_SESSION["select_blox"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									<br />
								<?php } ?>
								</div>
								<br />
								
								<?php 
									} 
									
									for ( $i = 0; $i < count( $number ); $i++ ) {
										
										$un_blox_cnt[$i] = 0;
										
										$db_conn = $global_db_conn[$i];
										
								?>
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_blox"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
								
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
									
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_blox"></th>
											<th id="xi_title" align="left" width="25%">Blox Name</th>
											<th id="xi_title" align="left" width="25%">Description</th>
											<th id="xi_title" align="left" width="25%">Push Status</th>
											<th id="xi_title" align="left">Push Date</th>
										</tr>
										
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										
										<?php
										
											$sql = "SELECT blox_name FROM xiblox_tabs where blox_name != \'SitePush\' order by blox_name";
											$res = $wpdb->get_results( $sql, ARRAY_A );
											
											foreach ( $res as $index => $value ) {
											
												foreach ( $value as $bloxName ) {
												
													$sql = "SELECT create_time  FROM information_schema.tables  WHERE table_schema=\'" . $db_name . "\' AND table_name=\'xiblox_tabs\' ";
													$res = $db_conn->queryArray( $sql );
													
													if ( count( $res ) > 0 ) {
													
														$flagExist = 1;
														
														$sql = "SELECT modified_date FROM xiblox_tabs WHERE blox_name = \'" . $db_conn->Res( $bloxName ) . "\'";
														$res = $db_conn->queryArray( $sql );
														
														$liveDate = $res[0]["modified_date"];
														
														$sql = "SELECT modified_date FROM xiblox_tabs WHERE blox_name = \'" . $db_conn->Res( $bloxName ) . "\'";
														$res = $wpdb->get_results( $sql, ARRAY_A );
														
														$stagingDate = $res[0]["modified_date"];
														
														if ( $stagingDate > $liveDate ) 
															$color = "blue";
															
														if ( $stagingDate == $liveDate ) 
															$color = "black";
															
														if ( $stagingDate < $liveDate ) 
															$color = "red";
															
													} else {
														$flagExist = 0; 
														$color = "blue";
													}
										?>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $bloxName; ?>" name="blox[]" class = "blox_check" onclick="ReverseLink(this);"/>
											</th>
											<td align="left"><span style="color: <?php echo $color; ?>"><?php echo $bloxName; ?></span></td>
											<td align="left">
											<?php
												$sql = "SELECT blox_content FROM xiblox_tabs WHERE blox_name = \'" . $db_conn->Res( $bloxName ) . "\'";
												$result = $wpdb->get_results( $sql, ARRAY_A );
												echo $result[0]["blox_content"];
											?>
											</td>
											<td align="left">
											<?php
												if ( $flagExist == 0 ) 
													echo "Doesn\'t exist"; 
												else {
													if ( $color == "red" ) 
														echo "Destination is newer";
														
													if ( $color == "blue" ) {
														
														echo "Unpushed";
														$un_blox_cnt[$i] = 1;
													}
													
													if ( $color == "black" ) echo "Pushed";
												}
											?>
											</td>
											<td align="left">
											<?php
												if ( $color != "blue" ) 
													echo $liveDate;
												$color = "";
												$flagPublish = 0;
												$flagExist = 0;
											?>
											</td>
										</tr>
									<?php }
									} ?>
									</table>
									
								</div>
								
								<?php 
									}
									$db_conn = null;
								?>		
							</div>


							
							
							<div id="unpushed">
								
								<?php if ( count($number) > 1 ) { ?>
								
								Select Target Site:
								<br />
								
								<div>
								
								<?php for ( $i = 0; $i < count($liveName); $i++ ) { ?>
									<input type="radio" name="selectUnpushed" id="selectLive" index="<?php echo $number[$i]; ?>" value="<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_unpushed"] == $liveName[$i] && $_SESSION["select_unpushed"] != "" ) echo "checked"; if ( $_SESSION["select_unpushed"] == "" && $i == 0 ) echo "checked"; ?>> <?php echo $liveName[$i]; ?>
									<br />
								<?php } ?>
								</div>
								
								<br />
								
								<?php 
									} 
									
									for ( $i = 0; $i < count( $number ); $i++ ) {
										
										$db_conn = $global_db_conn[$i];
										
								?>
								
								<div id="pub_<?php echo $liveName[$i]; ?>" <?php if ( $_SESSION["select_unpushed"] != $liveName[$i] ) { echo "style=\'display: none;\'"; } ?>>
									
									<?php 
										if ( $un_post_cnt[$i] == 1 ) {
									?>
									<h3>Unpushed Posts</h3>
									
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
									
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall"></th>
											<th id="xi_title" align="left" width="25%">Post Name</th>
											<th id="xi_categories" align="left" width="25%">Category</th>
											<th id="xi_categories" align="left" width="25%">Attachment</th>
											<th id="xi_categories" align="left" width="25%">Push Status</th>
										</tr>
										
										<?php
											global $post;
											$tmp_post = $post;
											
											if ( isset( $_POST["category"] ) )
												$category = $_POST["category"];
											else
												$category = "";
											
											// custom post types
											$args = array( "public" => true, "_builtin" => false );
											$output = "names"; // names or objects, note names is the default
											$operator = "and"; // "and" or "or"
											$post_types = get_post_types( $args, $output, $operator ); 
											
											$cptCount = 1;
											$cpt[0] = "post";
											
											foreach ( $post_types  as $post_type ) {
											
												$cpt[$cptCount] = $post_type;
												$cptCount ++;
											}
											
											// get destination information

											$db_conn = $global_db_conn[$i];
											
											for ( $postCount = 0; $postCount < $cptCount; $postCount++ ) {
											
												if ( $cpt[$postCount] == "options" ) 
													continue;
													
												$myposts = get_posts(array(
													"category" => $category,
													"post_status" => "publish",
													"post_type" => $cpt[$postCount],
													"posts_per_page" => -1,
													"orderby" => "title", 
													"order" => "ASC")
												);
												
												foreach ( $myposts as $post ) : setup_postdata( $post ); 
												
													global $wpdb;
													$baseurl = get_site_url();
													$tmp1url = str_replace( "www.", "", $baseurl );
													
													$sql = "SELECT a.post_modified FROM " . $prefix . "posts a WHERE post_title = \'" . $db_conn->Res( $post->post_title) . "\' AND post_name = \'" . $db_conn->Res( $post->post_name) . "\' AND post_status = \'" . $post->post_status . "\' AND post_type = \'" . $post->post_type . "\'";
													$res = $db_conn->queryArray( $sql );
													
													if ( count( $res ) > 0 ) {
													
														$livePostModified = $res[0]["post_modified"];
														
														if ( $livePostModified > $post->post_modified ) { 
															$flagLive = 1; 
															$color = "red"; 
														}
														
														if ( $livePostModified == $post->post_modified ) { 
															$flagEqual = 1; 
															$color = "black"; 
														}
														
														if ( $livePostModified < $post->post_modified ) { 
															$flagStaging = 1; 
															$color = "blue"; 
														}
														
													} else { 
													
														$color = "blue"; 
														$flagStaging = 1; 
													}
													
												if ( $color == "blue" ) { ?>
										<tr>
											<td colspan="6" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $post->ID; ?>" name="posts[]" class = "post_check" <?php
														if ( strcmp( $post->post_status , "pending" ) == 0 || strcmp( $post->post_status ,"draft" ) == 0 )
															echo "style=\'display:none;\'";
													?>/>
											</th>
											<td align="left">
												<a href="<?php echo get_edit_post_link( $post->ID ); ?> ">
													<b style="color: <?php echo $color; ?>">
													
													<?php 
														the_title(); 
														
														if ( $cpt[$postCount] != "post" ) 
															echo "(" . $cpt[$postCount] . ")"; 
															
														if ( strcmp( $post->post_status ,"pending" ) == 0 || strcmp( $post->post_status ,"draft" ) == 0 ) {
															$flagDraft = 1;
															echo " <span style=\'color:#000\'>" . $post->post_status . "</span>";
														}
														
													?>
													</b>
												</a>
												<?php
													$link = get_page_link( $post->ID );
													echo "<a target = \'_blank\' href=\'" . $link . "\' id=\'xi_preview_page\'>Preview&nbsp;&nbsp;&nbsp;</a>";
												?>
											</td>
											<td align="left">
												<a>
													<b>
													<?php 
														$cat = get_the_category( $post->ID );
														
														foreach ( $cat as $key => $cats ) {
															echo $cats->name;
															if ( $key < ( count( $cat ) - 1 ) )
																echo ",";
														}
													 ?>
													</b>
												</a>
											</td>
											<td align="left">
											<?php
												$args = array(
													"post_type" => "attachment",
													"numberposts" => null,
													"post_status" => null,
													"post_parent" => $post->ID
												);
												
												$attachments = get_posts($args);
												if ( $attachments ) 
													echo count( $attachments ) . " Attachments";
												else
													echo "No attachments";
											?>
											</td>
											<td align="left">
											<?php
												echo "Unpushed";
											?>
											</td>
										</tr>
										
										<?php 
											}
											endforeach; 
										}
										?>
									</table>
									<?php } ?>
									
									
									<?php
										if ( $un_page_cnt[$i] == 1 ) {
									?>
									<br>
									<h3>Unpushed Pages</h3>
									
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
									
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_page"></th>
											<th id="xi_title" align="left" width="25%">Page Name</th>
											<th id="xi_categories" align="left" width="25%">Category</th>
											<th id="xi_categories" align="left" width="25%">Attachment</th>
											<th id="xi_categories" align="left" width="25%">Push Status</th>
										</tr>
										
										<?php
											global $post;
											$tmp_post = $post;
											
											if ( isset( $_POST["category"] ) )
												$category = $_POST["category"];
											else
												$category = "";
											
											$myposts = get_pages();
											
											foreach ( $myposts as $post ) : setup_postdata( $post ); 
											
												global $wpdb;
												$baseurl = get_site_url();
												$tmp1url = str_replace( "www.", "", $baseurl );
												
												$sql = "SELECT a.post_modified FROM " . $prefix . "posts a WHERE post_title = \'" . $db_conn->Res( $post->post_title) . "\' AND post_name = \'" . $db_conn->Res( $post->post_name) . "\' AND post_status = \'" . $post->post_status . "\' AND post_type = \'" . $post->post_type . "\'";
												$res = $db_conn->queryArray( $sql );
												
												if ( count( $res ) > 0 ) {
												
													$livePostModified = $res[0]["post_modified"];
													
													if ( $livePostModified > $post->post_modified ) { 
														$flagLive = 1; 
														$color = "red"; 
													}
													
													if ( $livePostModified == $post->post_modified ) { 
														$flagEqual = 1; 
														$color = "black"; 
													}
													
													if ( $livePostModified < $post->post_modified ) { 
														$flagStaging = 1; 
														$color = "blue"; 
													}
													
												} else { 
												
													$color = "blue"; 
													$flagStaging = 1; 
												}
												
											if ( $color == "blue" ) { ?>
										<tr>
											<td colspan="6" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $post->ID; ?>" name="posts[]" class="page_check" <?php
														if ( strcmp( $post->post_status , "pending" ) == 0 || strcmp( $post->post_status ,"draft" ) == 0 )
															echo "style=\'display:none;\'";
													?>/>
											</th>
											<td align="left">
												<a href="<?php echo get_edit_post_link( $post->ID ); ?> ">
													<b style="color: <?php echo $color; ?>">
													
													<?php 
														the_title(); 
															
														if ( strcmp( $post->post_status ,"pending" ) == 0 || strcmp( $post->post_status ,"draft" ) == 0 ) {
															$flagDraft = 1;
															echo " <span style=\'color:#000\'>" . $post->post_status . "</span>";
														}
														
													?>
													</b>
												</a>
												<?php
													$link = get_page_link( $post->ID );
													echo "<a target = \'_blank\' href=\'" . $link . "\' id=\'xi_preview_page\'>Preview&nbsp;&nbsp;&nbsp;</a>";
												?>
											</td>
											<td align="left">
												<a>
													<b>
													<?php 
														$cat = get_the_category( $post->ID );
														
														foreach ( $cat as $key => $cats ) {
															echo $cats->name;
															if ( $key < ( count( $cat ) - 1 ) )
																echo ",";
														}
													 ?>
													</b>
												</a>
											</td>
											<td align="left">
											<?php
												$args = array(
													"post_type" => "attachment",
													"numberposts" => null,
													"post_status" => null,
													"post_parent" => $post->ID
												);
												
												$attachments = get_posts($args);
												if ( $attachments ) 
													echo count( $attachments ) . " Attachments";
												else
													echo "No attachments";
											?>
											</td>
											<td align="left">
											<?php
												echo "Unpushed";
											?>
											</td>
										</tr>
										
										<?php 
											}
											
											endforeach; 
										?>
									</table>
									<?php } ?>
									
									
									<?php 
										if ( $un_link_cnt[$i] == 1 ) { 
									?>
									<br>
									<h3>Unpushed Links</h3>
									
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_link"></th>
											<th id="xi_title" width="25%" align="left">Link Name</th>
											<th id="xi_categories" width="25%" align="left">Category</th>
											<th id="xi_categories" width="25%" align="left">Push Status</th>
											<th id="xi_categories" width="25%" align="left"></th>
										</tr>
										<?php
											$sql = "SELECT * FROM " . $wpdb->base_prefix . "links ORDER BY `link_name` asc";
											$links = $wpdb->get_results( $sql, ARRAY_A );
											
											if ( is_array( $links ) ) {
											
												foreach ( $links as $link ) {
												
													$sql = "select a.link_id from " . $prefix . "links a where link_name = \'" . $db_conn->Res( $link["link_name"] ) . "\'";
													$res = $db_conn->queryArray( $sql );
													
													if ( count( $res ) == 0 ) {
										?>
										<tr>
											<td colspan="5" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $link["link_id"]; ?>" name="links[]" class = "links_check" />
											</th>
											
											<td align="left">
												<a href="<?php echo "link.php?action=edit&link_id=" . $link["link_id"]; ?>">
													<b>
													<?php 
													
														if( empty( $link["link_name"] ) )
															echo "<no-title>";
														else 
															echo "<span style=\'color: blue\'>" . $link["link_name"];
														
													?>
													</b>
												</a>
											</td>
											
											<td align="left">
												<b>
													<?php 
														$cat = get_the_terms( $link["link_id"], "link_category" );
														
														if ( is_array( $cat ) ) {
														
															foreach ( $cat as $key => $cats ) {
															
																echo $cats->name;
																if ( $key < ( count( $cat ) - 1 ) )
																	echo ",";
															}
														}
													?>
												</b>
											</td>
											
											<td>
											<?php
												echo "Unpushed";
											?>
											</td>
											<td></td>
											
										</tr>
										
										<?php
													}
												}
											} else {
										?>
										
										<tr>
											<td colspan="5">No Links</td>
										</tr>
										
										<?php
											}
										?>
									</table>
									<?php } ?>
									
									
									<?php 
										if ( $un_media_cnt[$i] == 1 ) {
									?>
									<br>
									<h3>Unpushed Media</h3>
									
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_attachment"></th>
											<th id="xi_category" align="left" width="20%">Thumbnail</th>
											<th id="xi_title" align="left" width="20%">Attachment Name</th>
											<th id="xi_category" align="left" width="20%">Attached To</th>
											<th id="xi_category" align="left" width="20%">Push Status</th>
											<th id="xi_category" align="left">Push Date</th>
										</tr>
										<?php
											$args = array(
												"post_type" => "attachment",
												"post_mime_type" => null,
												"orderby" => "title",
												"order" => "ASC",
												"numberposts" => -1
											);
											$myposts = get_posts( $args );
											
											foreach ( $myposts as $post ) : setup_postdata( $post ); 
											
												$post_title = $db_conn->Res( $post->post_title );
												$post_name = $db_conn->Res( $post->post_name );
												
												$sql = "SELECT a.post_modified FROM " . $prefix . "posts a WHERE post_title = \'" . $post_title . "\' AND post_name = \'" . $post_name . "\'";
												$res = $db_conn->queryArray( $sql );
												
												if ( count( $res ) == 0 ) {
											?>
										<tr>
											<td colspan="6" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $post->ID; ?>" name="attachments[]" class = "attach_check" />
											</th>
											<td align="left">
											<?php
												$src_32 = wp_get_attachment_image_src( $post->ID, array(16, 16), true );
												echo "<img style=\'width: 150px; height: 150px\' src=\'" . $src_32[0] . "\'  alt = \'Attachment\' />";
											?>
											</td>
											<td align="left">
												<a href="<?php echo get_edit_post_link( $post->ID); ?>">
													<b style="color: blue">
													<?php 
														$title = the_title(); 
														$src = wp_get_attachment_image_src( $post->ID, full );
														$ext = pathinfo( $src[0], PATHINFO_EXTENSION );
														$ext = strtolower( $ext );
													?>
													</b>
												</a>&nbsp;&nbsp;&nbsp;&nbsp;
												<?php
													if ( $ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif" || $ext == "bmp" ) {
												?>
												<a href="javascript:Preview(\'<?php echo $src[0]; ?>\', \'<?php echo $src[1]; ?>\', \'<?php echo $src[2]; ?>\')" id="xi_preview_img">Preview</a>
												<?php
													}
												?>
											</td>
											<td align="left">
											<?php 
												$id = $post->post_parent; 
												
												if ($id == 0)
													echo "<i style=\'color:#777\'>none</i>";
												else {
													$attach_post = get_post( $id );
													echo $attach_post->post_title;
												}
											?>
											</td>
											
											<td>
											<?php
												echo "Unpushed";
											?>
											</td>
											
											<td>
											<?php
												$sql = "SELECT a.post_modified FROM " . $prefix . "posts a WHERE post_title = \'" . $db_conn->Res( $post_title ) . "\' AND post_name = \'" . $post_name . "\'";
												$res = $db_conn->queryArray( $sql );
												
												if ( count( $res ) > 0 ) {
												
													if ( ( $flagDraft != 1 ) && ( $color != "blue" ) ) 
													
														echo $res[0]["post_modified"];
												}
												
												$flagDraft = 0;
											?>
											</td>
										</tr>
										
										<?php
												}
												
											endforeach;	
										?>
									</table>
									<?php
										}
									?>
									
									
									
									<?php 
										if ( $un_theme_cnt[$i] == 1 ) {
											$flagEqual = 0;
											$flagBlue = 0;
									?>
									<br>
									<h3>Unpushed Theme</h3>
									
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_themes"></th>
											<th id="xi_title" align="left" width="33%">Theme Name</th>
											<th id="xi_title" align="left" width="33%">Push Status</th>
											<th id="xi_title" align="left" width="33%">Push Date</th>
										</tr>
										<?php
										
											$path = get_theme_root();
											$countEntry = 0;
											if ( $handle = @opendir( $path ) ) {
												while ( false !== ( $entry_old = readdir( $handle ) ) ) {
													if ( is_dir( $path . "/" . $entry_old ) ) {
														if ( ( strcmp( $entry_old, "." ) != 0 ) && ( strcmp( $entry_old, ".." ) != 0 ) ) {
															$entry[$countEntry] = $entry_old;
															$countEntry ++;
														}
													}
												}
											}
											closedir($handle);
											$countEntry = 0;
											natcasesort($entry);
										
											foreach ($entry as $elem) {
											
												$sql = "SELECT * FROM xiblox_destination_info WHERE id = " . $number[$i];
												$result = $wpdb->get_results( $sql, ARRAY_A );
												
												$destination_path = $result[0]["destination_path"];
												$destination_path .= "wp-content/themes";
												
												if ( $liveHandle = @opendir( $destination_path ) ) {
												
													while ( false !== ( $liveEntry = readdir( $liveHandle ) ) ) {
													
														if ( is_dir( $destination_path . "/" . $liveEntry ) ) {
														
															if ( ( strcmp( $destination_path, "." ) != 0 ) && ( strcmp( $destination_path, ".." ) != 0 ) ) {
																
																if ( $elem == $liveEntry ) { 
																
																	$liveCreateDate = date("Y-m-d H:i:s", filemtime( $destination_path . "/" . $liveEntry ));
																	$stagingCreateDate = date("Y-m-d H:i:s", filemtime( $path . "/" . $elem ));
																	
																	if ( $stagingCreateDate > $liveCreateDate ) { 
																	
																		$flagPublish = 0; 
																		$liveCreateDate = ""; 
																		$flagBlue = 1; 
																	}
																	
																	if ( $stagingCreateDate == $liveCreateDate ) { 
																	
																		$flagBlack = 1; 
																		$flagPublish = 1; 
																	}
																	
																	if ( $stagingCreateDate < $liveCreateDate ) { 
																	
																		$sql = "SELECT item_name FROM xiblox_new_item WHERE item_name = \'" . $liveEntry . "\' AND type = \'theme\'";
																		$result1 = $wpdb->get_results( $sql, ARRAY_A );
																		
																		if ( $result1[0]["item_name"] != "" ) {
																		
																			$flagBlack = 1;
																			$flagPublish = 1;
																		} else {
																			$flagPublish = 1;
																			$flagRed = 1; 
																		}
																	}
																	
																	$flagEqual = 1;
																}
															}
														}
													}
													
													if ( $flagEqual != 1 ) {
														$flagPublish = 0; 
														$flagBlue = 1; 
														$flagExist = 1; 
													} else 
														$flagEqual = 0;
												}
												
												if ( $flagBlue == 1 ) { 
										?>
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $elem; ?>" name="themes[]" class = "themes_check" />
											</th>
											
											<td align="left">
												<b>
												<?php 
													echo "<span style=\'color: blue\'>" . $elem . "</span>";
												?>
												</b>
											</td>
											
											<td>
												<?php
												
													if ( $flagBlue == 1 ) 
														echo "Unpushed";
													
													if ( $flagEqual == 0 ) 
														echo "Doesn\'t Exist";
													
													$flagPublish = 0;
													$flagRed = 0;
													$flagBlue = 0;
													$flagBlack = 0;
													$flagEqual = 0;
												?>
											</td>
											<td>
												<?php
													echo $liveCreateDate;
													$liveCreateDate = "";
												?>
											</td>
											
										</tr>
											<?php
													$countEntry ++;
												}
											}
										?>
									</table>
									<?php 
										}
									?>
									
									
									
									<?php
										if ( $un_plugin_cnt[$i] == 1 ) {
											$flagExist = 0;
									?>
									<br>
									<h3>Unpushed Plugin</h3>
									
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_plugin"></th>
											<th id="xi_title" align="left" width="33%">Plugin Name</th>
											<th id="xi_title" align="left" width="33%">Push Status</th>
											<th id="xi_title" align="left" width="33%">Push Date</th>
										</tr>
										<?php
											$path = ABSPATH . "wp-content/plugins/";
											$countEntry = 0;
											
											if ( $handle = @opendir( $path ) ) {
											
												while ( false !== ( $entry_old = readdir( $handle ) ) ) {
												
													if ( is_dir( $path . "/" . $entry_old ) ) {
													
														if ( ( strcmp( $entry_old, "." ) != 0 ) && ( strcmp( $entry_old, ".." ) != 0 ) ) {
														
															$entry_plugin[$countEntry] = $entry_old;
															$countEntry ++;
														}
													}
												}
											}
											closedir( $handle );
											$countEntry = 0;
											
											natcasesort($entry_plugin);
											
											foreach ( $entry_plugin as $elem ) {
											
												$sql = "SELECT * FROM xiblox_destination_info WHERE id = " . $number[$i];
												$result = $wpdb->get_results( $sql, ARRAY_A );
												
												$destination_path = $result[0]["destination_path"];
												$destination_path .= "wp-content/plugins";
												
												if ( $liveHandle = @opendir( $destination_path ) ) {
												
													while ( false !== ( $liveEntry = readdir( $liveHandle ) ) ) {
													
														if ( is_dir( $destination_path . "/" . $liveEntry ) ) {
														
															if ( ( strcmp( $liveEntry, "." ) != 0 ) && ( strcmp( $liveEntry, ".." ) != 0 ) ) {
															
																if ( $elem == $liveEntry ) { 
																
																	$liveCreateDate = date("Y-m-d H:i:s", filemtime( $destination_path . "/" . $liveEntry) ) ;
																	
																	$stagingCreateDate = date("Y-m-d H:i:s", filemtime( $path . "/" . $elem ) );
																	
																	if ( $stagingCreateDate > $liveCreateDate ) { 
																	
																		$flagPublish = 0; 
																		$liveCreateDate = ""; 
																		$flagBlue = 1; 
																		
																	}
																	
																	if ( $stagingCreateDate == $liveCreateDate ) {  
																	
																		$flagBlack = 1; 
																		$flagPublish = 1; 
																		
																	}
																	
																	if ( $stagingCreateDate < $liveCreateDate ) { 
																	
																		$sql = "SELECT item_name FROM xiblox_new_item WHERE item_name = \'" . $liveEntry . "\' AND type = \'plugin\'";
																		$result1 = $wpdb->get_results( $sql, ARRAY_A );
																		
																		if ( $result1[0]["item_name"] != "" ) {
																		
																			$flagBlack = 1;
																			$flagPublish = 1;
																			
																		} else {
																		
																			$flagPublish = 1;
																			$flagRed = 1; 
																			
																		}
																		
																	}
																	
																	$flagEqual = 1;
																	
																}
															}
														} 
													}
													
													if ( $flagEqual != 1 ) {
													
														$flagPublish = 0; 
														$flagBlue = 1; 
														$flagExist = 1; 
														
													} else 
														$flagEqual = 0;
												}
												
												if ( $flagBlue == 1 ) { 
										?>
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $elem; ?>" name="plugins[]" class = "plugin_check" />
											</th>
											<td align="left">
												<b>
													<?php 
														echo "<span style=\'color: blue\'>" . $elem . "</span>";
													?>
												</b>
											</td>
											<td>
												<?php
													if ( $flagExist == 1 ) 
														echo "Doesn\'t Exist";
													else {
														if ( $flagPublish == 1 ) {
															if ( $flagRed == 1 ) 
																echo "Destination is newer";
															else 
																echo "Pushed";
														}
														else 
															echo "Unpushed";
													}
													$flagExist = 0;
													$flagPublish = 0;
													$flagRed = 0;
													$flagBlue = 0;
													$flagBlack = 0;
													$flagEqual = 0;
												?>
											</td>
											<td>
												<?php
													echo $liveCreateDate;
													$liveCreateDate = "";
												?>
											</td>
										</tr>
										<?php
												$countEntry ++;
											}
										}
										?>
									</table>
									
									<?php } ?>
									
									
									
									<?php 
										if ( $un_user_cnt[$i] == 1 ) {
									?>
									<br>
									<h3>Unpushed User</h3>
									
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_users"></th>
											<th id="xi_title" align="left" width="20%">Username</th>
											<th id="xi_categories" align="left" width="20%">User Email</th>
											<th id="xi_categories" align="left" width="20%">Role</th>
											<th id="xi_categories" align="left" width="20%">Push Status</th>
											<th id="xi_categories" align="left" width="20%">Push Date</th>
										</tr>
										<?php
											global $post;
											$tmp_post = $post;
											
											$sql = "SELECT * FROM " . $wpdb->prefix . "users WHERE user_email != \'\'";
											$result = $wpdb->get_results( $sql, ARRAY_A );
											
											foreach ( $result as $post ) :  
													
												$sql = "SELECT a.user_registered FROM " . $prefix . "users a WHERE user_login = \'" . $db_conn->Res($post["user_login"]) . "\'";
												$res = $db_conn->queryArray( $sql );
												
												if ( count( $res ) > 0 ) {
												
													$registeredDate = $res[0]["user_registered"];
													
													if ( $registeredDate > $post["user_registered"] ) { 
													
														$color = "red"; 
														$flagPublish = 1; 
													}
													
													if ( $registeredDate < $post["user_registered"] ) { 
														$color = "blue"; 
														$flagPublish = 0; 
													}
													
													if ( $registeredDate == $post["user_registered"] ) { 
														$color = "black"; 
														$flagPublish = 1; 
													}
												} else { 
													$color = "blue"; 
													$flagPublish = 0; 
												}
												
												if ( $color == "blue" ) {
										?>
										<tr>
											<td colspan="6" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $post["ID"]; ?>" name="users[]" class = "user_check" />
											</th>
											<td align="left">
												<a href="user-edit.php?user_id=<?php echo $post["ID"]; ?>">
												<b style="color: <?php echo $color; ?>"><?php echo $post["user_login"]; ?></b>
												</a>
											</td>
											<td align="left">
												<b><?php echo $post["user_email"]; ?></b>
											</td align="left">
											<td align="left">
											<?php	
												$user = new WP_User( $post["ID"] );
												if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
													foreach ( $user->roles as $role ) {
														echo $role;
													}
												}
											?>
											</td>
											<td>
											<?php
												if ( $color == "red" ) 
													echo "Destination is newer";
													
												if ( $color == "blue" ) {
													
													echo "Unpushed";
													$un_user_cnt[$i] = 1;
												}
													
												if ( $color == "black" ) 
													echo "Pushed";
											?>
											</td>
											<td>
											<?php
												if ( $flagPublish == 1 ) 
													echo $registeredDate;
												$color = "";
												$flagPublish = 0;
											?>
											</td>
										</tr>
										
										<?php }
											endforeach; 
										?>
									</table>
									
									<?php 
										}
									?>
									
									
									<?php
										if ( $un_table_cnt[$i] == 1 ) {
									?>
									<br>
									<h3>Unpushed Table</h3>
									
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_tables"></th>
											<th id="xi_title" align="left">Table Name</th>
											<th id="xi_title" align="left">Description</th>
											<th id="xi_title" align="left">Push Status</th>
											<th id="xi_title" align="left">Push Date</th>
										</tr>
										
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<?php 
											$sql = "SHOW TABLES LIKE \'%\'";
											$results = $wpdb->get_results( $sql );
											$countEntry = 0;
											
											foreach ( $results as $index => $value ) {
											
												foreach ( $value as $tableName_old ) {
												
													$tableName_new[$countEntry] = $tableName_old;
													$countEntry ++;
												}
											}
											
											natcasesort( $tableName_new );
											
											foreach ( $tableName_new as $tableName ) {
											
												if ( strpos( $tableName, "xiblox_" ) !== false ) 
													continue;
													
												$sql = "SELECT check_status, conn_num FROM xiblox_check_publish WHERE table_name = \'" . $db_conn->Res( $tableName ) . "\'";
												$res = $wpdb->get_results( $sql, ARRAY_A );
												
												if ( $res[0]["check_status"] != 1 ) 
													continue;
													
												$a = $res[0]["conn_num"];
												$b = $number[$i];
												
												if ( strpos( "$a", "$b" ) === false ) 
													continue; 
												
												$sql = "SELECT link_table FROM xiblox_link_table WHERE table_name = \'" . $db_conn->Res( $tableName ) . "\'";
												$res = $wpdb->get_results( $sql, ARRAY_A );
												
												if ( $res[0]["link_table"] != "" ) {
													$linkedTable = explode( ",", $res[0]["link_table"] );
												}
												
												$sql = "SELECT update_time FROM information_schema.tables WHERE table_schema = \'" . $db_name . "\' AND table_name = \'" . $tableName . "\' ";
												$res = $db_conn->queryArray( $sql );
												
												if ( count( $res ) > 0 ) {
												
													$createDateLive = $res[0]["update_time"];
													
													$sql = "SELECT update_time FROM information_schema.tables WHERE table_schema=\'" . DB_NAME . "\' AND table_name=\'" . $tableName . "\' ";
													$result = $wpdb->get_results( $sql, ARRAY_A );
													
													$createDateStaging = $result[0]["update_time"];
													
													if ( $createDateLive > $createDateStaging ) {
													
														$sql = "SELECT item_name FROM xiblox_new_item WHERE item_name = \'" . $db_conn->Res( $tableName ) . "\' AND type = \'table\'";
														$res = $wpdb->get_results( $sql, ARRAY_A );
														
														if ( $res[0]["item_name"] != "" ) 
															$color = "black";
														else 
															$color = "red"; 
															
														$flagPublish = 1;
														
													}
													
													if ( $createDateLive < $createDateStaging ) { 
													
														$color = "blue"; 
														$flagPublish = 0;
														
													}
													
													if ( $createDateLive == $createDateStaging ) { 
													
														$color = "black"; 
														$flagPublish = 1; 
														
													}
													
												} else {
												
													$color = "blue"; 
													$flagExist = 1; 
												}

												if ( $color == "blue" ) {
											?>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $tableName; ?>" name="tables[]" class = "table_check" onclick="ReverseLink(this);"/>
											</th>
											<td align="left"><span style="color: <?php echo $color; ?>"><?php echo $tableName; ?></span></td>
											<td align="left">
											<?php
												$sql = "SELECT description FROM xiblox_description WHERE tableName = \'" . $db_conn->Res( $tableName ) . "\'";
												$result = $wpdb->get_results($sql, ARRAY_A);
												echo $result[0]["description"];
											?>
											</td>
											<td align="left">
											<?php
												if ( $flagExist == 1 ) 
													echo "Doesn\'t exist"; 
												else {
												
													if ( $color == "red" ) 
														echo "Destination is newer";
														
													if ( $color == "blue" ) {
														
														echo "Unpushed";
														$un_table_cnt[$i] = 1;
													}
													
													if ( $color == "black" ) 
														echo "Pushed";
												}
											?>
											</td>
											<td align="left">
											<?php
											
												if ( $flagPublish == 1 ) 
													echo $createDateLive;
													
												$color = "";
												$flagPublish = 0;
												$flagExist = 0;
											?>
											</td>
										</tr>
										<tr>
											<td colspan="5" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										<?php
										
											if ( count( $linkedTable ) >= 1 ) {
											
												for ( $j = 0; $j < ( count( $linkedTable ) - 1 ); $j++ ) {
												
													$sql = "SELECT create_time FROM information_schema.tables WHERE table_schema=\'" . $db_name . "\' AND table_name=\'" . $linkedTable[$j] . "\' ";
													$res = $db_conn->queryArray( $sql );
													
													if ( count( $res ) > 0 ) {
													
														$createDateLive = $res[0]["create_time"];
														
														$sql = "SELECT create_time  FROM information_schema.tables  WHERE table_schema=\'" . DB_NAME . "\' AND table_name=\'" . $linkedTable[$j] . "\' ";
														$result = $wpdb->get_results( $sql, ARRAY_A );
														
														$createDateStaging = $result[0]["create_time"];
														
														if ( $createDateLive > $createDateStaging ) {
														
															$sql = "SELECT item_name FROM xiblox_new_item WHERE item_name = \'" . $db_conn->Res( $linkedTable[$j] ) . "\' AND type = \'table\'";
															$res = $wpdb->get_results( $sql, ARRAY_A );
															
															if ( $res[0]["item_name"] != "" ) 
																$color="black";
															else 
																$color = "red"; 
															$flagPublish = 1; 
														}
														
														if ( $createDateLive < $createDateStaging ) { 
															$color = "blue"; 
															$flagPublish = 0; 
														}
														
														if ( $createDateLive == $createDateStaging ) { 
															$color = "black"; 
															$flagPublish = 1; 
														}
													} else {
														$color = "blue"; 
														$flagExist = 1; 
													}
										?>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $linkedTable[$j]; ?>" name="tables[]" id="<?php echo $tableName; ?>" class ="table_check"  disabled/>
											</th>
											<td align="left"><span style="color: <?php echo $color; ?>"><?php echo $linkedTable[$j]; ?></span></td>
											<td align="left">
											<?php
												$sql = "SELECT description FROM xiblox_description WHERE tableName = \'" . $db_conn->Res( $linkedTable[$j] ) . "\'";
												$result = $wpdb->get_results( $sql, ARRAY_A );
												echo $result[0]["description"];
											?>
											</td>
											<td align="left">
											<?php
												if ( $flagExist == 1 ) 
													echo "Doesn\'t exist"; 
												else {
												
													if ( $color == "red" ) 
														echo "Destination is newer";
														
													if ( $color == "blue" ) {
													
														echo "Unpushed";
														$un_table_cnt[$i] = 1;
													}
														
													if ( $color == "black" ) 
														echo "Pushed";
												}
											?>
											</td>
											<td align="left">
											<?php
												if ( $flagPublish == 1 ) echo $createDateLive;
												$color = "";
												$flagPublish = 0;
												$flagExist = 0;
											?>
											</td>
										</tr>
										
										<tr>
											<td colspan="5" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										
										<?php	} 
											}
											$linkedTable = "";
										?>
										<?php } 
										}
										?>
									</table>
									
									<?php } ?>
									
									
									<?php 
										if ( $un_blox_cnt[$i] == 1 ) {
									?>
									<br>
									<h3>Unpushed Blox</h3>
									
									<table style="background:#FFFFFF;width:100%;border:1px solid #DDDDDD;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" cellspacing="0">
									
										<tr>
											<th class="check-column" id="xi_cb"><input type="checkbox" name="checkall" id="checkall_blox"></th>
											<th id="xi_title" align="left" width="25%">Blox Name</th>
											<th id="xi_title" align="left" width="25%">Description</th>
											<th id="xi_title" align="left" width="25%">Push Status</th>
											<th id="xi_title" align="left">Push Date</th>
										</tr>
										
										<tr>
											<td colspan="4" style="border-top:1px solid #DDDDDD;height:1px;"></td>
										</tr>
										
										<?php
										
											$sql = "SELECT blox_name FROM xiblox_tabs where blox_name != \'SitePush\' order by blox_name";
											$res = $wpdb->get_results( $sql, ARRAY_A );
											
											foreach ( $res as $index => $value ) {
											
												foreach ( $value as $bloxName ) {
												
													$sql = "SELECT create_time  FROM information_schema.tables  WHERE table_schema=\'" . $db_name . "\' AND table_name=\'xiblox_tabs\' ";
													$res = $db_conn->queryArray( $sql );
													
													if ( count( $res ) > 0 ) {
													
														$flagExist = 1;
														
														$sql = "SELECT modified_date FROM xiblox_tabs WHERE blox_name = \'" . $db_conn->Res( $bloxName ) . "\'";
														$res = $db_conn->queryArray( $sql );
														
														$liveDate = $res[0]["modified_date"];
														
														$sql = "SELECT modified_date FROM xiblox_tabs WHERE blox_name = \'" . $db_conn->Res( $bloxName ) . "\'";
														$res = $wpdb->get_results( $sql, ARRAY_A );
														
														$stagingDate = $res[0]["modified_date"];
														
														if ( $stagingDate > $liveDate ) 
															$color = "blue";
															
														if ( $stagingDate == $liveDate ) 
															$color = "black";
															
														if ( $stagingDate < $liveDate ) 
															$color = "red";
															
													} else {
														$flagExist = 0; 
														$color = "blue";
													}
													
													if ( $color == "blue" ) {
										?>
										<tr>
											<th class="check-column">
												<input type="checkbox" value="<?php echo $bloxName; ?>" name="blox[]" class = "blox_check" onclick="ReverseLink(this);"/>
											</th>
											<td align="left"><span style="color: <?php echo $color; ?>"><?php echo $bloxName; ?></span></td>
											<td align="left">
											<?php
												$sql = "SELECT blox_content FROM xiblox_tabs WHERE blox_name = \'" . $db_conn->Res( $bloxName ) . "\'";
												$result = $wpdb->get_results( $sql, ARRAY_A );
												echo $result[0]["blox_content"];
											?>
											</td>
											<td align="left">
											<?php
												if ( $flagExist == 0 ) 
													echo "Doesn\'t exist"; 
												else {
													if ( $color == "red" ) 
														echo "Destination is newer";
														
													if ( $color == "blue" ) {
														
														echo "Unpushed";
														$un_blox_cnt[$i] = 1;
													}
													
													if ( $color == "black" ) echo "Pushed";
												}
											?>
											</td>
											<td align="left">
											<?php
												if ( $color != "blue" ) 
													echo $liveDate;
												$color = "";
												$flagPublish = 0;
												$flagExist = 0;
											?>
											</td>
										</tr>
									<?php }
										}
									} ?>
									</table>
									
									<?php } ?>
									
									
									
									
									
									
								</div>
								
								<?php 
									}
									
									$db_conn = null;
								?>		
							</div>
					
							
							
							
							
							
							
							
							
							
							<?php
								$sql = "SELECT * FROM xiblox_tabs WHERE menu = 4 and status = 1";
								require_once( ABSPATH . "wp-content/plugins/XIBLOX/classes/xi_main_class.php" );
								$xi = new XIBLOX();
								$results = $wpdb->get_results( $sql, ARRAY_A );
								if ( count( $results ) != 0 ) {
									foreach ( $results as $result ) {
										if ( strpos("' . $args . '", $result["blox_name"]) == 0 ) continue;
										$content = stripslashes( $result["blox_custom"] );
										$contents = $xi->replace_tag_admin( $content, $result["id"] );
										$contents = $xi->replace_tag( $contents );
									//	$content = ProcessTag($content);
									//	print_r($content);
										echo "<div id=\'custom_tab" . $result["id"] . "\'>";
										echo $contents . "</div>";
									}
								}
							?>
						</div>
						</br>
						<input type="button" name="submit" value="Push All Selected Items" class="button-primary" id = "pub" /> &nbsp;&nbsp;
						<input type="button" name="submit" value="Show Status" class="button-primary" id = "xi_status" />
						<input type="hidden" name="number" value="<?php echo $number[0]; ?>" class="button-primary" id = "number" />
					</div>
				</form>
				
				<div id="dialog" title="Process..." style="display:none;color:#000000;">
					<div id="process" style="padding-left : 20px;">
						<p>
							<span class="progressBar" id="spaceused1">0%</span>
						</p>
						<h3>Log</h3>
						<div id = "ajax_field">
						</div>
					</div>
				</div>
    
				<div id="preview_image">
					<img id = "pre_img" src="" alt="" />
				</div>
			</div>

			<div style="clear:both"></div>

		<?php
		}
		?>';
?>