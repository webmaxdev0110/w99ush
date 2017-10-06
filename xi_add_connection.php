<?php
	
	/*****************************************
	* add the new connection ( xiblox menu ) *
	*									     *
	* @package 	XIBLOX/					     *
	* @type 	hidden					     *
	* @author	itabix					     *
	*****************************************/
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	require_once( "classes/xi_dbconn_class.php");
	
	global $wpdb;
	
	$current_user = wp_get_current_user();
	$id = $current_user->ID;
	
	$submit = $_POST["submit"];
	
	if ( $submit == "Save Changes" ) {
	
		$connection_name = $_POST["connection_name"];
		$destination_url = $_POST["destination_url"];
		$db_host = $_POST["db_host"];
		$db_name = $_POST["db_name"];
		$db_user = $_POST["db_user"];
		$db_password = $_POST["db_password"];
		$db_prefix = $_POST["db_prefix"];
		$destination_path = $_POST["destination_path"] . "/";
		$ftp_host = $_POST["ftp_host"];
		$ftp_user = $_POST["ftp_user"];
		$ftp_password = $_POST["ftp_password"];
		
		if ( isset( $_POST['ftp_ssl'] ) )
			$ftp_ssl = 1;
		else 
			$fpt_ssl = 0;
		
		// check if this connection is same with current db
		if ( ( $db_user == DB_USER ) && ( $db_name == DB_NAME ) ) {
			echo "<script>
					alert('DB info is same as current site');
					window.history.back();
				  </script>";
			exit();
		}
		if ( $destination_path == ABSPATH ) {
			echo "<script>
					alert('Target site path is same as current site');
					window.history.back();
				  </script>";
			exit();
		}
		
		// Check db connection
		$db_conn = new DatabaseManager('mysql', $db_hosst, $db_user, $db_password, $db_name);
		
		
		// check same connection exist 
		$sql = "select connection_name from xiblox_destination_info where connection_name = '$connection_name'";
		$result = $wpdb->get_results( $sql, ARRAY_A );
		
		if ( $result[0]["connection_name"] != "" ) {
		
			echo "<script>
					alert('Same connection already exists.');
					window.history.back();
				 </script>";
			exit();
			
		}
		
		
		$result = $wpdb->insert( "xiblox_destination_info", array( 'connection_name' => $connection_name, 'destination_url' => $destination_url, 'db_host' => $db_host, 'db_name' => $db_name, 'db_user' => $db_user, 'db_password' => $db_password, 'db_prefix' => $db_prefix, 'destination_path' => $destination_path, 'ftp_host' => $ftp_host, 'ftp_user' => $ftp_user, 'ftp_password' => $ftp_password, 'ftp_ssl' => $ftp_ssl ) );
		
		/************************************************************/
		
		$blox_custom = "||tabs|database:" . $connection_name . "||";
		$modified_date = date( "Y-m-d H:i:s" );
		
		$sql = "SELECT blox_custom FROM xiblox_tabs WHERE blox_name = 'SitePush'";
		$res = $wpdb->get_results( $sql, ARRAY_A );
		$isBlox = $res[0]["blox_custom"];
		
		if ( $isBlox == "" ) {	
		
			$sql = "INSERT INTO xiblox_tabs ( `blox_name`, `blox_content`, `blox_custom`, `status`, `menu`, `admin_use`, `modified_date` ) VALUES ( 'SitePush', 'SitePush', '" . $blox_custom . "', 1, 2, 1, '" . $modified_date . "' )";
			
		} else {
		
			$sql = "SELECT connection_name from xiblox_destination_info";
			$res = $wpdb->get_results( $sql, ARRAY_A );
			$destination_name = '';
			
			for ( $i = 0; $i < count( $res ); $i++ ) {
			
				if ( $i != ( count( $res ) - 1 ) )
					$destination_name .= $res[$i]["connection_name"] . ",";
				else	
					$destination_name .= $res[$i]["connection_name"];
					
			}
			
			$blox_custom = "||tabs|database:" . $destination_name . "||";
			$sql = "UPDATE xiblox_tabs SET blox_custom='" . $blox_custom . "' WHERE blox_name = 'SitePush'";
			
		}
		
		$res = $wpdb->query( $sql );
		
		if ( $res == '' ) {
			echo "<script>
					alert('Please remove the old XIBLOX tables in the database.
						   If you have the same problem, then please contact with XIBLOX administrator!');
				  </script>";
			exit();
		}
		
		echo "<script>
				location.href='" . site_url() . "/wp-admin/admin.php?page=XIBLOX/xi_settings.php';
			  </script>";
		exit();
	}
?>

<div class="container pull-left">

	<h2>XIBLOX Settings</h2>

	<div class="panel panel-default">
	  <div class="panel-heading">
	    <h3 class="panel-title">Add New Connection</h3>
	  </div>
	  <div class="panel-body">
	    <form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ); ?>">
			<div class="alert alert-info" role="alert">
			  Fill in the following fields with the destination site's details (the site you are copying/publishing to, usually called the Live site). You can ask your hosting company to provide any information you do not have.
			</div>

			<div class="form-group">
			    <label for="connection_name">Connection Name </label>
			    <input type="text" class="form-control" name="connection_name" value="" id="siteName"  placeholder="Connection Name (no spaces)">
			</div>
			<div class="form-group">
			    <label for="destination_url">Destination Site URL </label>
			    <input type="text" class="form-control" name="destination_url" value=""  placeholder="Destination Site URL">
			</div>

			<strong>Destination Site Database Information</strong>
			<div class="form-group">
			    <label for="db_host">Database Host Name</label>
			    <input type="text" class="form-control" name="db_host" value="localhost"  placeholder="Database Host Name">
			</div>
			<div class="alert alert-info" role="alert">
			  If the destination site is using the same database server as this site, the database host name is usually <strong>localhost</strong>
			</div>
			<div class="form-group">
			    <label for="db_user">Database Username</label>
			    <input type="text" class="form-control" name="db_user" value=""  placeholder="Database Username">
			</div>
			<div class="form-group">
			    <label for="db_name">Database Name</label>
			    <input type="text" class="form-control" name="db_name" value=""  placeholder="Database Name">
			</div>
			<div class="form-group">
			    <label for="db_password">Database Password</label>
			    <input type="password" class="form-control" name="db_password" value=""  placeholder="Database Password">
			</div>
			<div class="form-group">
			    <label for="db_prefix">Table Prefix( Default: wp_  )</label>
			    <input type="text" class="form-control" name="db_prefix" value="wp_"  placeholder="WordPress Database Table Prefix">
			</div>
			<div class="form-group">
			    <label for="destination_path">Site Directory Path</label>
			    <input type="text" class="form-control" name="destination_path" value=""  placeholder="Site Directory Path(ex: /home/username/public_html/sitename)">
			</div>
			<div class="panel panel-default">
			  <div class="panel-heading">
			    <h3 class="panel-title">Destination Site FTP Information</h3>
			  </div>
			  <div class="panel-body">
			    <div class="alert alert-info" role="alert">
				  For transfers to a site on the same server, leave the following fields blank. For destination sites under a different server, use the destination site's FTP information to fill in the following fields:
				</div>
				<div class="form-group">
				    <label for="ftp_host">Ftp Server</label>
				    <input type="text" class="form-control" name="ftp_host" value=""  placeholder="Ftp Server">
				</div>
				<div class="form-group">
				    <label for="ftp_host">Ftp UserName</label>
				    <input type="text" class="form-control" name="ftp_user" value=""  placeholder="Ftp UserName">
				</div>
				<div class="form-group">
				    <label for="ftp_host">Ftp Password</label>
				    <input type="password" class="form-control" name="ftp_password" value=""  placeholder="Ftp Password">
				</div>
				<div class="form-group">
				    <label for="ftp_host">Using secured connection(ssl)</label>
				    <input type="checkbox" class="form-control" name="ftp_ssl" />
				</div>
			  </div>
			</div>
			
			<p class="submit">
				<input type="submit" class="btn btn-primary" name="submit" value="Save Changes" onclick="return ConfirmForm();" />
			</p>
			
		</form>
	  </div>
	</div>
	
	
	
