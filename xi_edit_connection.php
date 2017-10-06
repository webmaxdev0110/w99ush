<?php
	/****************************************
	* edit push connection  ( xiblox menu ) *
	*									    *
	* @package 	XIBLOX/					    *
	* @type 	hidden					    *
	* @author	itabix					    *
	****************************************/
	
	@session_start();
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}

	global $wpdb;
	
	$number = 1;

	if ( isset( $_POST['submit'] ) ) {
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
		if ( ( $db_user == DB_USER) && ( $db_name == DB_NAME ) ) {
			echo "<script>
					alert('DB info is same as current site');
					location.reload();
				  </script>";
			exit();
		}
		if ( $destination_path == ABSPATH ) {
			echo "<script>
					alert('Target site path is same as current site');
				  </script>";
		}
		
		require_once( "classes/xi_dbconn_class.php" );
		
		// Check db connection
		$db_conn = new DatabaseManager('mysql', $db_host, $db_user, $db_password, $db_name);
		
		$result = $wpdb->query( "UPDATE xiblox_destination_info SET connection_name = '$connection_name', destination_url = '$destination_url', db_host = '$db_host', db_name = '$db_name', db_user = '$db_user', db_password = '$db_password', db_prefix = '$db_prefix', destination_path = '$destination_path', ftp_host = '$ftp_host', ftp_user = '$ftp_user', ftp_password = '$ftp_password', ftp_ssl = '$ftp_ssl' WHERE id = " . $number );
		
		// change default push blox 
		$sql = "SELECT connection_name FROM xiblox_destination_info";
		$res = $wpdb->get_results( $sql, ARRAY_A );
		
		$blox_custom = "||tabs|database:" . $res[0]["connection_name"];
		
		for ( $i = 1; $i < count( $res ); $i++ ) {
			$blox_custom .= "," . $res[$i]["connection_name"];
		}
		
		$blox_custom .= "||";
		
		$sql = "UPDATE xiblox_tabs SET blox_custom = '" . $blox_custom . "', menu = 2 WHERE blox_name = 'SitePush'";
		$res = $wpdb->query( $sql );

		
	} else {
	
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
		$ftp_host = $result[0]["ftp_host"];
		$ftp_user = $result[0]["ftp_user"];
		$ftp_password = $result[0]["ftp_password"];
		$ftp_ssl = $result[0]["ftp_ssl"];
		
		$destination_path = substr( $destination_path, 0, -1 );
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
			    <input type="text" class="form-control" name="connection_name" value="<?php echo $connection_name; ?>" id="siteName"  placeholder="Connection Name (no spaces)">
			</div>
			<div class="form-group">
			    <label for="destination_url">Destination Site URL </label>
			    <input type="text" class="form-control" name="destination_url" value="<?php echo $destination_url; ?>"  placeholder="Destination Site URL">
			</div>

			<strong>Destination Site Database Information</strong>
			<div class="form-group">
			    <label for="db_host">Database Host Name</label>
			    <input type="text" class="form-control" name="db_host" value="<?php echo $db_host; ?>"  placeholder="Database Host Name">
			</div>
			<div class="alert alert-info" role="alert">
			  If the destination site is using the same database server as this site, the database host name is usually <strong>localhost</strong>
			</div>
			<div class="form-group">
			    <label for="db_user">Database Username</label>
			    <input type="text" class="form-control" name="db_user" value="<?php echo $db_user; ?>"  placeholder="Database Username">
			</div>
			<div class="form-group">
			    <label for="db_name">Database Name</label>
			    <input type="text" class="form-control" name="db_name" value="<?php echo $db_name; ?>"  placeholder="Database Name">
			</div>
			<div class="form-group">
			    <label for="db_password">Database Password</label>
			    <input type="password" class="form-control" name="db_password" value="<?php echo $db_password; ?>"  placeholder="Database Password">
			</div>
			<div class="form-group">
			    <label for="db_prefix">Table Prefix( Default: wp_  )</label>
			    <input type="text" class="form-control" name="db_prefix" value="<?php echo $db_prefix; ?>"  placeholder="WordPress Database Table Prefix">
			</div>
			<div class="form-group">
			    <label for="destination_path">Site Directory Path</label>
			    <input type="text" class="form-control" name="destination_path" value="<?php echo $destination_path; ?>"  placeholder="Site Directory Path(ex: /home/username/public_html/sitename)">
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
				    <input type="text" class="form-control" name="ftp_host" value="<?php echo $ftp_host; ?>"  placeholder="Ftp Server">
				</div>
				<div class="form-group">
				    <label for="ftp_host">Ftp UserName</label>
				    <input type="text" class="form-control" name="ftp_user" value="<?php echo $ftp_user; ?>"  placeholder="Ftp UserName">
				</div>
				<div class="form-group">
				    <label for="ftp_host">Ftp Password</label>
				    <input type="psssword" class="form-control" name="ftp_password" value="<?php echo $ftp_password; ?>"  placeholder="Ftp Password">
				</div>
				<div class="form-group">
				    <label for="ftp_host">Using secured connection(ssl)</label>
				    <input type="checkbox" class="form-control" name="ftp_ssl" <?php $ssl = $ftp_ssl;
						if ( $ssl == 1 )
							echo "checked=\"checked\"";
					?> />
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
