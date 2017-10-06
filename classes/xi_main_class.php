<?php

	/*********************************
	* XIPUSH Main Class 			 *
	*								 *
	* @class	XIPUSH	   			 *
	* @package	XIPUSH/classes		 *
	* @author	itabix				 *
	*********************************/

	require_once( "xi_dbconn_class.php");
	require_once( "xi_push_class.php" );
	require_once( "xi_db_push.php" );
	
	class XIPUSH {
	
		/**
		 * @var database handler
		 */
		 
		public $dbhandler		=	null;
		
		/**
		 * @var blox convert handler
		 */
		 
		public $tags_handler	=	null;
	
		/**
		 * XIPUSH Constructor.
		 * @access public
		 */
		 
		public function __construct() {

			// declare wordpress default database handler as global variable
			global $wpdb;
			
			// substitute wordpress default database handler to XIPUSH database handler 
			$this->dbhandler = $wpdb;
			$this->register_ajax();
			// add action and filter 
			$this->init();
		}

		public function enqueue_scripts() {
		 
			wp_enqueue_script('jquery');
			
			// import needed customized css and jQuery plugin css
			wp_enqueue_style( 'style', plugins_url() . '/XIPUSH/assets/css/style.css' );
 			wp_enqueue_style( 'bootstrap-style', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
			wp_enqueue_style( 'dt-bootstrap-style', 'https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css');
			wp_enqueue_style( 'fontawesome-style', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
			wp_enqueue_style( 'theme-style', plugins_url() . '/XIPUSH/assets/css/theme-style.css' );
			wp_enqueue_script( 'jquery-dataTables', 'https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js');
			wp_enqueue_script( 'bootstrap-script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
			wp_enqueue_script( 'dt-bootstrap-script', 'https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js');
			
			wp_enqueue_script( 'jquery-script', plugins_url() . '/XIPUSH/assets/js/script.js' );

			$values = array( 'path' => plugins_url().'/XIPUSH' );
			wp_localize_script( 'jquery-script', 'AJAX', $values );
			
		}

		public function register_ajax() {
			add_action( 'wp_ajax_datatable_post', array( $this, 'datatable_post') );
			add_action( 'wp_ajax_datatable_page', array( $this, 'datatable_page') );
			add_action( 'wp_ajax_datatable_custom_post', array( $this, 'datatable_custom_post') );
			add_action( 'wp_ajax_datatable_media', array( $this, 'datatable_media') );
			add_action( 'wp_ajax_datatable_user', array( $this, 'datatable_user') );
			add_action( 'wp_ajax_datatable_menu', array( $this, 'datatable_menu') );
			add_action( 'wp_ajax_datatable_tables', array( $this, 'datatable_tables') );
			add_action( 'wp_ajax_push_all', array( $this, 'push_all') );
			add_action( 'wp_ajax_push_image', array( $this, 'push_image') );
			add_action( 'wp_ajax_push_all_image', array($this, 'push_all_image'));
			add_action( 'wp_ajax_push_delete', array( $this, 'push_delete') );
			add_action( 'wp_ajax_replace_content', array( $this, 'replace_content') );
			add_action( 'wp_ajax_push_copy_all', array( $this, 'push_copy_all') );
			add_action( 'wp_ajax_push_copy', array( $this, 'push_copy') );
			add_action( 'wp_ajax_push_sync', array( $this, 'push_sync') );
		}

		public function plugin_url(){
			if ( $this->plugin_url ) return $this->plugin_url;
			return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		}
		
		public function init() {
			// add XIPUSH menu in wordpress admin left navigation menu
			add_action( 'admin_menu', 	array( $this, 'add_xiblox_menu' ), 10000, 1 );

			add_action( 'init', array($this, 'stop_heartbeat'), 1 );
			add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		}

		public function stop_heartbeat() {
			wp_deregister_script('heartbeat');
		}

		public function add_xiblox_menu() {
			// add top XIPUSH menu on left nav menu
			add_menu_page( 'XIPUSH', 'XIPUSH', "administrator", 'XIPUSH/xi_push.php', '', plugins_url('XIPUSH/xipush.png') );
			add_submenu_page( 'XIPUSH/xi_push.php', __('Site Push', 'xibloxpublisher'), __('Site Push', 'xibloxpublisher'), "administrator" , 'XIPUSH/xi_push.php', '' );
			// add Settings sub menu to set the push options
			add_submenu_page( 'XIPUSH/xi_push.php', __('Settings', 'xibloxpublisher'),  __('Settings', 'xibloxpublisher'), 			"administrator" , 'XIPUSH/xi_settings.php', '' );

		}

		public function datatable_post() {
			global $wpdb;

			$aColumns = array( 'ID','post_title');
			$sIndexColumn = "ID";
			
			$sLimit = "";
			if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' ) {
				$sLimit = "LIMIT ".intval( $_POST['iDisplayStart'] ).", ".intval( $_POST['iDisplayLength'] );
			}
			
			$sOrder = "";
			if ( isset( $_POST['iSortCol_0'] ) ){
				$sOrder = "ORDER BY  ";
				for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ ) {
					if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" ){
						$sOrder .= "`".$aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ".($_POST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
					}
				}
				$sOrder = substr_replace( $sOrder, "", -2 );
			
				if ( $sOrder == "ORDER BY" ){
					$sOrder = "";
				}
			}

			$sWhere = "";
			if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" ) {
				$sWhere = "WHERE (";
				$sWhere .= "`".$aColumns[1]."` LIKE '%".( $_POST['sSearch'] )."%' OR ";
				$sWhere = substr_replace( $sWhere, "", -3 );
				$sWhere .= ')';
			}
		
			if ( isset($_POST['bSearchable_1']) && $_POST['bSearchable_1'] == "true" && $_POST['sSearch_1'] != '' ) {
				if ( $sWhere == "" ){
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= "`".$aColumns[1]."` LIKE '%".($_POST['sSearch_1'])."%' ";
			}

			if ( $sWhere == "" ) {
				$sWhere = "WHERE post_type LIKE 'post' AND post_name NOT LIKE ''";
			} else {
				$sWhere .= " AND post_type LIKE 'post' AND post_name NOT LIKE ''";
			}
		
			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."posts
				$sWhere			
				$sOrder			
				$sLimit";
		
			$rResult = $wpdb->get_results($sQuery,ARRAY_A);
			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."posts			
				$sWhere			
				$sOrder";
		
			$aResultFilterTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iFilteredTotal = count($aResultFilterTotal);
		
			$sQuery = "			
				SELECT COUNT(*)			
				FROM   ".$wpdb->base_prefix."posts WHERE post_type LIKE 'post' AND post_name NOT LIKE ''";
		
			$aResultTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iTotal = $aResultTotal[0]['COUNT(*)'];

			$output = array(			
				"sEcho" => intval($_POST['sEcho']),			
				"iTotalRecords" => $iTotal,			
				"iTotalDisplayRecords" => $iFilteredTotal,			
				"aaData" => array()			
			);

			foreach ( $rResult as $aRow ){
				$row = array();
				$row[0] = "<input type=\"checkbox\" value=\"".$aRow['ID']."\" name=\"posts[]\" class = \"post_check\" onclick= \"row_click(this,'post')\"/>";
				$row[1] = "<a href=\"".get_edit_post_link( $aRow['ID'])."\">".$aRow['post_title']."</a>";
				$category = "";
				$cat = get_the_category($aRow['ID']);
				foreach($cat as $key => $cats){
					$category .= $cats->name;
					if ($key < (count($cat)-1)){
						$category .= ",";
					}
				}
				$row[2] = $category;
				$args = array(			
					'post_type' => 'attachment',			
					'numberposts' => null,			
					'post_status' => null,			
					'post_parent' => $aRow['ID']			
				);
		
				$att_count = "";			
				$attachments = get_posts($args);			
				if ($attachments) {			
					$att_count = count($attachments)." Attachments";			
				} else {
					$att_count = "No attachments";
				}
		
				$row[3] = $att_count;
				$id = $aRow['ID'];
				$query = "SELECT * FROM `xiblox_publish_status` WHERE `val_id` LIKE '".$id."' AND `type_id` LIKE '0' AND `status`=1";
				if ($wpdb->get_results($query, ARRAY_A)) {
					$row[4] = 'Pushed';
				}
				else {
					$row[4] = 'Unpushed';
				}
				$output['aaData'][] = $row;
			}
			echo json_encode( $output );
			wp_die();
		}

		public function datatable_page() {
			global $wpdb;
	
			$aColumns = array( 'ID','post_title');
			$sIndexColumn = "ID";
	
			$sLimit = "";			
			if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' ) {			
				$sLimit = "LIMIT ".intval( $_POST['iDisplayStart'] ).", ".intval( $_POST['iDisplayLength'] );			
			}
		
			$sOrder = "";			
			if ( isset( $_POST['iSortCol_0'] ) ) {			
				$sOrder = "ORDER BY  ";			
				for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ ) {			
					if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" ) {			
						$sOrder .= "`".$aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ".($_POST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";			
					}			
				}				
		
				$sOrder = substr_replace( $sOrder, "", -2 );			
				if ( $sOrder == "ORDER BY" ) {			
					$sOrder = "";					
				}			
			}
		
			$sWhere = "";			
			if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" ) {			
				$sWhere = "WHERE (";			
				$sWhere .= "`".$aColumns[1]."` LIKE '%".( $_POST['sSearch'] )."%' OR ";			
				$sWhere = substr_replace( $sWhere, "", -3 );			
				$sWhere .= ')';			
			}

			if ( isset($_POST['bSearchable_1']) && $_POST['bSearchable_1'] == "true" && $_POST['sSearch_1'] != '' ) {
				if ( $sWhere == "" ) {			
					$sWhere = "WHERE ";			
				} else {		
					$sWhere .= " AND ";			
				}			
				$sWhere .= "`".$aColumns[1]."` LIKE '%".($_POST['sSearch_1'])."%' ";			
			}

			if ( $sWhere == "" ) {			
				$sWhere = "WHERE post_type LIKE 'page' AND post_name NOT LIKE ''";			
			} else {			
				$sWhere .= " AND post_type LIKE 'page' AND post_name NOT LIKE ''";			
			}

			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."posts			
				$sWhere			
				$sOrder			
				$sLimit			
				";
		
			$rResult = $wpdb->get_results($sQuery,ARRAY_A);
		
			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."posts			
				$sWhere			
				$sOrder			
				";

			$aResultFilterTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iFilteredTotal = count($aResultFilterTotal);
		
			$sQuery = "			
				SELECT COUNT(*)			
				FROM   ".$wpdb->base_prefix."posts WHERE post_type LIKE 'page' AND post_name NOT LIKE ''			
			";
		
			$aResultTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iTotal = $aResultTotal[0]['COUNT(*)'];
		
			$output = array(			
				"sEcho" => intval($_POST['sEcho']),			
				"iTotalRecords" => $iTotal,			
				"iTotalDisplayRecords" => $iFilteredTotal,			
				"aaData" => array()			
			);
		
			foreach ( $rResult as $aRow ) {		
				$row = array();
				$row[0] = "<input type=\"checkbox\" value=\"".$aRow['ID']."\" name=\"posts[]\" class = \"page_check\" onclick= \"row_click(this,'page')\"/>";
				$row[1] = "<a href=\"".get_edit_post_link( $aRow['ID'])."\">".$aRow['post_title']."</a>";

				$args = array(			
					'post_type' => 'attachment',			
					'numberposts' => null,			
					'post_status' => null,			
					'post_parent' => $aRow['ID']			
				);
		
				$att_count = "";
				$attachments = get_posts($args);
				if ($attachments) {
					$att_count = count($attachments)." Attachments";
				} else{
					$att_count = "No attachments";
				}
				$row[2] = $att_count;
		
				$id = $aRow['ID'];
				$query = "SELECT * FROM `xiblox_publish_status` WHERE `val_id` LIKE '".$id."' AND `type_id` LIKE '0' AND `status`='1'";
				if ($wpdb->get_results($query, ARRAY_A)) {
					$row[3] = 'Pushed';
				}
				else {
					$row[3] = 'Unpushed';
				}
				$output['aaData'][] = $row;
			}
			echo json_encode( $output );
			wp_die();
		}

		public function datatable_custom_post() {
			global $wpdb;
			
			$aColumns = array( 'ID','post_title','post_type');
			$sIndexColumn = "ID";
			$sLimit = "";
		
			if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' ) {
				$sLimit = "LIMIT ".intval( $_POST['iDisplayStart'] ).", ".intval( $_POST['iDisplayLength'] );
			}
		
			$sOrder = "";
			if ( isset( $_POST['iSortCol_0'] ) ) {
				$sOrder = "ORDER BY  ";
				for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ ) {
					if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" ) {
						$sOrder .= "`".$aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ".($_POST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
					}
				}
		
				$sOrder = substr_replace( $sOrder, "", -2 );
				if ( $sOrder == "ORDER BY" ) {
					$sOrder = "";
				}
			}
		
			$sWhere = "";
			if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" ) {
				$sWhere = "WHERE (";
				$sWhere .= "`".$aColumns[1]."` LIKE '%".( $_POST['sSearch'] )."%' OR ";
				$sWhere = substr_replace( $sWhere, "", -3 );		
				$sWhere .= ')';			
			}
					
			if ( isset($_POST['bSearchable_1']) && $_POST['bSearchable_1'] == "true" && $_POST['sSearch_1'] != '' ) {
				if ( $sWhere == "" ) {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= "`".$aColumns[1]."` LIKE '%".($_POST['sSearch_1'])."%' ";
			}
		
			$post_type_arr = array();			
			$post_types=get_post_types('','objects');			
			foreach ($post_types as $key => $post_type ) {			
				$post_type_name = $key;			
				if (in_array($post_type_name,array('post','page','attachment','revision','nav_menu_item'))) {
					continue;
				} else {
					array_push($post_type_arr,"'".$key."'");
				}
			}
		
			if ( $sWhere == "" ) {
				$sWhere = "WHERE post_type IN(".implode(',',$post_type_arr).") AND post_name NOT LIKE ''";
			} else {
				$sWhere .= " AND post_type IN(".implode(',',$post_type_arr).") AND post_name NOT LIKE ''";
			}
		
			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."posts			
				$sWhere			
				$sOrder			
				$sLimit			
				";

			$rResult = $wpdb->get_results($sQuery,ARRAY_A);
			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."posts			
				$sWhere			
				$sOrder			
				";
		
			$aResultFilterTotal = $wpdb->get_results($sQuery,ARRAY_A);			
			$iFilteredTotal = count($aResultFilterTotal);		
			
			$sQuery = "			
				SELECT COUNT(*)			
				FROM   ".$wpdb->base_prefix."posts WHERE post_type IN(".implode(',',$post_type_arr).") AND post_name NOT LIKE ''			
			";

			$aResultTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iTotal = $aResultTotal[0]['COUNT(*)'];
		
			$output = array(			
				"sEcho" => intval($_POST['sEcho']),			
				"iTotalRecords" => $iTotal,			
				"iTotalDisplayRecords" => $iFilteredTotal,			
				"aaData" => array()			
			);
		
			foreach ( $rResult as $aRow ) {
				$row = array();			
				$row[0] = "<input type=\"checkbox\" value=\"".$aRow['ID']."\" name=\"posts[]\" class = \"custom_post_check\" onclick =  \"row_click(this,'custom_post')\"/>";			
				$row[1] = "<a href=\"".get_edit_post_link( $aRow['ID'])."\">".$aRow['post_title']."</a>";			
				$row[2] = $aRow['post_type'];			
				$category = "";			
				foreach ($post_types as $key => $post_type ) {			
					$post_type_name = $key;			
					if (in_array($post_type_name,array('post','page','attachment','revision','nav_menu_item')))	{
						continue;
					}
					$taxonomies = $post_type->taxonomies;
					$taxonomy = $taxonomies[0];
					$cat = get_the_terms($aRow['ID'],$taxonomy);
					if ($cat) {
						foreach($cat as $key => $cats){
							$category .= $cats->name;
							if ($key < (count($cat)-1))	{
								$category .= ",";
							}
						}
					}
				}
		
				$row[3] = $category;			
				$args = array(			
					'post_type' => 'attachment',			
					'numberposts' => null,			
					'post_status' => null,			
					'post_parent' => $aRow['ID']			
				);
		
				$att_count = "";			
				$attachments = get_posts($args);			
				if ($attachments) {			
					$att_count = count($attachments)." Attachments";			
				} else {
					$att_count = "No attachments";
				}
		
				$row[4] = $att_count;			
				$id = $aRow['ID'];
				$query = "SELECT * FROM `xiblox_publish_status` WHERE `val_id` LIKE '".$id."' AND `type_id` LIKE '0' AND `status`='1'";
				if ($wpdb->get_results($query, ARRAY_A)) {
					$row[5] = 'Pushed';
				}
				else {
					$row[5] = 'Unpushed';
				}
				$output['aaData'][] = $row;
			}
		
			echo json_encode( $output );
			wp_die();
		}

		public function datatable_media() {
			global $wpdb;

			$aColumns = array( 'ID','post_title','post_parent');
			$sIndexColumn = "ID";
			$sLimit = "";
		
			if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' ) {
				$sLimit = "LIMIT ".intval( $_POST['iDisplayStart'] ).", ".intval( $_POST['iDisplayLength'] );
			}

			$sOrder = "";
		
			if ( isset( $_POST['iSortCol_0'] ) ){
				$sOrder = "ORDER BY  ";			
				for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ ){			
					if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" ){
						$sOrder .= "`".$aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ".	($_POST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
					}
				}
		
				$sOrder = substr_replace( $sOrder, "", -2 );
				if ( $sOrder == "ORDER BY" ){
					$sOrder = "";			
				}			
			}

			$sWhere = "";
			if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" ){
				$sWhere = "WHERE (";			
				$sWhere .= "`".$aColumns[1]."` LIKE '%".( $_POST['sSearch'] )."%' OR ";			
				$sWhere = substr_replace( $sWhere, "", -3 );			
				$sWhere .= ')';			
			}
		
			if ( isset($_POST['bSearchable_1']) && $_POST['bSearchable_1'] == "true" && $_POST['sSearch_1'] != '' ){
				if ( $sWhere == "" ){
					$sWhere = "WHERE ";			
				} else {
					$sWhere .= " AND ";			
				}
				$sWhere .= "`".$aColumns[1]."` LIKE '%".($_POST['sSearch_1'])."%' ";			
			}
		
			if ( $sWhere == "" ){			
				$sWhere = "WHERE post_type LIKE 'attachment'";			
			}else{
				$sWhere .= " AND post_type LIKE 'attachment'";			
			}
		
			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."posts			
				$sWhere			
				$sOrder			
				$sLimit			
				";
		
			$rResult = $wpdb->get_results($sQuery,ARRAY_A);			
			
			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."posts			
				$sWhere
				$sOrder			
				";
		
			$aResultFilterTotal = $wpdb->get_results($sQuery,ARRAY_A);			
			$iFilteredTotal = count($aResultFilterTotal);		
			
			$sQuery = "			
				SELECT COUNT(*)			
				FROM   ".$wpdb->base_prefix."posts WHERE post_type LIKE 'attachment'			
			";
		
			$aResultTotal = $wpdb->get_results($sQuery,ARRAY_A);			
			$iTotal = $aResultTotal[0]['COUNT(*)'];	
			
			$output = array(			
				"sEcho" => intval($_POST['sEcho']),			
				"iTotalRecords" => $iTotal,			
				"iTotalDisplayRecords" => $iFilteredTotal,			
				"aaData" => array()			
			);
		
			foreach ( $rResult as $aRow ){
				$row = array();			
				$row[0] = "<input type=\"checkbox\" value=\"".$aRow['ID']."\" name=\"attachments[]\" class = \"media_check\" onclick= \"row_click(this,'media')\"/>";			
				$src_32 = wp_get_attachment_image_src($aRow['ID'],array(16,16),true);		
				$row[1] = "<img src=\"".$src_32[0]."\" alt = \"Attachment\" />";			
				$src = wp_get_attachment_image_src($aRow['ID'],full);			
				$ext = pathinfo($src[0], PATHINFO_EXTENSION);			
				$ext = strtolower($ext);			
				$cont = "<a href=\"".get_edit_post_link($aRow['ID'])."\">".$aRow['post_title']."</a>";			
				if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'bmp'){
					$cont .= "<a href=\"javascript:preview('".$src[0]."',".$src[1].",".$src[2].")\" id = \"preview_img\">Preview</a>";
				}
				$row[2] = $cont;			
				$pid = $aRow['post_parent']; 			
				if ($pid == 0){			
					$cont3 = "<i style=\"color:#777\">none</i>";			
				}else{			
					$attach_post = get_post( $pid );			
					$cont3 = $attach_post->post_title;			
				}
		
				$row[3] = $cont3;
		
				$id = $aRow['ID'];
				$query = "SELECT * FROM `xiblox_publish_status` WHERE `val_id` LIKE '".$id."' AND `type_id` LIKE '2' AND `status`='1'";
				if ($wpdb->get_results($query, ARRAY_A)) {
					$row[4] = 'Pushed';			
				} else {
					$row[4] = 'Unpushed';
				}
				$output['aaData'][] = $row;
			}
			echo json_encode( $output );
			wp_die();
		}

		public function datatable_user() {
			global $wpdb;
			$aColumns = array( 'ID','user_login','user_email');
			$sIndexColumn = "ID";
		
			$sLimit = "";
		
			if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' )	{
				$sLimit = "LIMIT ".intval( $_POST['iDisplayStart'] ).", ". intval( $_POST['iDisplayLength'] );
		
			}
		
			$sOrder = "";			
			if ( isset( $_POST['iSortCol_0'] ) ){			
				$sOrder = "ORDER BY  ";			
				for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ ){			
					if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" ){
						$sOrder .= "`".$aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ".($_POST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";			
					}			
				}
				$sOrder = substr_replace( $sOrder, "", -2 );			
				if ( $sOrder == "ORDER BY" ){			
					$sOrder = "";			
				}			
			}

			$sWhere = "";
		
			if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" ){			
				$sWhere = "WHERE (";			
				for ( $i=1 ; $i<count($aColumns) ; $i++ ){			
					if ( isset($_POST['bSearchable_'.$i]) && $_POST['bSearchable_'.$i] == "true" ){			
						$sWhere .= "`".$aColumns[$i]."` LIKE '%". $_POST['sSearch']."%' OR ";			
					}
		
				}			
				$sWhere = substr_replace( $sWhere, "", -3 );
				$sWhere .= ')';			
			}
		
			if ( isset($_POST['bSearchable_1']) && $_POST['bSearchable_1'] == "true" && $_POST['sSearch_1'] != '' ){			
				if ( $sWhere == "" ){			
					$sWhere = "WHERE ";			
				}else{			
					$sWhere .= " AND ";			
				}			
				$sWhere .= "`".$aColumns[1]."` LIKE '%".$_POST['sSearch_1']."%' ";			
			}
		
			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."users			
				$sWhere			
				$sOrder			
				$sLimit			
				";
		
			$rResult = $wpdb->get_results($sQuery,ARRAY_A);
		
			$sQuery = "			
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`			
				FROM   ".$wpdb->base_prefix."users			
				$sWhere			
				$sOrder			
				";
		
			$aResultFilterTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iFilteredTotal = count($aResultFilterTotal);

			$sQuery = "			
				SELECT COUNT(*)			
				FROM   ".$wpdb->base_prefix."users";

			$aResultTotal = $wpdb->get_results($sQuery,ARRAY_A);			
			$iTotal = $aResultTotal[0]['COUNT(*)'];
		
			$output = array(			
				"sEcho" => intval($_POST['sEcho']),			
				"iTotalRecords" => $iTotal,			
				"iTotalDisplayRecords" => $iFilteredTotal,			
				"aaData" => array()			
			);
		
			foreach ( $rResult as $aRow ){			
				$row = array();			
				$row[0] = "<input type=\"checkbox\" value=\"".$aRow['ID']."\" name=\"users[]\" class = \"user_check\" onclick =  \"row_click(this,'user')\" />";			
				$row[1] = $aRow['user_login'];			
				$row[2] = $aRow['user_email'];			
				$user = new WP_User( $aRow['ID'] );
				$user_role = "";
				if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
					foreach ( $user->roles as $role ) {
						$user_role = $role;
					}
				}
				$row[3] = $user_role;
		
				$id = $aRow['ID'];
				$query = "SELECT * FROM `xiblox_publish_status` WHERE `val_id` LIKE '".$id."' AND `type_id` LIKE '5' AND `status`='1'";
				if ($wpdb->get_results($query, ARRAY_A)) {
					$row[4] = 'Pushed';			
				} else {
					$row[4] = 'Unpushed';
				}
				$output['aaData'][] = $row;
			}
		
			echo json_encode( $output );
			wp_die();
		}

		public function datatable_menu() {
			global $wpdb;
			
			$aColumns = array( 'term_id','name','slug');
			$sIndexColumn = "term_id";

			$sLimit = "";
			if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' ){
				$sLimit = "LIMIT ".intval( $_POST['iDisplayStart'] ).", ".
					intval( $_POST['iDisplayLength'] );
			}
			
			$sOrder = "";
			if ( isset( $_POST['iSortCol_0'] ) ){
				$sOrder = "ORDER BY  ";
				for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ ){
					if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" ){
						$sOrder .= "`".$aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ".($_POST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
					}
				}
				
				$sOrder = substr_replace( $sOrder, "", -2 );
				if ( $sOrder == "ORDER BY" ){
					$sOrder = "";
				}
			}
			$sJoin = "LEFT JOIN `".$wpdb->base_prefix."term_taxonomy` ON `".$wpdb->base_prefix."terms`.`term_id` = `".$wpdb->base_prefix."term_taxonomy`.`term_id` WHERE `".$wpdb->base_prefix."term_taxonomy`.`taxonomy` = 'nav_menu'";
			
			$sWhere = "";
			if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" )
			{
				$sWhere = "AND (";
				$sWhere .= "`".$aColumns[1]."` LIKE '%".( $_POST['sSearch'] )."%' OR ";
				$sWhere = substr_replace( $sWhere, "", -3 );
				$sWhere .= ')';
			}
		
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS `".$wpdb->base_prefix."terms`.`term_id`, `".$wpdb->base_prefix."terms`.`name`, `".$wpdb->base_prefix."terms`.`slug`
				FROM  `".$wpdb->base_prefix."terms`
				$sJoin
				$sWhere
				$sOrder
				$sLimit
				";
			$rResult = $wpdb->get_results($sQuery,ARRAY_A);
			
			/* Data set length after filtering */
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS `".$wpdb->base_prefix."terms`.`term_id`, `".$wpdb->base_prefix."terms`.`name`, `".$wpdb->base_prefix."terms`.`slug`
				FROM  `".$wpdb->base_prefix."terms`
				$sJoin
				$sWhere
				$sOrder
				";
			$aResultFilterTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iFilteredTotal = count($aResultFilterTotal);

			$sQuery = "
				SELECT COUNT(*)
				FROM  `".$wpdb->base_prefix."terms`
				LEFT JOIN `".$wpdb->base_prefix."term_taxonomy`
				ON `".$wpdb->base_prefix."terms`.`term_id` = `".$wpdb->base_prefix."term_taxonomy`.`term_id` AND `".$wpdb->base_prefix."term_taxonomy`.`taxonomy` = 'nav_menu'";
			$aResultTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iTotal = $aResultTotal[0]['COUNT(*)'];
			
			$output = array(
				"sEcho" => intval($_POST['sEcho']),
				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
			
			foreach ( $rResult as $aRow ){
				$row = array();
				$row[0] = "<input type=\"checkbox\" value=\"".$aRow['term_id']."\" name=\"menu[]\" class = \"menu_check\" onclick =  \"row_click(this,'menu')\"/>";
				$row[1] = $aRow['name'];
				$row[2] = $aRow['slug'];
				
				$id = $aRow['ID'];
				$query = "SELECT * FROM `xiblox_publish_status` WHERE `val_id` LIKE '".$id."' AND `type_id` LIKE '20' AND `status`='1'";
				if ($wpdb->get_results($query, ARRAY_A)) {
					$row[3] = 'Pushed';
		
				}
				else {
					$row[3] = 'Unpushed';
				}
				$row[3] = '-';
				$output['aaData'][] = $row;
			}
			echo json_encode( $output );
			wp_die();
		}

		public function datatable_tables() {
			global $wpdb;

			$aColumns = array( 'ID','table_name','link_table');
			$sIndexColumn = "ID";
			$sLimit = "";

			if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' )
			{
				$sLimit = "LIMIT ".intval( $_POST['iDisplayStart'] ).", ".
					intval( $_POST['iDisplayLength'] );
			}
			
			$sOrder = "";
			if ( isset( $_POST['iSortCol_0'] ) ){
				$sOrder = "ORDER BY  ";
				for ( $i=0 ; $i<intval( $_POST['iSortingCols'] ) ; $i++ ){
					if ( $_POST[ 'bSortable_'.intval($_POST['iSortCol_'.$i]) ] == "true" ){
						$sOrder .= "`".$aColumns[ intval( $_POST['iSortCol_'.$i] ) ]."` ".
							($_POST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
					}
				}
				
				$sOrder = substr_replace( $sOrder, "", -2 );
				if ( $sOrder == "ORDER BY" )
				{
					$sOrder = "";
				}
			}
			
			$sWhere = "";
			if ( isset($_POST['sSearch']) && $_POST['sSearch'] != "" ){
				$sWhere = "WHERE (";
				$sWhere .= "`".$aColumns[1]."` LIKE '%".( $_POST['sSearch'] )."%' OR ";
				$sWhere = substr_replace( $sWhere, "", -3 );
				$sWhere .= ')';
			}

			if ( isset($_POST['bSearchable_1']) && $_POST['bSearchable_1'] == "true" && $_POST['sSearch_1'] != '' ){
				if ( $sWhere == "" ){
					$sWhere = "WHERE ";
				}else{
					$sWhere .= " AND ";
				}
				$sWhere .= "`".$aColumns[1]."` LIKE '%".($_POST['sSearch_1'])."%' ";
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
				FROM   `xiblox_link_table`
				$sWhere
				$sOrder
				$sLimit
				";
			$rResult = $wpdb->get_results($sQuery,ARRAY_A);

			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
				FROM   `xiblox_link_table`
				$sWhere
				$sOrder
				";
			$aResultFilterTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iFilteredTotal = count($aResultFilterTotal);

			$sQuery = "
				SELECT COUNT(*)
				FROM   `xiblox_link_table`";
			$aResultTotal = $wpdb->get_results($sQuery,ARRAY_A);
			$iTotal = $aResultTotal[0]['COUNT(*)'];

			$output = array(
				"sEcho" => intval($_POST['sEcho']),
				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
			
			foreach ( $rResult as $aRow ){
				$row = array();
				$row[0] = "<input type=\"checkbox\" value=\"".$aRow['ID']."\" name=\"tables[]\" class = \"table_check\" onclick =  \"row_click(this,'table')\"/>";
				$row[1] = "<a href=\"".get_edit_post_link( $aRow['ID'])."\">".$aRow['table_name']."</a>";
				$row[2] = $aRow['link_table'];
			
				$id = $aRow['ID'];
				$query = "SELECT * FROM `xiblox_publish_status` WHERE `val_id` LIKE '".$id."' AND `type_id` LIKE '6' AND `status`='1'";
				if ($wpdb->get_results($query, ARRAY_A)) {
					$row[3] = 'Pushed';
				}
				else {
					$row[3] = 'Unpushed';
				}
				$output['aaData'][] = $row;
			}
			echo json_encode( $output );
			wp_die();
		}

		public function push_all() {
			global $wpdb;
			
			$allVals = array();
			$allTypes = array();
			
			$sql = "SHOW TABLES;";
			$tables = $wpdb->get_results($sql, ARRAY_A);
			foreach ($tables as $table) {
				$table = array_values($table);
				$table_name = $table[0];
				$allVals[] = $table_name;
				$allVals[] = $table_name;
				$allTypes[] = 100;
				$allTypes[] = 101;
			}
			// all theme name
			$path = get_theme_root();
			if ( $handle = @opendir( $path ) ) {
				while ( false !== ( $entry = readdir( $handle ) ) ) {
					if ( is_dir( $path . "/" . $entry ) ) {
						if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
							$name = $entry;
							$allVals[] = $name;
							$allTypes[] = 3;
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
							$allVals[] = $name;
							$allTypes[] = 4;
						}
					}
				}
			}
			$allVals[] = 'upload';
			$allTypes[] = 99;	// copy upload folder
			
			echo '{"value":'.json_encode($allVals).', "type":'.json_encode($allTypes).'}';
			wp_die();			
		}

		public function push_image() {
			$path = ABSPATH . "wp-content/uploads/";
			$everything = scandir($path);
			foreach ($everything as $entry) {
				if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
					$name = $entry;
					$plugins[] = base64_encode($entry);
				}
			}
			echo json_encode($plugins);
			wp_die();
		}

		public function push_delete() {
			global $wpdb;
			
			$allVals = array();
			$allTypes = array();
			
			$sql = "SHOW TABLES;";
			$tables = $wpdb->get_results($sql, ARRAY_A);
			foreach ($tables as $table) {
				$table = array_values($table);
				$table_name = $table[0];
				$origin_name = str_replace($wpdb->prefix, '', $table[0]);
				if ($origin_name !== 'options') {
					$allVals[] = $table_name;
					$allTypes[] = 100;
				}
			}
			
			$allVals[] = 'upload';
			$allTypes[] = 98;
			
			echo '{"value":'.json_encode($allVals).', "type":'.json_encode($allTypes).'}';
			wp_die();
		}

		public function replace_content() {
			global $wpdb;
			
			$allVals = array();
			
			$allVals[] = '';
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
									$allVals[] = $path.$name;
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
									$allVals[] = $path.$name;
							}
						}
					}
				}
			}
			$allVals[] = '';
			
			$args = array(
				"post_type" => "attachment",
				"post_mime_type" => null,
				"numberposts" => -1
			);
			$myposts = get_posts( $args );
			foreach ( $myposts as $post ) : setup_postdata( $post ); 
				$allVals[] = $post->ID;
			endforeach;
			
			$path = get_theme_root();
			if ( $handle = @opendir( $path ) ) {
				while ( false !== ( $entry = readdir( $handle ) ) ) {
					if ( is_dir( $path . "/" . $entry ) ) {
						if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
							$name = $entry;
							$allVals[] = $name;
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
							$allVals[] = $name;
						}
					}
				}
			}
			$allVals[] = '';
			
			$allTypes = array();
			$allTypes[] = 7;
			
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
									$allTypes[] = 8;
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
									$allTypes[] = 8;
							}
						}
					}
				}
			}
			$allTypes[] = 9;
			
			$args = array(
				"post_type" => "attachment",
				"post_mime_type" => null,
				"numberposts" => -1
			);
			$myposts = get_posts( $args );
			foreach ( $myposts as $post ) : setup_postdata( $post ); 
				$allTypes[] = 2;
			endforeach;
			
			$path = get_theme_root();
			if ( $handle = @opendir( $path ) ) {
			   while ( false !== ( $entry = readdir( $handle ) ) ) {
					if ( is_dir( $path . "/" . $entry ) ) {
						if ( ( strcmp( $entry, "." ) != 0 ) && ( strcmp( $entry, ".." ) != 0 ) ) {
							$name = $entry;
							$allTypes[] = 3;
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
							$allTypes[] = 4;
						}
					}
				}
			}
			$allTypes[] = '';
			
			echo '{"value":'.json_encode($allVals).', "type":'.json_encode($allTypes).'}';
			wp_die();
		}

		function push_copy_all() {
			global $wpdb;
			
			$type= $_GET['type'];
			$value = $_GET['value'];
		
			// Update pushed status
			$timestamp = date('Y-m-d H:i:s');
			$query = "SELECT * FROM `xiblox_publish_status` WHERE val_id=".$value." AND type_id=".$type;
			if ($wpdb->get_results($query, ARRAY_A)) {
				// already exist
				$query = "UPDATE `xiblox_publish_status` SET `status`='1', `date` = %s WHERE `val_id`=%s AND `type_id`=%s";
				$wpdb->query($wpdb->prepare($query, $timestamp, $value, $type));
			}	
			else {
				// insert
				$query = "INSERT INTO `xiblox_publish_status`(`val_id`, `type_id`, `status`, `date`) VALUES(%s, %s, '1', %s)";
				$wpdb->query($wpdb->prepare($query, $value, $type, $timestamp));
			}
			// end of updating push status
			
			if (isset($_GET['number']) && $_GET['number'] != '') {
				$number = $_GET['number'];	
			} else {
				$number = 1;
			}
			
			// create new instance
			$xi_push = new xiblox_push( $wpdb, $number );
			$xi_db_push = new xiblox_db_push( $wpdb, $number );
			
			if ( ( $value != 'undefined' ) && ( $type != 'undefined' ) ) {
				switch ( $type ) {				
					case 0:
						// copy posts and pages
						if ( $value != "" )
							$xi_push->copy_posts( $value );
						break;						
					case 1:
						// copy links
						if ( $value != "" )
							$xi_push->copy_links( $value );
						break;						
					case 2:
						// copy media
						if ( $value != "" )
							$xi_push->copy_attachment( $value , 0);
						break;						
					case 3:
						// copy theme
						if ( $value != "" )
							$xi_push->copy_theme( $value );
						break;						
					case 4:
						// copy plugins
						if ( $value != "" )
							$xi_push->copy_plugin( $value );
						break;						
					case 5:
						// copy users
						if ( $value != "" )
							$xi_push->copy_user( $value, $number );
						break;						
					case 20: 
						// copy menu
						if ( $value != "" )
							$xi_push->copy_menu( $value );
						break;						
					case 21: 
						// copy single menu
						if ( $value != "" )
							$xi_push->copy_menu_item_single( $value );
						break;
					case 99:
						$path = ABSPATH . "wp-content/uploads";
						$xi_push->copy_directory( 0, $path . "/" . $hash, xiblox_push::$destination_path . "wp-content/uploads/" . $hash );
						break;
					case 100:
						if ($value != "") {
							$xi_db_push -> truncate_table($value);
						}
						break;
					case 101:
						if ($value != "") {
							$xi_db_push -> migrate_table($value);
						}
						break;
				}
			}
			wp_die();
		}

		function push_copy() {
			global $wpdb;
			
			$type= $_GET['type'];
			$value = $_GET['value'];
		
			// Update pushed status
			$timestamp = date('Y-m-d H:i:s');
			$query = "SELECT * FROM `xiblox_publish_status` WHERE val_id=".$value." AND type_id=".$type;
			if ($wpdb->get_results($query, ARRAY_A)) {
				// already exist
				$query = "UPDATE `xiblox_publish_status` SET `status`='1', `date` = %s WHERE `val_id`=%s AND `type_id`=%s";
				$wpdb->query($wpdb->prepare($query, $timestamp, $value, $type));
			}	
			else {
				// insert
				$query = "INSERT INTO `xiblox_publish_status`(`val_id`, `type_id`, `status`, `date`) VALUES(%s, %s, '1', %s)";
				$wpdb->query($wpdb->prepare($query, $value, $type, $timestamp));
			}
			// end of updating push status
			
			if (isset($_GET['number']) && $_GET['number'] != '') {
				$number = $_GET['number'];	
			} else {
				$number = 1;
			}
			
			// create new instance
			$xi_push = new xiblox_push( $wpdb, $number );
			$xi_db_push = new xiblox_db_push( $wpdb, $number );
			
			if ( ( $value != 'undefined' ) && ( $type != 'undefined' ) ) {
				switch ( $type ) {				
					case 0:
						// copy posts and pages
						if ( $value != "" )
							$xi_push->copy_posts( $value );
						break;						
					case 1:
						// copy links
						if ( $value != "" )
							$xi_push->copy_links( $value );
						break;						
					case 2:
						// copy media
						if ( $value != "" )
							$xi_push->copy_attachment( $value , 0);
						break;						
					case 3:
						// copy theme
						if ( $value != "" )
							$xi_push->copy_theme( $value );
						break;						
					case 4:
						// copy plugins
						if ( $value != "" )
							$xi_push->copy_plugin( $value );
						break;						
					case 5:
						// copy users
						if ( $value != "" )
							$xi_push->copy_user( $value, $number );
						break;						
					case 20: 
						// copy menu
						if ( $value != "" )
							$xi_push->copy_menu( $value );
						break;						
					case 21: 
						// copy single menu
						if ( $value != "" )
							$xi_push->copy_menu_item_single( $value );
						break;
					case 99:
						$path = ABSPATH . "wp-content/uploads";
						$xi_push->copy_directory( 0, $path . "/" . $hash, xiblox_push::$destination_path . "wp-content/uploads/" . $hash );
						break;
					case 100:
						if ($value != "") {
							$xi_db_push -> truncate_table($value);
						}
						break;
					case 101:
						if ($value != "") {
							$xi_db_push -> migrate_table($value);
						}
						break;
				}
			}
			wp_die();
		}

		function push_all_image() {
			global $wpdb;
			$type= $_GET['type'];
			$value = $_GET['value'];
		
			// Update pushed status
			$timestamp = date('Y-m-d H:i:s');
			$query = "SELECT * FROM `xiblox_publish_status` WHERE val_id=".$value." AND type_id=".$type;
			if ($wpdb->get_results($query, ARRAY_A)) {
				// already exist
				$query = "UPDATE `xiblox_publish_status` SET `status`='1', `date` = %s WHERE `val_id`=%s AND `type_id`=%s";
				$wpdb->query($wpdb->prepare($query, $timestamp, $value, $type));
			}	
			else {
				// insert
				$query = "INSERT INTO `xiblox_publish_status`(`val_id`, `type_id`, `status`, `date`) VALUES(%s, %s, '1', %s)";
				$wpdb->query($wpdb->prepare($query, $value, $type, $timestamp));
			}
		
			// end of updating push status
			
			if (isset($_GET['number']) && $_GET['number'] != '') {
				$number = $_GET['number'];	
			} else {
				$number = 1;
			}
			
			$hash = base64_decode($_GET['hash']);
			// create new instance
			$xi_push = new xiblox_push( $wpdb, $number );

			$path = ABSPATH . "wp-content/uploads";
			$xi_push->copy_directory( 0, $path . "/" . $hash, xiblox_push::$destination_path . "wp-content/uploads/" . $hash );
			wp_die();
		}

		function push_sync() {
			global $wpdb;

			$type= $_GET['type'];
			$value = $_GET['value'];
		
			// end of updating push status
			if (isset($_GET['number']) && $_GET['number'] != '') {
				$number = $_GET['number'];	
			} else {
				$number = 1;
			}
			
			// create new instance
			$xi_push = new xiblox_push( $wpdb, $number );
			$state = false;
		
			if (in_array($type, array(0,1,2))) {
				$state = $xi_push -> check_state( $value );
			} else if ($type == 5) {
				$state = $xi_push -> check_user_state($value);
			} else if ($type == 10) {
				$state = $xi_push -> check_blox_state($value);
			}
		
			if ($state == true) {
				$status = 0;
			} else {
				$status = 1;
			}
		
			$timestamp = date('Y-m-d H:i:s');
			$query = "SELECT * FROM `xiblox_publish_status` WHERE val_id=".$value." AND type_id=".$type;
			if ($wpdb->get_results($query, ARRAY_A)) {
				// already exist
				$query = "UPDATE `xiblox_publish_status` SET `status`=%s, `date` = %s WHERE `val_id`=%s AND `type_id`=%s";
				$wpdb->query($wpdb->prepare($query, $status, $timestamp, $value, $type));
			} else {
				// insert
				$query = "INSERT INTO `xiblox_publish_status`(`val_id`, `type_id`, `status`, `date`) VALUES(%s, %s, %s, %s)";
				$wpdb->query($wpdb->prepare($query, $value, $type, $status, $timestamp));
			}
			wp_die();
		}
	}