<?php
	/************************************************************************
	*                                                                       *
	* WHMCompleteSolution - Client Management, Billing & Support System     *
	* Copyright (c) 2007-2012 WHMCS. All Rights Reserved,                   *
	* Licensing Addon Integration Code                                      *
	* Last Modified: 1st October 2010                                       *
	*                                                                       *
	************************************************************************/

	require_once( "../../../../wp-load.php" );

	// Begin Check Function

	require_once ( "xi_license_function.php" );

	// End Check Function

	global $wpdb;

	$key = $_POST['key'];
	
	if ( $key != "" ) {
		$results = GetLicense( $key, "" );
		if ( $results["status"] == "Active" ) {
		# Allow Script to Run
			if ( $results["localkey"] ) {
				# Save Updated Local Key to DB or File
				$localkeydata = $results["localkey"];
				$sql = "DELETE FROM xiblox_license";
				$result = $wpdb->query( $sql );
				$sql = "INSERT INTO xiblox_license( license_key, local_key ) VALUES ( '$key', '$localkeydata' )";
				$result = $wpdb->query( $sql );
				echo "Activated Successufully! Please reload to use this function!";
			}
		} elseif ( $results["status"] == "Invalid" ) {
				echo "Invalid License Key!";
			# Show Invalid Message
		} elseif ( $results["status"] == "Expired" ) {
				echo "License Key Expired!";
			# Show Expired Message
		} elseif ( $results["status"] == "Suspended" ) {
				echo "License Key Suspended!";
			# Show Suspended Message
		}
	}
	else
		echo "Invalid License Key!";
?>