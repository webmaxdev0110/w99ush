<?php
	/**
	 * save the selected tab as session value
	 * @package	XIBLOX/
	 * @author	itabix
	 */
	
	@session_start();
	
	$tab = $_GET['tab'];
	$select_all = $_GET['select_all'];
	$select_post = $_GET['select_post'];
	$select_page = $_GET['select_page'];
	$select_link = $_GET['select_link'];
	$select_media = $_GET['select_media'];
	$select_theme = $_GET['select_theme'];
	$select_plugin = $_GET['select_plugin'];
	$select_user = $_GET['select_user'];
	$select_table = $_GET['select_table'];
	$select_menu = $_GET['select_menu'];
	$select_blox = $_GET['select_blox'];
	$select_unpushed = $_GET['select_unpushed'];
	
	$_SESSION['tab_index'] = $tab;
	
	if ( $select_all != "" )
		$_SESSION['select_all'] = $select_all;
	if ( $select_post != "" )
		$_SESSION['select_post'] = $select_post;
	if ( $select_page != "" )
		$_SESSION['select_page'] = $select_page;
	if ( $select_link != "" )
		$_SESSION['select_link'] = $select_link;
	if ( $select_media != "" )
		$_SESSION['select_media'] = $select_media;
	if ( $select_theme != "" )
		$_SESSION['select_theme'] = $select_theme;
	if ( $select_plugin != "" )
		$_SESSION['select_plugin'] = $select_plugin;
	if ( $select_user != "" )
		$_SESSION['select_user'] = $select_user;
	if ( $select_table != "" )
		$_SESSION['select_table'] = $select_table;
	if ( $select_menu != "" )
		$_SESSION['select_menu'] = $select_menu;
	if ( $select_blox != "" )
		$_SESSION['select_blox'] = $select_blox;
	if ( $select_unpushed != "" )
		$_SESSION['select_unpushed'] = $select_unpushed;

?>