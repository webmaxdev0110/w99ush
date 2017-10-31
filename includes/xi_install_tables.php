<?php
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	/************************ Install xiblox_tabs ********************************/
	$sql = "CREATE TABLE IF NOT EXISTS `xiblox_tabs` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`blox_name` varchar(255) NOT NULL,
				`blox_content` text NOT NULL,
				`blox_custom` text NOT NULL,
				`status` int(11) NOT NULL,
				`menu` bigint(11) NOT NULL DEFAULT '-1',
				`admin_use` int(11) NOT NULL,
				`menu_name` varchar(255) NOT NULL,
				`parent_blox` varchar(255) NOT NULL,
				`blox_editor`	varchar(255) NOT NULL,
				`modified_date` varchar(255) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	
	/************************ Install xiblox_compiled_versions ********************************/
	$sql = "CREATE TABLE IF NOT EXISTS `xiblox_compiled_versions` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`blox_id` int(11) NOT NULL,
				`filename` varchar(256) NOT NULL,
				`comment` text NOT NULL,
				`use_status` boolean,
				`datetime` int(11) NOT NULL,
				UNIQUE KEY `Combination` (`id`,`blox_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	
	/************************ Install xiblox_destination_info ********************************/
	$sql = "CREATE TABLE IF NOT EXISTS xiblox_destination_info (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`connection_name` varchar(255),
				`destination_url` varchar(255),
				`db_host` varchar(255),
				`db_name` varchar(255),
				`db_user` varchar(255),
				`db_password` varchar(255),
				`db_prefix` varchar(255),
				`destination_path` varchar(255),
				`ftp_host` varchar(255),
				`ftp_user` varchar(255),
				`ftp_password` varchar(255),
				`ftp_ssl` varchar(255),
				UNIQUE KEY id (id)
			)ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	
	/************************ Install xiblox_link_table ********************************/
	$sql = "CREATE TABLE IF NOT EXISTS xiblox_link_table (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`table_name` varchar(255),
				`link_table` varchar(255),
				UNIQUE KEY id (id)
			)ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	
	/************************ Install xiblox_description ********************************/
	$sql = "CREATE TABLE IF NOT EXISTS xiblox_description (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`table_name` varchar(255),
				`description` varchar(255),
				UNIQUE KEY id (id)
			)ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	
	/************************ Install xiblox_check_publish ********************************/
	$sql = "CREATE TABLE IF NOT EXISTS xiblox_check_publish (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`table_name` varchar(255),
				`check_status` varchar(255),
				`conn_num` varchar(255),
				UNIQUE KEY id (id)
			)ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	
	/************************ Install xiblox_new_item ********************************/
	$sql = "CREATE TABLE IF NOT EXISTS xiblox_new_item (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`item_name` varchar(255),
				`type` varchar(255),
				UNIQUE KEY id (id)
	)ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	
	/************************ Install xiblox_invoice ********************************/
	$sql = "CREATE TABLE IF NOT EXISTS xiblox_invoice (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`invoice` varchar(255),
				`token` varchar(255),
				UNIQUE KEY id (id)
	)ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	
	/************************ Install xiblox_license ********************************/
	$sql = "CREATE TABLE IF NOT EXISTS xiblox_license (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`license_key` varchar(255),
				`local_key` varchar(255),
				UNIQUE KEY id (id)
	)ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	
	/************************ Install xiblox_push_status *****************************/
	$sql = "CREATE TABLE IF NOT EXISTS xiblox_push_status (
				`id` bigint NOT NULL AUTO_INCREMENT,
				`flag` int(11),
				UNIQUE KEY id (id)
	)ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
	/************************ Install xiblox_publish_status *****************************/
	$sql = "CREATE TABLE IF NOT EXISTS `xiblox_publish_status` (
	  `id` int(11) NOT NULL,
	  `val_id` varchar(256) NOT NULL,
	  `type_id` varchar(256) NOT NULL,
	  `status` int(11) NOT NULL,
	  `date` timestamp NOT NULL
	) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;";
	$result = dbDelta( $sql );
	if ( $result == false ) { 
		echo "Fail to install needed tables to the database"; 
		exit();
	}
?>