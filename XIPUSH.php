<?php
	/**
	 * Plugin Name: XIPUSH
	 * Plugin URI: 
	 * Description: A multipurpose plugin that require_onces the ability to publish any and all content from one (staging) website to another (live) site.
	 * Version: 1.5
	 * Author: Itabix Inc
	 * Author URI: http://itabix.com
	 * License: GPL2
	 */
	 
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	require_once ( 'includes/xi_install_tables.php' );
	require_once ( 'classes/xi_tags_class.php' );
	require_once ( 'classes/xi_main_class.php' );

	new XIPUSH();
?>