</div>

<script>
	function ConfirmForm() {
		// check all info is valid 
		var connection_name = jQuery("input[name=connection_name]").val();
		var destination_url = jQuery("input[name=destination_url]").val();
		var db_host = jQuery("input[name=db_host]").val();
		var db_user = jQuery("input[name=db_user]").val();
		var db_name = jQuery("input[name=db_name]").val();
		var db_password = jQuery("input[name=db_password]").val();
		var db_prefix = jQuery("input[name=db_prefix]").val();
		var destination_path = jQuery("input[name=destination_path]").val();
		
		if ( connection_name == "" ) {
			alert("Please put connection name.");
			return false;
		}
		
		if ( destination_url == "" ) {
			alert("Please put destination site url.");
			return false;
		}
		
		if ( db_host == "" ) {
			alert("Please put database host name.");
			return false;
		}
		
		if ( db_user == "" ) {
			alert("Please put database user name.");
			return false;
		}
		
		if ( db_name == "" ) {
			alert("Please put database name.");
			return false;
		}
		
		// if ( db_password == "" ) {
		// 	alert("Please put database password.");
		// 	return false;
		// }
		
		if ( db_prefix == "" ) {
			alert("Please put database table prefix.");
			return false;
		}
		
		if ( destination_path == "" ) {
			alert("Please put site directory path.");
			return false;
		}
	
		// check connection name 
		if ( connection_name.indexOf(' ') >= 0 ) {
			alert("Connection Name shouldn't have a space");
			return false;
		} 
		
		// check trailing slash of destination url 
		if ( destination_url.slice(-1) == "/" ) {
			alert("Destination url includes trailing slash now. Please remove it.");
			return false;
		} 
		
		// check trailing slash of destination path 
		if ( destination_path.slice(-1) == "/" ) {
			alert("Directory path includes trailing slash now. Please remove it.");
			return false;
		} 
		return true;
	}
</script>