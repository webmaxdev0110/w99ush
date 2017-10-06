<?php
	
	/*********************************
	* XIBLOX Push Basic Class 		 *
	*								 *
	* @class	xiblox_push			 *
	* @package	XIBLOX/classes		 *
	* @author	itabix				 *
	*********************************/
	
	class xiblox_push {
	
		/**
		 * @var current database handler
		 */
		private static 	$curr_dbhandler		= 	null;
		
		/**
		 * @var connection database handler
		 */
		private static 	$db_conn			= 	null;
		
		/**
		 * @var connection database name
		 */
		private static 	$db_name			= 	null;
		
		/**
		 * @var connection number
		 */
		private static 	$number				= 	null;
		
		/**
		 * @var destination url
		 */
		private static 	$destination_url	= 	null;
		
		/**
		 * @var destination path
		 */
		public  static	$destination_path	= 	null;
		
		/**
		 * @var connection database prefix
		 */
		private static 	$db_prefix			= 	null;
		
		/**
		 * @var ftp connection host
		 */
		private static 	$ftp_host			= 	null;
		
		/**
		 * @var ftp connection user
		 */
		private static 	$ftp_user			= 	null;
		
		/**
		 * @var ftp connection password
		 */
		private static 	$ftp_password		= 	null;
		
		/**
		 * @var ftp ssl connection
		 */
		private static 	$ftp_ssl			= 	null;
		
		
		public function __construct( $db, $conn_number ) {
			// current database handler
			self::$curr_dbhandler = $db;
			
			// substitue all variables
			$sql = "select * from xiblox_destination_info where id = '$conn_number'";
			$result = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
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
			
			self::$db_conn = new DatabaseManager('mysql', $db_host, $db_user, $db_password, $db_name);
			self::$db_name = $db_name;
			self::$number = $conn_number;
			self::$destination_url = $destination_url;
			self::$destination_path = $destination_path;
			self::$db_prefix = $db_prefix;
			
			self::$ftp_host = $ftp_host;
			self::$ftp_user = $ftp_user;
			self::$ftp_password = $ftp_password;
			self::$ftp_ssl = $ftp_ssl;
		}
		
		/**
		 * replace current site url to destination one
		 * @ access	public
		 * @ param 	string wanted to convert $string
		 * @ return string // converted url
		 */
		public function string_replace_url( $string ) {
			
			$baseurl = get_site_url();
			$short_baseurl = str_replace( "www.", "", $baseurl );
			
			if ( strpos( $string, $baseurl ) > 0 ) 
				$string = str_replace( $baseurl, self::$destination_url, $string );
			
			if ( strpos( $string, $short_baseurl ) > 0 ) 
				$string = str_replace( $short_baseurl, self::$destination_url, $string );
			
			return $string;
		}
		
		/**
		 * replace current site path to destination one
		 * @ access	public
		 * @ param 	string wanted to convert $string
		 * @ return string // converted path
		 */
		public function string_replace_path( $string ) {
			
			$basepath = ABSPATH;
			
			// add temp prefix for strpos command
			$string = "TempPrefix" . $string;
			
			if ( strpos( $string, $basepath ) > 0 ) {
				$string = str_replace( $basepath, self::$destination_path, $string );
			}
			
			// remove temp prefix 
			$string = str_replace( "TempPrefix", "", $string );
			
			return $string;
		}

		/**
		 * convert object to array
		 * @ access	public
		 * @ param 	object
		 * @ return array
		 */
		public function object_to_array( $obj ) {
		
			if ( is_object( $obj ) ) {
				// Gets the properties of the given object
				// with get_object_vars function
				$obj = get_object_vars( $obj );
			}

			if ( is_array( $obj ) ) {
				/*
				* Return array converted to object
				* Using __FUNCTION__ (Magic constant)
				* for recursive call
				*/
				return array_map( array( $this, 'object_to_array'), $obj );
			} else {
				// Return array
				return $obj;
			}
		}
		
		/**
		 * convert array string including current site url to destination one
		 * @ access	public
		 * @ param 	array
		 * @ return array
		 */
		public function convert_array_url( $array ) {
			
			foreach ( $array as $key => $value ) {
			
				if ( is_array( $value ) ) {
					$array[$key] = $this->convert_array_url( $value );
				}
				else {
				
					if ( is_object( $value ) == 1 )
						$value = $this->object_to_array( $value );
					
					if ( $array[$key] !== true )
						$array[$key] = $this->string_replace_url( $value );
				}
			}
			
			return $array;
			
		}
		
		/**
		 * convert array string including current site path to destination one
		 * @ access	public
		 * @ param 	array
		 * @ return array
		 */
		public function convert_array_path( $array ) {
		
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) ) 
					$array[$key] = $this->convert_array_path( $value );
				else {
					
					if ( is_object( $value ) == 1 )
						$value = $this->object_to_array( $value );
						
					$array[$key] = $this->string_replace_path( $value );
				}
			}
			
			return $array;
			
		}

		/**
		 * copy only database and .htaccess file
		 * @access	public
		 * @return 	void
		 */
		public function copy_db_except_wpcontent() {
		
			$sql = "SHOW TABLES LIKE '%'";
			$results = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
			for ( $i = 0; $i < count( $results ); $i++ ) {
				foreach ( $results[$i] as $tableName ) {
					// copy each table
					$this->copy_table( $tableName );
				}
			}
			
			// copy .htaccess file 
			$path_dir = ABSPATH . ".htaccess";
			@copy( $path_dir, self::$destination_path . ".htaccess" );
			
		}
		
		/**
		 * check if the table exists on destination site
		 * @access	public
		 * @param	table name
		 * @return 	boolean
		 */
		public function check_table( $table_name ) {
			
			$sql = "SELECT create_time FROM information_schema.tables WHERE table_schema = '" . self::$db_name . "' AND table_name = '" . $table_name . "'";
			$res = self::$db_conn->queryArray( $sql );
			
			if ( count($res) > 0 ) 
				return true;
			else 
				return false;
		}
		
		/**
		 * check if user exists on destination site
		 * @access	public
		 * @param	user id : $user_id
		 * @return 	int ( user id founded )
		 */
		public function check_user( $user_id ) {
			
			// get current user info
			$user_info = get_userdata( $user_id );
			
			$sql = "SELECT * FROM " . self::$db_prefix . "users WHERE user_login = '" . $user_info->user_login . "'";
			$res = self::$db_conn->queryRow( $sql );
			
			if ( $res[0][0] != '' ) 
				return $res[0][0];
			else	
				return null;
				
		}
		
		/**
		 * copy database , .htaccess file and all files inside of wp-content
		 * @access	public
		 * @return 	void
		 */
		public function copy_db() {
		
			$sql = "SHOW TABLES LIKE '%'";
			$results = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
			for ( $i = 0; $i < count($results); $i++ ) {
				foreach ( $results[$i] as $tableName ) {
					// copy each table
					$this->copy_table( $tableName );
				}
			}
			
			// copy .htaccess file 
			$path_dir = ABSPATH . ".htaccess";
			@copy( $path_dir, self::$destination_path . ".htaccess" );
			
			// copy extra directory 
			$path = ABSPATH . "wp-content/";
			
			if ( $handle = @opendir( $path ) ) {
			   while ( false !== ( $entry = readdir( $handle ) ) ) {
					if ( is_dir( $path . "/" . $entry ) ) {
						if ( ( strcmp($entry, ".") != 0 ) && ( strcmp( $entry, ".." ) != 0 ) && ( $entry != "plugins" ) && ( $entry != "themes" ) && ( $entry != "upgrade" ) && ( $entry != "uploads" ) ) {
							$this->copy_directory( 0, $path . "/" . $entry, self::$destination_path . "wp-content/" . $entry );
						}
					}
				}
			}
		}
		
		/**
		 * check if same post exists
		 * @access public
		 * @param checking post id $post_id
		 * @return int // if yes, the post id ; if not, create new post id and return that id
		 */
		public function check_same_post( $post_id ) {
			
			// get post content by id
			$post = get_post( $post_id, ARRAY_A );
			
			// check if same post exist in destination site
			$sql = "SELECT id FROM " . self::$db_prefix . "posts WHERE post_title='" . self::$db_conn->RES( $post["post_title"] ) . "' AND post_name='" . $post["post_name"] . "' AND post_type = '" . $post["post_type"] . "' AND post_parent = '" . self::$db_conn->Res( $post["post_parent"] ) . "'";
			$res = self::$db_conn->queryArray( $sql );
			
			$is_id = $res[0]["id"];
			
			if ( $is_id == "" ) {
				// create this post to destination site
				$this->copy_posts( $post_id );
				
				// get the just registered post ID 
				$sql = "SELECT max(id) as maxId FROM " . self::$db_prefix . "posts";
				$res = self::$db_conn->queryArray( $sql );
				
				$is_id = $res[0]["maxId"];
			} else 
				// update this post at destination site
				$this->copy_posts( $post_id );
			
			return $is_id;
			
		}

		/**
		 * Check for update
		 * @access	public
		 * @param	post array, destination post id
		 * @return 	void
		 */

		public function check_state( $post_id ) {
			
			$post = get_post( $post_id, ARRAY_A );
			$sql = "SELECT * FROM `" . self::$db_prefix . "posts`
				WHERE 	post_name = '" 	. $post['post_name'] . "' AND
						post_title = '" . $post['post_title']. "' AND
						post_type = '"  . $post['post_type']. "' AND
						post_modified = '" . $post['post_modified'] . "'";
			$res_result = self::$db_conn->queryArray( $sql );
			if (count($res_result) == 0) { // Need to be updated
				return true;
			}
			else { // Don't need to
				return false;
			}
		}

		/**
		 * Check for update
		 * @access	public
		 * @param	post array, destination post id
		 * @return 	void
		 */

		public function check_user_state( $user_id ) {
			$user_info = get_userdata( $user_id );

			$sql = "SELECT * FROM `" . self::$db_prefix . "users`
				WHERE 	user_login = '" . $user_info -> user_login . "' AND
						user_pass = '"  . $user_info -> user_pass. "' AND
						user_nicename = '"  . $post -> user_nicename. "' AND
						user_email = '" . $post -> user_email . "'";

			$res_result = self::$db_conn->queryArray( $sql );
			if (count($res_result) == 0) { // Need to be updated
				return true;
			}
			else { // Don't need to
				return false;
			}
		}

		/**
		 * Check for update
		 * @access	public
		 * @param	post array, destination post id
		 * @return 	void
		 */

		public function check_blox_state( $blox_name ) {
			// get the selected blox content
			global $wpdb;
			$sql = "SELECT * FROM xiblox_tabs WHERE blox_name = '$blox_name'";
			$res = $wpdb -> get_results( $sql, ARRAY_A );
			$blox = $res[0];

			$sql = "SELECT * FROM `xiblox_tabs`
					WHERE 	blox_name = '" . $blox['blox_name'] . "' AND
							modified_date = '"  . $user_info['modified_date']. "'";
						
			$res_result = self::$db_conn->queryArray( $sql );
			if (count($res_result) == 0) { // Need to be updated
				return true;
			}
			else { // Don't need to
				return false;
			}
		}
		
		/**
		 * update the post
		 * @access	public
		 * @param	post array, destination post id
		 * @return 	void
		 */
		public function update_post( $post, $destination_post_id ) {
			
			$parent_id = $post['post_parent'];
			
			// get the parent post id from destination site
			if ( $parent_id != 0 ) {
			
				// get parent_post info from current site 
				$parent_post = get_post( $parent_id, ARRAY_A );
				
				$sql = "SELECT * FROM " . self::$db_prefix . "posts WHERE post_name = '" . $parent_post["post_name"] . "' AND post_title = '" . $parent_post["post_title"] . "' AND post_parent = '" . $parent_post["post_parent"] . "'";
				$res_parent = self::$db_conn->queryArray( $sql );
				
				$destination_parent_id = $res_parent[0]["ID"];
				
			} else
				$destination_parent_id = 0;
			
			
			$vals = sprintf("post_author = '%s', post_date = '%s', post_date_gmt = '%s', post_content = '%s', post_title = '%s', post_excerpt = '%s',
					post_status = '%s', comment_status = '%s', ping_status = '%s', post_password = '%s', post_name = '%s', to_ping = '%s', pinged = '%s',
					post_modified = '%s',post_modified_gmt = '%s', post_content_filtered = '%s',
					post_parent = '%s', guid = '%s',menu_order = '%s', post_type = '%s', post_mime_type = '%s', comment_count = '%s'", 
					
					$post['post_author'], $post['post_date'], $post['post_date_gmt'], self::$db_conn->Res( $post['post_content'] ), self::$db_conn->Res( $post['post_title'] ), self::$db_conn->Res( $post['post_excerpt'] ), $post['post_status'], $post['comment_status'], $post['ping_status'], $post['post_password'], self::$db_conn->Res( $post['post_name'] ), $post['to_ping'], $post['pinged'], 			$post['post_modified'], $post['post_modified_gmt'], $post['post_content_filtered'], $destination_parent_id, self::$db_conn->Res( $post['guid'] ), $post['menu_order'], $post['post_type'], $post['post_mime_type'], $post['comment_count'] );
		
			$sql = "UPDATE " . self::$db_prefix . "posts SET " . $vals . " WHERE ID = $destination_post_id";
			$sql = $this->string_replace_url( $sql );
			
			self::$db_conn->query( $sql ) or printf("Error occurs while updating the post !" . $post["post_title"]);
			
		}
		
		/**
		 * insert the post
		 * @access	public
		 * @param	post array
		 * @return 	post id ( just created )
		 */
		public function insert_post( $post ) {
		
			$parent_id = $post['post_parent'];

			if ( $parent_id != 0 ) { // exist parent post
			
				// get parent post content
				$sql = "SELECT * FROM " . self::$curr_dbhandler->base_prefix . "posts WHERE ID = $parent_id";
				$fetchp = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
				
				$p_post_name = $fetchp[0]['post_name'];
				$p_post_title = $fetchp[0]['post_title'];
				$p_post_parent = $fetchp[0]['post_parent'];
				
				// find the corresponding parent id at destination site
				$sql = "SELECT * FROM " . self::$db_prefix . "posts WHERE post_title = '" . $p_post_title . "' AND post_name = '" . $p_post_name . "' AND post_parent = '" . $p_post_parent . "'";
				$restp = self::$db_conn->queryArray( $sql );
				
				$destination_parent_id = $restp[0]["ID"];
				
			} else
				$destination_parent_id = 0;
			
			$vals = sprintf("'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s',	%s, '%s', '%s', '%s', '%s', '%s'",  $post['post_author'], $post['post_date'], $post['post_date_gmt'], self::$db_conn->Res( $post['post_content'] ), self::$db_conn->Res( $post['post_title'] ), self::$db_conn->Res( $post['post_excerpt'] ), $post['post_status'], $post['comment_status'], $post['ping_status'], $post['post_password'], self::$db_conn->Res( $post['post_name'] ), $post['to_ping'], $post['pinged'], $post['post_modified'], $post['post_modified_gmt'], $post['post_content_filtered'], $destination_parent_id, self::$db_conn->Res( $post['guid'] ), $post['menu_order'], $post['post_type'], $post['post_mime_type'], $post['comment_count']	);
			
			$cols = "post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,	post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count";
			
			$sql = "INSERT INTO " . self::$db_prefix . "posts (" . $cols . ") VALUES (" . $vals . ")";
			$sql = $this->string_replace_url( $sql );
			
			self::$db_conn->queryInsert( $sql ) or printf ( "Error Occurs while inserting the post " . $post["post_title"] );
			
			$post_id = self::$db_conn->getPrevInsertId();
			
			return $post_id;
		}
		
		/**
		 * copy the custom field of the post
		 * @access	public
		 * @param	post id, destination post id
		 * @return 	void 
		 */
		public function copy_custom_field( $post_id, $des_post_id ) {
			
			// get the custom field of this post
			$custom_fields = get_post_custom( $post_id );
			foreach ( $custom_fields as $key => $custom_field ) {
				
				if ( !in_array($key, array('_edit_last', '_edit_lock', '_thumbnail_id', '0'))) {
					$real_custom_field = $custom_field[0];
					echo $real_custom_field;
					$sql = "SELECT * FROM `" . self::$db_prefix . "postmeta` WHERE `meta_key` LIKE '" . $key . "' AND `meta_value` LIKE '" . $real_custom_field . "'";
					$res = self::$db_conn->queryArray( $sql );
					
					if ( count($res) == 0 ) {
					
						$sql = "INSERT INTO " . self::$db_prefix . "postmeta ( post_id, meta_key, meta_value ) values( $des_post_id,'" . $key . "', '" . $real_custom_field . "')";
						$sql = $this->string_replace_url( $sql );
						self::$db_conn->query( $sql );
					}
					else {
						$sql = "UPDATE " . self::$db_prefix . "postmeta SET `meta_value` = '".$real_custom_field."' WHERE `post_id` = $des_post_id AND `meta_key` LIKE '".$key."'";
						$sql = $this->string_replace_url( $sql );
						
						self::$db_conn->query( $sql );
					}
				}
			}
		}
		
		/**
		 * copy the comment of the post
		 * @access	public
		 * @param	post id, destination post id
		 * @return 	void 
		 */
		public function copy_comment( $post_id, $des_post_id ) {
			
			// delete all comments first
			$sql = "DELETE FROM " . self::$db_prefix . "comments WHERE comment_post_ID = " . $des_post_id;
			self::$db_conn->query( $sql );
			
			$comment_id_map = array();
			
			$sql = "SELECT * FROM " . self::$curr_dbhandler->base_prefix . "comments WHERE comment_post_ID = " . $post_id . " ORDER BY comment_ID";
			$comments = self::$curr_dbhandler->get_results( $sql, ARRAY_A );		
			
			if ( is_array( $comments ) ) {
			
				foreach ( $comments as $comment ) {
				
					$user_id = $this->check_user( $comment['user_id'] );
					
					if ( $user_id == null ) {
						$user_id = 0;
					}
					
					if ( $comment['comment_parent'] != 0 ) {
					
						if ( !array_key_exists( $comment['comment_parent'], $comment_id_map ) ) {
							echo "<span class=\"error\">ERROR: could not rebuild comment tree!</span>";
							exit();
						}				
						$comment['comment_parent'] = $comment_id_map[$comment['comment_parent']];
					}
				
					$vals = sprintf("'%s', '%s', '%s', '%s', '%s', '%s', '%s', 
									'%s', '%s', '%s', '%s', 
									'%s', '%s', '%s'", $parent_id, $comment['comment_author'], $comment['comment_author_email'], $comment['comment_author_url'], $comment['comment_author_IP'], $comment['comment_date'], $comment['comment_date_gmt'], self::$db_conn->Res( $comment['comment_content'] ), $comment['comment_karma'], self::$db_conn->Res( $comment['comment_approved'] ), self::$db_conn->Res( $comment['comment_agent'] ), $comment['comment_type'], $comment['comment_parent'], $user_id );
									
					$cols = "comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id";
					
					$sql = "INSERT INTO " . self::$db_prefix . "comments (" . $cols . ") values (" . $vals . ")";
					$sql = $this->string_replace_url( $sql );
					
					self::$db_conn->queryInsert( $sql );
					
					$comment_id = self::$db_conn->getPrevInsertId();
					
					$comment_id_map[$comment['comment_ID']] = $comment_id;
				}
						
			}
			
		}
		
		
		/**
		 * copy the categories of the post
		 * @access	public
		 * @param	post id, destination post id
		 * @return 	void 
		 */
		public function copy_category( $post_id, $des_post_id ) {
		
			// delete categories first
			$sql = "DELETE FROM " . self::$db_prefix . "term_relationships WHERE object_id = $des_post_id";
			self::$db_conn->query( $sql );
			
			$new_post_cats = get_the_category( $post_id );
			
			foreach ( $new_post_cats as $new_post_cat ) {
			
				$new_post_catslug = $new_post_cat->slug;
				$new_post_catname = $new_post_cat->name;
				$new_post_catterm_group = $new_post_cat->term_group;
			
				$sql = "SELECT slug FROM " . self::$db_prefix . "terms WHERE slug LIKE '" . $new_post_catslug . "'";
				$result = self::$db_conn->queryArray( $sql );
				
				if ( $result[0]["slug"] == '' ) {
				
					$sql = "INSERT INTO " . self::$db_prefix . "terms ( name, slug, term_group ) values ('" . $new_post_catname . "','" . $new_post_catslug . "','" . $new_post_catterm_group . "')";
					$sql = $this->string_replace_url( $sql );
					
					self::$db_conn->queryInsert( $sql );
					$term_id = self::$db_conn->getPrevInsertId();
					
					$sql = "SELECT * FROM " . self::$curr_dbhandler->base_prefix . "term_taxonomy WHERE term_id = " . $new_post_cat->term_id . " LIMIT 0,1";
					$taxonomy = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
					
					$vals = sprintf( "'%s', '%s', '%s', '%s', '%s'", $term_id, $taxonomy[0]['taxonomy'], $taxonomy[0]['description'], $taxonomy[0]['parent'], $taxonomy[0]['count'] );
					
					$sql = "INSERT INTO " . self::$db_prefix . "term_taxonomy ( term_id, taxonomy, description, parent, count) values ( $vals )";
					$sql = $this->string_replace_url( $sql );
					
					self::$db_conn->queryInsert( $sql );
					
					$taxonomy_id = self::$db_conn->getPrevInsertId();
					
				} else {
				
					$sql = "UPDATE " . self::$db_prefix . "terms SET name = '" . $new_post_catname . "', term_group = '" . $new_post_catterm_group . "' WHERE slug LIKE '" . $new_post_catslug . "'";
					$sql = $this->string_replace_url( $sql );
					
					self::$db_conn->query( $sql );
					
					$sql = "SELECT term_id FROM " . self::$db_prefix . "terms WHERE slug LIKE '" . $new_post_catslug . "'";
					$result = self::$db_conn->queryArray( $sql );
					$term_id = $result[0]["term_id"];
					
					$sql = "SELECT term_taxonomy_id FROM " . self::$db_prefix . "term_taxonomy WHERE term_id = " . $term_id . " LIMIT 0,1";
					$result = self::$db_conn->queryArray( $sql );
					
					$taxonomy_id = $result[0]["term_taxonomy_id"];
					
				}
				
				$sql = "INSERT INTO " . self::$db_prefix . "term_relationships VALUES ( $des_post_id, $taxonomy_id, 0 )";
				self::$db_conn->queryInsert( $sql );
				
			}
			
		}
		
		/**
		 * find the attached file
		 * @access	public
		 * @param	attachment id
		 * @return 	array ( found files ) 
		 */
		public function get_attached_file( $attach_id ) {
			
			// get attachment file
			$file = get_attached_file( $attach_id );
			$ext = pathinfo( $file, PATHINFO_EXTENSION );
			$ext = strtolower( $ext );
			
			$dirname = dirname( $file );
			$files = array();
			
			if ( $ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'bmp' ) {
			
				$path1 = wp_get_attachment_image_src( $attach_id, 'full' );
				$files[0] = $path1[0];
				
				$path2 = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
				$files[1] = $path2[0];
				
				$path3 = wp_get_attachment_image_src( $attach_id, 'medium' );
				$files[2] = $path3[0];
				
				$path4 = wp_get_attachment_image_src( $attach_id, 'large' );
				$files[3] = $path4[0];
				
				// if attachment is not above type
				$continue_val = 3;
				
				$sql = "SELECT meta_value FROM " . self::$curr_dbhandler->prefix . "postmeta WHERE post_id = $post_id AND meta_key = '_wp_attachment_metadata'";
				$res = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
				
				$array = unserialize( $res[0]["meta_value"] );
				
				if ( !$array ) {
				
					$image_obj = get_intermediate_image_sizes();
					
					foreach ( $image_obj as $name ) {
					
						if ( ( $name != "thumbnail" ) && ( $name != "medium" ) && ( $name != "large" ) ) {
						
							$continue_val ++;
							$path = wp_get_attachment_image_src( $attach_id, $name );
							$files[$continue_val] = $path[0];
							
						}
					}
				} else {
				
					foreach ( $array as $key => $value ) {
					
						if ( $key == "sizes" ) {
						
							foreach ( $value as $size_name => $size_value ) {
							
								if ( ( $size_name != "thumbnail" ) && ( $size_name != "medium" ) && ( $size_name != "large" ) ) {
								
									$continue_val ++;
									$path = wp_get_attachment_image_src( $attach_id, $size_name );
									$files[$continue_val] = $path[0];
									
								}
							}
						}
					}
				}
				
			} else
				$files[0] = $file;
				
			return $files;
		}
		
		/**
		 * copy the attached file
		 * @access	public
		 * @param	files, attachment id
		 * @return 	void
		 */
		public function copy_attached_file( $files, $attach_id ) {
			
			$file = get_attached_file( $attach_id );
			$dirname = dirname( $file );
			
			$flag_echo = 0;
			
			foreach ( $files as $file ) {
							
				if ( $file == "" ) 
					continue;
					
				$filename = basename( $file );
				
				$pos = strpos( $dirname, "/wp-content" );
				$newfilename = self::$destination_path . substr( $dirname, (int)( $pos + 1 ) ) . "/" . $filename;
				
				$dir = substr( $dirname, (int)( $pos + 1 ) );
				$dirs = explode( "/", $dir );
				
				$maindir = self::$destination_path;
				
				foreach ( $dirs as $dr ) {
					$maindir .= "/" . $dr;
					if ( !is_dir( $maindir ) )
						@mkdir($maindir);
				}
				
				@chmod( $dir . "/", 0755 );
				
				$file = $dirname . "/" . $filename;
				
				if ( @copy( $file, $newfilename ) ) {
					
					if ( $flag_echo == 0 ) {
						echo "Copying file: " . $file;
						$flag_echo = 1;
					} 
				}
				else
					echo "<span class=\"error\">Error reading file: " . $file . "</span>";
			}
			
		}
		
		/**
		 * copy the attachments of the post
		 * @access	public
		 * @param	post id, destination post id
		 * @return 	void 
		 */
		public function copy_attachment( $post_id , $dest_id) {
			// get the attachments of this post
			
			if ($dest_id == 0)
				$attach_id = $post_id;
			else
				$attach_id = get_post_thumbnail_id( $post_id );
			$meta = get_post_meta( $attach_id );
		
			$attach_file = $meta['_wp_attached_file'][0];
			$attach_filemeta = $meta['_wp_attachment_metadata'][0];
			
			$sql = "SELECT meta_key FROM `" . self::$db_prefix . "postmeta` WHERE `meta_key` LIKE '_wp_attached_file' AND `meta_value` LIKE '" . $attach_file . "'";
			$res = self::$db_conn->queryArray( $sql );

			if ( $res[0]["meta_key"] == '' ) {
			
				$results = get_post( $attach_id, ARRAY_A );
				
				$vals = sprintf("'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'", $results['post_author'], $results['post_date'], $results['post_date_gmt'], self::$db_conn->Res( $results['post_content'] ), self::$db_conn->Res( $results['post_title'] ), self::$db_conn->Res( $results['post_excerpt'] ), $results['post_status'], $results['comment_status'], $results['ping_status'], $results['post_password'], self::$db_conn->Res( $results['post_name'] ), $results['to_ping'], $results['pinged'], $results['post_modified'], $results['post_modified_gmt'], self::$db_conn->Res( $results['post_content_filtered'] ), $dest_id, self::$db_conn->Res( $results['guid'] ), $results['menu_order'], $results['post_type'], $results['post_mime_type'], $results['comment_count'] );
				
				$cols = "post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,	post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count";
				
				$sql = "INSERT INTO " . self::$db_prefix . "posts ( " . $cols . " ) VALUES ( " . $vals . " )";
				$sql = $this->string_replace_url( $sql );
				
				self::$db_conn->queryInsert( $sql );
				
				$p_new_id = self::$db_conn->getPrevInsertId();
				if ($dest_id != 0) {
					$sql = "INSERT INTO " . self::$db_prefix . "postmeta ( post_id, meta_key, meta_value ) values( $dest_id, '_thumbnail_id', $p_new_id)";
					self::$db_conn->queryInsert( $sql );
				}
				
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta ( post_id, meta_key, meta_value ) VALUES ( $p_new_id, '_wp_attached_file', '" . $attach_file . "')";
				$sql = $this->string_replace_url( $sql );
				
				self::$db_conn->queryInsert( $sql );
				
				$attach_filemeta = str_replace( "'", "''", $attach_filemeta );
				
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta ( post_id, meta_key, meta_value ) values( $p_new_id, '_wp_attachment_metadata', '" . $attach_filemeta . "')";
				$sql = $this->string_replace_url( $sql );
				
				self::$db_conn->queryInsert( $sql );
				
			} else {
			
				$att_post = get_post( $post_id, ARRAY_A );
				
				$sql = "SELECT * FROM " . self::$db_prefix . "posts WHERE guid LIKE '" . $att_post['guid'] . "'";
				$res = self::$db_conn->queryArray( $sql );
				
				if ( count( $res ) > 0 ) {
				
					$p_id = $res[0]["ID"];
					$parent_id = $dest_id;
					
					// if ( $parent_id != 0 ) {
					
					// 	$sql = "SELECT * FROM " . self::$curr_dbhandler->base_prefix . "posts WHERE ID = $parent_id";
					// 	$fetchp = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
						
					// 	$pid = $fetchp[0]['guid'];
						
					// 	$sql = "SELECT * FROM " . self::$db_prefix . "posts WHERE guid LIKE '" . $pid . "'";
					// 	$restp = self::$db_conn->queryArray( $sql );
						
					// 	$new_parent_id = $restp[0]["ID"];
					// } else
					// 	$new_parent_id = 0;
					
					$vals = sprintf( "post_author = '%s', post_date = '%s', post_date_gmt = '%s', post_content = '%s', post_title = '%s', post_excerpt = '%s', post_status = '%s', comment_status = '%s', ping_status = '%s', post_password = '%s', post_name = '%s', to_ping = '%s', pinged = '%s', post_modified = '%s',post_modified_gmt = '%s', post_content_filtered = '%s', post_parent = '%s', guid = '%s',menu_order = '%s', post_type = '%s', post_mime_type = '%s', comment_count = '%s'", $att_post['post_author'], $att_post['post_date'], $att_post['post_date_gmt'], self::$db_conn->Res( $att_post['post_content'] ), self::$db_conn->Res( $att_post['post_title'] ), self::$db_conn->Res( $att_post['post_excerpt'] ), $att_post['post_status'], $att_post['comment_status'], $att_post['ping_status'], $att_post['post_password'], self::$db_conn->Res( $att_post['post_name'] ), $att_post['to_ping'], $att_post['pinged'] , $att_post['post_modified'], $att_post['post_modified_gmt'], self::$db_conn->Res( $att_post['post_content_filtered'] ), $parent_id, self::$db_conn->Res( $att_post['guid'] ), $att_post['menu_order'], $att_post['post_type'], $att_post['post_mime_type'], $att_post['comment_count'] );
					
					$sql = "UPDATE " . self::$db_prefix . "posts SET " . $vals . " WHERE ID = $p_id";
					$sql = $this->string_replace_url( $sql );
					
					self::$db_conn->query( $sql ) or print( "error running update" );

					$sql = "DELETE FROM " . self::$db_prefix . " WHERE `meta_key` LIKE '_thumbnail_id' AND `post_id`=$p_id";
					self::$db_conn->query( $sql );

					$sql = "UPDATE " . self::$db_prefix . "postmeta SET `meta_value` = $p_id WHERE `post_id`=$dest_id AND `meta_key` LIKE '_thumbnail_id'";
					$sql = $this->string_replace_url( $sql );
					
					self::$db_conn->query( $sql );

					$sql = "UPDATE " . self::$db_prefix . "postmeta SET `meta_value` = '".$attach_file."' WHERE `post_id`=$p_id AND `meta_key` LIKE '_wp_attached_file'";
					$sql = $this->string_replace_url( $sql );
					
					self::$db_conn->query( $sql );
					
					$attach_filemeta = str_replace( "'", "''", $attach_filemeta );
					
					$sql = "UPDATE " . self::$db_prefix . "postmeta SET `meta_value` = '".$attach_filemeta."' WHERE `post_id`=$p_id AND `meta_key` LIKE '_wp_attachment_metadata'";
					$sql = $this->string_replace_url( $sql );
					
					self::$db_conn->query( $sql );
				}
			}
			
			// ********************
			// Copying Files Part *
			// ********************
				
			if ( empty( self::$ftp_host ) || empty( self::$ftp_user ) || empty( self::$ftp_password ) ) {
			
				if ( !empty( self::$destination_path ) ) {
				
					// get the attached file
					$files = $this->get_attached_file( $attach_id );
					
					// copy the attached file
					$this->copy_attached_file( $files, $attach_id );
					
				}
			} else {
			
				if ( self::$ftp_ssl == 1 )
					$conn_id = ftp_ssl_connect( self::$ftp_host, 21, 10 );
				else 
					$conn_id = ftp_connect( self::$ftp_host );
				
				if ( $conn_id ) {
				
					$login_result = ftp_login( $conn_id, self::$ftp_user, self::$ftp_password );
					echo "Connection established!</br>";
					
				}
				
				if ( ( !$conn_id ) || ( !$login_result ) )
					echo "FTP Connect Error!</br>";
				else {
					ftp_pasv( $conn_id, true );
					
					$file = get_attached_file( $post_id );
					
					$ext = pathinfo( $file, PATHINFO_EXTENSION );
					$ext = strtolower( $ext );
					
					$dirname = dirname( $file );
					
					$files = $this->get_attached_file( $post_id );
						
					
					@ftp_cdup( $conn_id );
					
					foreach ( $files as $file ) {
					
						if ( $file == "" ) 
							continue;
							
						ftp_chdir( $conn_id, "/" );
						ftp_chdir( $conn_id, self::$destination_path );
						
						$filename = basename( $file );
						
						$pos = strpos( $dirname, "/wp-content" );
						$newfilename = self::$destination_path . substr( $dirname, (int)( $pos + 1) ) . "/" . $filename;
						$dir = substr( $dirname,(int)( $pos + 1 ) );
						$dirs = explode( "/", $dir );
						
						$maindir = self::$destination_path;
						$length_dest = strlen( self::$destination_path );
						
						$last_char = self::$destination_path[$length_dest - 1];
						
						if ( $last_char == '/' )
							$maindir = substr( $maindir, 0, -1 );
							
						$maindir = "";
						
						foreach ( $dirs as $dr ) {
						
							$maindir .= $dr . "/";
							$length_dest = strlen( self::$destination_path );
							$last_char = $self::$destination_path[$length_dest - 1];
							
							if ( $last_char == '/' )
								$cdir = substr( self::$destination_path, 0, -1 );
							
							if ( @ftp_chdir( $conn_id, $dr . "/" ) == FALSE ) {
								ftp_mkdir( $conn_id, $dr );
								ftp_chmod( $conn_id, 0755, $dr . "/" );
								ftp_chdir( $conn_id, $dr . "/" );
							}
							
						}
						
						$file = $dirname . "/" . $filename;
						$fp = @fopen( $file, 'r' );
						$err = ftp_nb_fput( $conn_id, $filename, $fp, FTP_BINARY );
						
						while ( $err == FTP_MOREDATA ) {
							// Do whatever you want
							echo ".";
							// Continue upload...
							$err = ftp_nb_continue( $conn_id );
						}
						
						if ( $err != FTP_FINISHED ) 
						   echo "<span class=\"error\">Error reading file: " . $file . "</span>";
						else
							echo "Copying file: " . $file . "<br>";
							
						@fclose($fp);
					}
				}
				
				@ftp_close( $conn_id );
			}
		}
		
		/**
		 * copy the menu meta for pushing the menu item
		 * @access	public
		 * @params	menu item info
		 * @return 	void
		 */
		public function copy_menu_meta( $push_id, $obj_id, $parent_id, $post_id ) {
			
			// insert or update "_menu_item_classes" meta value 
			$menu_item_classes = "";
			
			$sql = "SELECT meta_id FROM " . self::$db_prefix . "postmeta WHERE post_id = $push_id AND meta_key = '_menu_item_classes'";
			$res = self::$db_conn->queryArray( $sql );
			
			$meta_id = $res[0]["meta_id"];
			
			if ( $meta_id == "" ) {
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ( $push_id, '_menu_item_classes', '" . $menu_item_classes . "' )";
			} else {
				$sql = "UPDATE " . self::$db_prefix . "postmeta SET meta_value = '" . $menu_item_classes . "' WHERE post_id = $push_id AND meta_key = '_menu_item_classes'";
			}
			
			$sql = $this->string_replace_url( $sql );
			$result = self::$db_conn->query( $sql );
			
			if ( !$result ) { 
				echo "Fail to store _menu_item_classes meta value in postmeta table"; 
				exit(); 
			}
			
			// insert or update "_menu_item_menu_item_parent" meta value 
			
			if ( $parent_id != 0 ) {
				$parent_post = get_post( $parent_id, ARRAY_A );
				
				$sql = "SELECT id FROM " . self::$db_prefix . "posts WHERE post_title = '" . self::$db_conn->Res( $parent_post["post_title"] ) . "' AND post_name = '" . self::$db_conn->Res( $parent_post["post_name"] ) . "' AND post_type = '" . self::$db_conn->Res( $parent_post["post_type"] ) . "' AND post_parent = '" . self::$db_conn->Res( $parent_post["post_parent"] ) . "'";
				$res = self::$db_conn->queryArray( $sql );
				
				if ( $res[0]["id"] != '' ) 
					$menu_item_menu_item_parent = $res[0]["id"];
			} else 
				$menu_item_menu_item_parent = 0;
				
			$sql = "SELECT meta_id FROM " . self::$db_prefix . "postmeta WHERE post_id = $push_id AND meta_key = '_menu_item_menu_item_parent'";
			$res = self::$db_conn->queryArray( $sql );
			
			$meta_id = $res[0]["meta_id"];
			
			if ( $meta_id == "" ) {
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ( $push_id, '_menu_item_menu_item_parent', '" .  $menu_item_menu_item_parent . "' )";
			} else {
				$sql = "UPDATE " . self::$db_prefix . "postmeta SET meta_value = '" . $menu_item_menu_item_parent . "' WHERE post_id = $push_id AND meta_key = '_menu_item_menu_item_parent'";
			}
			
			$sql = $this->string_replace_url( $sql );
			$result = self::$db_conn->query( $sql );
			
			if ( !$result ) { 
				echo "Fail to store _menu_item_menu_item_parent meta value in postmeta table"; 
				exit(); 
			}
			
			// insert or update "_menu_item_object" meta value
			$menu_item_object = get_post_meta( $post_id, '_menu_item_object' );
			$menu_item_object = $menu_item_object[0];
			
			$sql = "SELECT meta_id FROM " . self::$db_prefix . "postmeta WHERE post_id = $push_id AND meta_key = '_menu_item_object'";
			$res = self::$db_conn->queryArray( $sql );
			
			$meta_id = $res[0]["meta_id"];
			
			if ( $meta_id == "" ) {
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta ( `post_id`, `meta_key`, `meta_value` ) VALUES ( $push_id, '_menu_item_object', '" . $menu_item_object . "' )";
			} else {
				$sql = "UPDATE " . self::$db_prefix . "postmeta SET meta_value = '" . $menu_item_object . "' WHERE post_id = $push_id AND meta_key = '_menu_item_object'";
			}
			
			$sql = $this->string_replace_url( $sql );
			$result = self::$db_conn->query( $sql );
			
			if ( !$result ) { 
				echo "Fail to store _menu_item_object meta value in postmeta table"; 
				exit(); 
			}
			
			// insert or update "_menu_item_object_id" meta value
			$menu_item_object_id = $obj_id;
			
			$sql = "SELECT meta_id FROM " . self::$db_prefix . "postmeta WHERE post_id = $push_id AND meta_key = '_menu_item_object_id'";
			$res = self::$db_conn->queryArray( $sql );
			$meta_id = $res[0]["meta_id"];
			
			if ( $meta_id == "" ) {
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta ( `post_id`, `meta_key`, `meta_value` ) VALUES ( $push_id, '_menu_item_object_id', '" . $menu_item_object_id . "' )";
			} else {
				$sql = "UPDATE " . self::$db_prefix . "postmeta SET meta_value = '" . $menu_item_object_id . "' WHERE post_id = $push_id and meta_key = '_menu_item_object_id'";
			}
			
			$sql = $this->string_replace_url( $sql );
			$result = self::$db_conn->query( $sql );
			
			if ( !$result ) { 
				echo "Fail to store _menu_item_object_id meta value in postmeta table"; 
				exit(); 
			}
			
			// insert or update "_menu_item_target" meta value
			$menu_item_target = get_post_meta( $post_id, '_menu_item_target' );
			$menu_item_target = $menu_item_target[0];
			
			$sql = "SELECT meta_id FROM " . self::$db_prefix . "postmeta WHERE post_id = $push_id AND meta_key = '_menu_item_target'";
			$res = self::$db_conn->queryArray( $sql );
			
			$meta_id = $res[0]["meta_id"];
			if ( $meta_id == "" ) {
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta ( `post_id`, `meta_key`, `meta_value` ) VALUES ( $push_id, '_menu_item_target', '" . $menu_item_target . "' )";
			} else {
				$sql = "UPDATE " . self::$db_prefix . "postmeta SET meta_value = '" . $menu_item_target . "' WHERE post_id = $push_id AND meta_key = '_menu_item_target'";
			}
			
			$sql = $this->string_replace_url( $sql );
			$result = self::$db_conn->query( $sql );
			if ( !$result ) { 
				echo "Fail to store _menu_item_target meta value in postmeta table"; 
				exit(); 
			}
			
			// insert or update "_menu_item_type" meta value
			$menu_item_type = get_post_meta( $post_id, '_menu_item_type' );
			$menu_item_type = $menu_item_type[0];
			
			$sql = "SELECT meta_id FROM " . self::$db_prefix . "postmeta WHERE post_id = $push_id AND meta_key = '_menu_item_type'";
			$res = self::$db_conn->queryArray( $sql );
			
			$meta_id = $res[0]["meta_id"];
			if ( $meta_id == "" ) {
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta ( `post_id`, `meta_key`, `meta_value` ) VALUES ( $push_id, '_menu_item_type', '" . $menu_item_type . "' )";
			} else {
				$sql = "UPDATE " . self::$db_prefix . "postmeta SET meta_value = '" . $menu_item_type . "' WHERE post_id = $push_id AND meta_key = '_menu_item_type'";
			}
			
			$sql = $this->string_replace_url( $sql );
			$result = self::$db_conn->query( $sql );
			if ( !$result ) { 
				echo "Fail to store _menu_item_type value in postmeta table"; 
				exit(); 
			}
			
			// insert or update "_menu_item_url" meta value
			$menu_item_url = get_post_meta( $post_id, '_menu_item_url' );
			$menu_item_url = $menu_item_url[0];
			
			$sql = "SELECT meta_id FROM " . self::$db_prefix . "postmeta WHERE post_id = $push_id AND meta_key = '_menu_item_url'";
			$res = self::$db_conn->queryArray( $sql );
			
			$meta_id = $res[0]["meta_id"];
			if ( $meta_id == "" ) {
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta ( `post_id`, `meta_key`, `meta_value` ) VALUES ( $push_id, '_menu_item_url', '" . $menu_item_url . "' )";
			} else {
				$sql = "UPDATE " . self::$db_prefix . "postmeta SET meta_value = '" . $menu_item_url . "' WHERE post_id = $push_id AND meta_key = '_menu_item_url'";
			}
			
			$sql = $this->string_replace_url( $sql );
			$result = self::$db_conn->query( $sql );
			if ( !$result ) { 
				echo "Fail to store _menu_item_url meta value in postmeta table"; exit(); 
			}
			
			// insert or update _menu_item_xfn meta value
			$menu_item_xfn = get_post_meta( $post_id, '_menu_item_xfn' );
			$menu_item_xfn = $menu_item_xfn[0];
			
			$sql = "SELECT meta_id FROM " . self::$db_prefix . "postmeta WHERE post_id = $push_id AND meta_key = '_menu_item_xfn'";
			$res = self::$db_conn->queryArray( $sql );
			
			$meta_id = $res[0]["meta_id"];
			if ( $meta_id == "" ) {
				$sql = "INSERT INTO " . self::$db_prefix . "postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ( $push_id, '_menu_item_xfn', '" . $menu_item_xfn . "' )";
			} else {
				$sql = "UPDATE " . self::$db_prefix . "postmeta SET meta_value='" . $menu_item_xfn . "' WHERE post_id = $push_id AND meta_key = '_menu_item_xfn'";
			}
			
			$sql = $this->string_replace_url( $sql );
			$result = self::$db_conn->query( $sql );
			if ( !$result ) { 
				echo "Fail to store _menu_item_xfn meta value in postmeta table"; 
				exit(); 
			}
		}
		
		/** 
		 * copy the menu item
		 * @access 	public
		 * @params	menu item info
		 * @return 	void
		 */
		public function copy_menu_item( $menu, $term_id, $term_taxonomy_id ) {

			$post_id = $menu->ID;
			$menu_type = $menu->object;
			$cat_id = $menu->object_id;
			$parent_id = $menu->menu_item_parent;

			// get the term_taxonomy_id 
			// $sql = "SELECT * FROM " . self::$curr_dbhandler->prefix . "term_relationships WHERE object_id = $post_id";
			// $res = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			// $term_taxonomy_id = $term_id;

			if ( strtolower($menu_type) != "category" ) {
				// check if same post exist on destination database
				$push_id = $this->check_same_post( $post_id );
				
				if ( $post_id != $cat_id ) {
					$obj_id = $this->check_same_post( $cat_id );
				} else 
					$obj_id = $push_id;
					
				// copy menu meta
				$this->copy_menu_meta( $push_id, $obj_id, $parent_id, $post_id );
			} else {
				$sql = "SELECT name, slug, term_group FROM " . self::$curr_dbhandler->prefix . "terms WHERE term_id = $cat_id";
				$res = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
				
				$sql = "SELECT term_id FROM " . self::$db_prefix . "terms WHERE name = '" . $res[0]["name"] . "' AND slug = '" . $res[0]["slug"] . "'";
				$res_term = self::$db_conn->queryArray( $sql );
				
				$obj_id = $res_term[0]["term_id"];
				
				if ( $obj_id == "" ) {
					$sql = "INSERT INTO " . self::$db_prefix . "terms ( name, slug, term_group ) VALUES ('" . $res[0]["name"] . "', '" . $res[0]["slug"] . "', '" . $res[0]["term_group"] . "')";
					$sql = $this->string_replace_url( $sql );
					$result = self::$db_conn->query( $sql );
					
					if ( !$result ) { 
						echo "Fail to store menu term info in terms table"; 
						exit(); 
					}
					
					// get just created term_id
					$sql = "SELECT max(term_id) as maxId FROM " . self::$db_prefix . "terms";
					$res = self::$db_conn->queryArray( $sql );
					$obj_id = $res[0]["maxId"];
				}
				
				// check if same post exist on destination database
				$push_id = $this->check_same_post( $post_id );
				
				$this->copy_menu_meta( $push_id, $obj_id, $parent_id, $post_id );
			}
			
			// check if this menu item already registered to destination site
			$sql = "SELECT object_id FROM " . self::$db_prefix . "term_relationships WHERE term_taxonomy_id = '" . $term_taxonomy_id . "'"; // No try object_id where paragraph, I can't understand why it doesn't work by Jack 2015-1-21
			$res = self::$db_conn->queryRow( $sql );
			
			$flag_exist = 0;
			
			for ( $i = 0; $i < count( $res ); $i++ ) {
				if ( $push_id == $res[$i][0] ) {
					$flag_exist = 1;
				}
			}
			
			if ( $flag_exist == 0 ) {
				// register this menu Item to related nav_menu 
				$sql = "INSERT INTO " . self::$db_prefix . "term_relationships VALUES ('" . $push_id . "', '" . $term_taxonomy_id . "', 0)";
				$sql = $this->string_replace_url( $sql );
				$result = self::$db_conn->query( $sql );
				
				if ( !$result ) { 
					echo "Fail to store menu item in term_relationships table"; 
					exit(); 
				}
			}
			
			// copy all posts in category
			if (  strtolower($menu_type) == "category" ) {
				$args = array( 'posts_per_page' => -1, 'category' => $cat_id );
				$myposts = get_posts( $args );
				
				foreach ( $myposts as $key_post => $value_post ) {
					foreach ( $value_post as $key_final => $value_final ) {
						if ( $key_final == "ID" ) $cat_post_id = $value_final;
					}
					$this->copy_posts( $cat_post_id );
				}
			}
			
		}
		
		/**
		 * copy the menu
		 * @access 	public
		 * @params	menu name 
		 * @return 	void
		 */
		public function copy_menu( $menu_id ) {
			
			// get current site menu ID, Slug, Count 
			$sql = "SELECT * FROM " . self::$curr_dbhandler->prefix . "terms WHERE term_id = '" . $menu_id . "'";
			$arr = self::$curr_dbhandler->get_results( $sql, ARRAY_A );

			$menu_name = $arr[0]["name"];  
			$menu_slug = $arr[0]["slug"];
			$term_group = $arr[0]["term_group"];
			
			$sql = "SELECT count FROM " . self::$curr_dbhandler->prefix . "term_taxonomy WHERE term_id = " . $menu_id;
			$arr = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
			$menu_count = $arr[0]["count"];  // total menu numbers belong to this menu
			$menu_description = $arr[0]["description"];  // menu description
			$menu_parent = $arr[0]["parent"];  // menu parent 
			
			// check if the same menu exist in destination site
			$sql = "SELECT b.name, b.term_id, a.term_taxonomy_id FROM " . self::$db_prefix . "term_taxonomy a LEFT JOIN " . self::$db_prefix . "terms b ON a.term_id = b.term_id WHERE a.taxonomy = 'nav_menu' AND b.name = '" . $menu_name . "'";
			$res = self::$db_conn->queryArray( $sql );
			
			
			if ( $menu_name != $res[0]["name"] ) {  // the menu doesn't exist in destination site
		
				// copy new nav_menu to push site 
				
				// insert to terms table
				$sql = "INSERT INTO " . self::$db_prefix . "terms ( name, slug, term_group ) VALUES ('" . $menu_name . "', '" . $menu_slug . "', '" . $term_group . "')";
				$sql = $this->string_replace_url( $sql );
				$result = self::$db_conn->query( $sql );
				$new_menu_id = self::$db_conn->getPrevInsertId();
				if ( !$result ) { 
					echo "Fail to store new nav menu in terms table"; 
					exit(); 
				}
				
				// insert to term_taxonomy table
				$sql = "INSERT INTO " . self::$db_prefix . "term_taxonomy ( term_id, taxonomy, description, parent, count ) VALUES ('" . $new_menu_id . "', 'nav_menu', '" . $menu_description . "', '" . $menu_parent . "', '" . $menu_count . "')";
				$sql = $this->string_replace_url( $sql );
				$result = self::$db_conn->query( $sql );
				$term_taxonomy_id = self::$db_conn->getPrevInsertId();
				if ( !$result ) { 
					echo "Fail to store nav menu meta in term_taxonomy table"; 
					exit(); 
				}
			}
			else {
				$new_menu_id = $res[0]["term_id"];
				$term_taxonomy_id = $res[0]["term_taxonomy_id"];
			}
			
			// overwrite the menu item to nav_menu 
			
			// get the menu items belong to this menu 
			$menu_list = wp_get_nav_menu_items( $menu_id ); 

			foreach ( $menu_list as $key => $menu ) {
				$this->copy_menu_item( $menu, $new_menu_id, $term_taxonomy_id );
			}
			
		}
		
		/**
		 * copy blox
		 * @access 	public
		 * @param	blox name
		 * @return 	void
		 */
		public function copy_blox( $blox_name ) {
			
			// check if xiblox_tabs table already exist on destination database
			$val = self::$db_conn->queryArray( 'SELECT 1 FROM xiblox_tabs' );
			
			if ( $val !== null ) {
			} else { // if not exist
				$sql = "show create table xiblox_tabs";
				$result = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
				
				$create_sql = $result[0]['Create Table'];
				self::$db_conn->query( $create_sql );
				
				echo "xiblox_tabs table is created.";
			}
			
			// get the selected blox content
			$sql = "SELECT * FROM xiblox_tabs WHERE blox_name = '$blox_name'";
			$res = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
			$blox_content = $res[0]["blox_content"];
			$blox_custom = $res[0]["blox_custom"];
			$status = $res[0]["status"];	
			$menu = $res[0]["menu"];	
			$admin_use = $res[0]["admin_use"];	
			$menu_name = $res[0]["menu_name"];	
			$parent_blox = $res[0]["parent_blox"];	
			$blox_editor = $res[0]["blox_editor"];	
			$modified_date = $res[0]["modified_date"];
			
			// create insert / update query
			$sql = "SELECT id FROM xiblox_tabs WHERE blox_name = '$blox_name'";
			$res = self::$db_conn->queryArray( $sql );
			
			if ( $res[0]["id"] == '' ) { // insert query
				$sql = "INSERT INTO xiblox_tabs ( blox_name, blox_content, blox_custom, status, menu, admin_use, menu_name, parent_blox, blox_editor, modified_date) VALUES ( '$blox_name', '$blox_content', '" . self::$db_conn->RES( $blox_custom ) . "', '$status', '$menu', '$admin_use', '$menu_name', '$parent_blox', '$blox_editor', '$modified_date' )";
			} else // update query
				$sql = "UPDATE xiblox_tabs SET blox_content = '$blox_content', blox_custom = '" . self::$db_conn->RES( $blox_custom ) . "', status = '$status', menu = '$menu', admin_use = '$admin_use', menu_name = '$menu_name', parent_blox = '$parent_blox', blox_editor = '$blox_editor', modified_date = '$modified_date' WHERE blox_name = '$blox_name'";
				
			$sql = $this->string_replace_url( $sql );
			$result = self::$db_conn->query( $sql );
			
			if ( !$result ) { 
				echo "Fail to store blox in xiblox_tabs table"; 
				exit(); 
			} else 
				echo "Copying Blox: " . $blox_name;
			
		}
		
		/**
		 * copy database table
		 * @access 	public
		 * @param	blox name
		 * @return 	void
		 */
		public function copy_table( $table_name ) {
		
			$baseurl = get_site_url();
			$tmp1url = str_replace( "www.", "", $baseurl );
			$tmp1url = str_replace( "http://", "", $tmp1url );
			
			$basepath = ABSPATH;
			
			$option_table = self::$db_prefix . "options"; 
			$post_table = self::$db_prefix . "posts";
			$postmeta_table = self::$db_prefix . "postmeta";
			
			// match the type of the destination url for converting string
			$f_des_url = str_replace( "http://", "", self::$destination_url );
			
			$delta_url = strlen( $f_des_url ) - strlen( $tmp1url );
			$delta_path = strlen( self::$destination_path ) - strlen( $basepath );
			
			
			// delete same table on destination database first
			self::$db_conn->query( 'DROP TABLE ' . $table_name );
			
			// create table
			$sql = "SHOW CREATE TABLE $table_name";
			$result = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			$create_sql = $result[0]['Create Table'];
			
			self::$db_conn->query( $create_sql );
			echo "Table $table_name created!<br>";
			
			// copy the content of the table
			$sql = "SELECT * FROM $table_name";
			
			// check if the table name includes '404' 
			if ( strpos( $table_name, "404" ) > 0 ) {

				// mysql exception process 
				$result = @mysql_query( $sql, self::$curr_dbhandler->dbh );
				
				if ( $result ) { // if current hosting use mysql
				
					$conn_local = mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, true );
					mysql_select_db( DB_NAME, $conn_local );
					mysql_set_charset( 'utf8', $conn_local );
					
					$res = mysql_query( $sql, $conn_local );
					$row = mysql_fetch_assoc( $res );
					
					while ( $row = mysql_fetch_assoc( $res ) ) {
					
						foreach ( $row as $key => $data )
							$row[$key] = addslashes($row[$key]);
							
						$tmp_field = explode( ",", implode( ", ", array_keys($row) ) );
						$field = "`" . $tmp_field[0] . "`";
						for ( $i = 1; $i < count( $tmp_field ); $i++ ) {
							$tmp_field[$i] = str_replace( " ", "", $tmp_field[$i] );
							$field .= ", `" . $tmp_field[$i] . "`";
						}
						
						$sql = "INSERT INTO $table_name (" . $field . ") VALUES ('" . implode("', '", array_values($row)) . "')";
						$sql = $this->string_replace_url( $sql );
						
						if ( ( $table_name == $user_table ) && ( $row['ID'] == 1 ) ){} 
						else 
							self::$db_conn->query( $sql ) or print( "copy 404 table error : " . $sql . "</br>");
						
					}
					
					@mysql_close( $conn_local );
				} else { // if current hosting use mysqli
					
					$result = self::$db_conn->query( $sqsl );
					
					while ( $row = mysqli_fetch_assoc( $result ) ) {
					
						foreach ( $row as $key => $data )
							$row[$key] = addslashes($row[$key]);

						$tmpField = explode(",", implode(", ", array_keys($row)));
						$field = "`" . $tmpField[0] . "`";
						for ( $i = 1; $i < count( $tmpField ); $i++ ) {
							$tmpField[$i] = str_replace(" ", "", $tmpField[$i]);
							$field .= ", `" . $tmpField[$i] . "`";
						}
						
						$sql = "INSERT INTO $table_name (" . $field . ") VALUES ('" . implode("', '", array_values($row)) . "')";
						$sql = $this->string_replace_url( $sql );
						if ( ( $table_name == $user_table ) && ( $row['ID'] == 1 ) ) {
						} else {
							self::$db_conn->query( $sql ) or print( "copy 404 table error : " . $sql . "</br>");
						}
					}
					@mysqli_close( $conn_local );
				}
				
				@mysqli_free_result( $result );
				
			} else {
				$conn_local = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
				mysqli_set_charset( $conn_local, 'utf8' );

				$result = @mysqli_query( $sql, self::$curr_dbhandler->dbh );
				
				if ( $result ) { // mysql connection
				
					while ( ( $row = @mysql_fetch_array( $result, MYSQL_ASSOC ) ) ) {
					
						if ( $table_name == $post_table ) 
							$row["post_content"] = $this->string_replace_url( $row["post_content"] );
							
						if ( $table_name == $option_table ) {
							
							// if option_value includes current site url
							if ( strpos( $row["option_value"], $tmp1url ) > 0 ) {
								
								if ($row["option_name"] == "siteurl" || $row["option_name"] == "homeurl") {
									$row["option_value"] = self::$destination_url;
								} else {
									$array = unserialize( $row["option_value"] );
									
									if ( is_array( $array ) ) {

										$array = $this->convert_array_url( $array );
										$row["option_value"] = serialize( $array );
										
									} else {
									
										$split_url = $tmp1url; // define the splitUrl as tmp1url
										$temp = explode( $split_url, $row["option_value"] ); // split the string 
										
										// split 
										for ( $k = 0; $k < ( count( $temp ) - 1 ); $k++ ) {
											$iTemp = explode( ":", $temp[$k] );
											$iTempCnt = count( $iTemp );
											
											// check the string has http or www 
											$add_step = 0; // to use for find the string length index.
											
											if ( strpos(" " . $iTemp[ $iTempCnt - 2 ], "http") > 0 ) 
												$add_step = 1;
											
											// check unconverted array
											if ( is_numeric($iTemp[ $iTempCnt - 2 - $add_step ]) ) {
											
												$iTemp[$iTempCnt - 2 - $add_step] = $iTemp[$iTempCnt - 2 - $add_step] + $delta_url;
												$buffer = "";
												
												// merge
												for ( $j = 0; $j < ( count( $iTemp ) - 1 ); $j++ ) {
													$buffer .= $iTemp[$j] . ":";
												}
												$temp[$k] = $buffer;
											}
										}
										
										$row["option_value"] = "";
										$last = count( $temp ) - 1;
										for ( $j = 0; $j < count( $temp ); $j++ ) {
											if ( $j != $last )
												$row["option_value"] .= $temp[$j] . $split_url; 
											else 
												$row["option_value"] .= $temp[$j];
										}
									}
								}
							}
							
							// if option_value includes current site path
							if ( strpos( $row["option_value"], $basepath ) > 0 ) {
							
								$array = unserialize( $row["option_value"] );
								
								if ( is_array( $array ) ) {
								
									$array = $this->convert_array_path( $array );
									$row["option_value"] = serialize( $array );
									
								} else {
								
									$splitPath = $basepath;
									$temp = explode( $splitPath, $row["option_value"] );
									
									// split
									for ( $j = 0; $j < ( count( $temp ) - 1 ); $j++ ) {
										$iTemp = explode( ":", $temp[$j] );
										$iTempCnt = count( $iTemp );
										$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta_path;
										
										$buffer = "";
										
										// merge
										for ( $k = 0; $k < ( count( $iTemp ) - 1 ); $k++ ) {
											$buffer .= $iTemp[$k] . ":";
										}
										$temp[$j] = $buffer;
									}
									
									$row["option_value"] = "";
									$last = count($temp) - 1;
									
									for ( $j = 0; $j < count( $temp ); $j++ ) {
										if ( $j != $last )
											$row["option_value"] .= $temp[$j] . $splitPath;
										else 
											$row["option_value"] .= $temp[$j];
									}
								}
							}
							
						}
						
						foreach ( $row as $key => $data ) {
						
							if ( is_null( $row[$key] ) ) 
								$row[$key] = null; 
							else 
								$row[$key]=addslashes( $row[$key] );
								
							if ( $table_name != $post_table && $table_name != $option_table ) {
								
								// if row field includes current site url
								if ( strpos( $row[$key], $tmp1url ) > 0 ) {
								
									$array = unserialize( $row[$key] );
									
									if ( is_array( $array ) ) {
									
										$array = $this->convert_array_url( $array );
										$row[$key] = serialize( $array );
										
									} else {
									
										$split_url = $tmp1url; // define the split_url as tmp1url
										$temp = explode( $split_url, $row[$key] ); // split the string 
										
										// split 
										for ( $k = 0; $k < ( count( $temp ) - 1 ); $k++ ) {
										
											$iTemp = explode( ":", $temp[$k] );
											$iTempCnt = count( $iTemp );
											
											// check the string has http or www
											$add_step = 0; // to use for find the string length index.
											
											if ( strpos( " " . $iTemp[$iTempCnt - 2], "http") > 0 ) 
												$add_step = 1;
											
											// check unconverted array
											if ( is_numeric( $iTemp[$iTempCnt - 2 - $add_step] ) ) {
											
												$iTemp[$iTempCnt - 2 - $add_step] = $iTemp[$iTempCnt - 2 - $add_step] + $delta_url;
												$buffer = "";
												
												// merge
												for ( $j = 0; $j < ( count( $iTemp ) - 1 ); $j++ ) {
													$buffer .= $iTemp[$j] . ":";
												}
												
												$temp[$k] = $buffer;
											}
										}
										
										$row[$key] = "";
										$last = count( $temp ) - 1;
										for ( $j = 0; $j < count( $temp ); $j++ ) {
											if ( $j != $last )
												$row[$key] .= $temp[$j] . $split_url; 
											else 
												$row[$key] .= $temp[$j];
										}
									}
								}
								
								// if row field includes current site path
								if ( strpos( $row[$key], $basepath ) > 0 ) {
								
									$array = unserialize( $row[$key] );
									
									if ( is_array( $array ) ) {
									
										$array = $this->convert_array_path( $array );
										$row[$key] = serialize( $array );
										
									} else {
									
										$split_path = $basepath;
										$temp = explode( $split_path, $row[$key] );
										
										// split
										for ( $j = 0; $j < ( count( $temp ) - 1); $j++ ) {
										
											$iTemp = explode( ":", $temp[$j] );
											$iTempCnt = count( $iTemp );
											$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta_path;
											
											$buffer = "";
											/************merge********************************/
											for ( $k = 0; $k < ( count( $iTemp ) - 1 ); $k++ ) {
												$buffer .= $iTemp[$k] . ":";
											}
											$temp[$j] = $buffer;
										}
										
										$row[$key] = "";
										$last = count( $temp ) - 1;
										
										for ( $j = 0; $j < count( $temp ); $j++ ) {
											if ( $j != $last )
												$row[$key] .= $temp[$j] . $split_path;
											else 
												$row[$key] .= $temp[$j];
										}
									}
								}
								
							}
						}
						
						// create sql query
						$tmp_field = explode( ",", implode( ", ", array_keys( $row ) ) );
						$field = "`" . $tmp_field[0] . "`";
						
						for ( $j = 1; $j < count( $tmp_field ); $j++ ) {
							$tmp_field[$j] = str_replace( " ", "", $tmp_field[$j] );
							$field .= ", `" . $tmp_field[$j] . "`";
						}
						
						$array_row = array_values( $row );
						
						if ( is_null( $array_row[0] ) ) 
							$value = "null";
						else 
							$value = "'" . $array_row[0] . "'";
							
						for ( $j = 1; $j < count( $array_row ); $j++ ) {
						
							if ( is_null( $array_row[$j] ) ) 
								$value .= ", null";
							else 
								$value .= ", '" . $array_row[$j] . "'";
								
						}
						
						$sql = "INSERT INTO $table_name (" . $field . ") VALUES (" . $value . ")";
						$sql = $this->string_replace_url( $sql );
						$sql = $this->string_replace_path( $sql );
						
						self::$db_conn->query( $sql ) or print( "insert table field error : " . $sql . "</br>" );
						
					}
					
					@mysql_free_result( $result );
					
				} else {
					// mysqli function 
					$result = @mysqli_query( self::$curr_dbhandler->dbh, $sql );
					
					if ( $result ) {
					
						while ( ( $row = @mysqli_fetch_array( $result, MYSQLI_ASSOC ) ) ) {
						
							if ( $table_name == $post_table ) {
								$row["post_content"] = $this->string_replace_url( $row["post_content"] );
							}
							
							if ( $table_name == $option_table ) {
								
								// if option value includes 
								if ( strpos( $row["option_value"], $tmp1url ) > 0 ) {
									if ($row["option_name"] == "siteurl") {
										$row["option_value"] = self::$destination_url;
									} else {
										$array = unserialize( $row["option_value"] );
										
										if ( is_array( $array ) ) {
										
											$array = $this->convert_array_url( $array );
											$row["option_value"] = serialize( $array );
											
										} else {
										
											if ( strpos( $row["option_value"], $tmp1url ) > 0 ) 
												$split_url = $tmp1url;
											else 
												$split_url = $baseurl;
												
											$temp = explode( $split_url, $row["option_value"] );
											// split
											for ( $k = 0; $k < ( count( $temp ) - 1 ); $k++ ) {
												
												if (strpos($temp[$k], ":") !== false) {
													$iTemp = explode( ":", $temp[$k] );
													$iTempCnt = count( $iTemp );
													$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta_url;
													
													$buffer = "";
													
													// merge
													for ( $j = 0; $j < ( count( $iTemp ) - 1 ); $j++ ) {
														$buffer .= $iTemp[$j] . ":";
													}
													
													$temp[$k] = $buffer;
												}
											}
											
											$row["option_value"] = "";
											$last = count( $temp ) - 1;
											
											for ( $j = 0; $j < count( $temp ); $j++ ) {
												if ( $j != $last )
													$row["option_value"] .= $temp[$j] . $split_url;
												else 
													$row["option_value"] .= $temp[$i];
											}
										}
									}
								}
								
								// if option value includes current site path
								if ( strpos( $row["option_value"], $basepath ) > 0 ) {
								
									$array = unserialize( $row["option_value"] );
									
									if ( is_array( $array ) ) {
									
										$array = $this->convert_array_path( $array );
										$row["option_value"] = serialize( $array );
										
									} else {
										
										$split_path = $basepath;
										$temp = explode( $split_path, $row["option_value"] );
										
										// split
										for ( $j = 0; $j < ( count( $temp ) - 1 ); $j++ ) {
										
											$iTemp = explode( ":", $temp[$j] );
											$iTempCnt = count( $iTemp );
											$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta_path;
											
											$buffer = "";
											
											// merge
											for ( $k = 0; $k < ( count( $iTemp ) - 1 ); $k++ ) {
												$buffer .= $iTemp[$k] . ":";
											}
											
											$temp[$j] = $buffer;
										}
										
										$row["option_value"] = "";
										$last = count($temp) - 1;
										
										for ( $j = 0; $j < count( $temp ); $j++ ) {
											if ( $j != $last )
												$row["option_value"] .= $temp[$j] . $split_path;
											else 
												$row["option_value"] .= $temp[$j];
										}
									}
								}
							}
							
							foreach ( $row as $key => $data ) {
							
								if ( is_null( $row[$key] ) ) 
									$row[$key] = null; 
								else 
									$row[$key]=addslashes( $row[$key] );
									
								if ( $table_name != $post_table && $table_name != $option_table ) {
									
									// if row field includes current site url
									if ( strpos( $row[$key], $tmp1url ) > 0 ) {
									
										$array = unserialize( $row[$key] );
										
										if ( is_array( $array ) ) {
										
											$array = $this->convert_array_url( $array );
											$row[$key] = serialize( $array );
											
										} else {
										
											if ( strpos( $row[$key], $tmp1url ) > 0 ) 
												$split_url = $tmp1url;
											else 
												$split_url = $baseurl;
												
											$temp = explode( $split_url, $row[$key] );
											
											// split
											for ( $j = 0; $j < ( count( $temp ) - 1 ); $j++ ) {
											
												$iTemp = explode( ":", $temp[$j] );
												$iTempCnt = count( $iTemp );
												$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta_url;
												
												$buffer = "";
												
												// merge
												for ( $k = 0; $k < ( count( $iTemp ) - 1 ); $k++ ) {
													$buffer .= $iTemp[$k] . ":";
												}
												
												$temp[$j] = $buffer;
											}
											
											$row[$key] = "";
											$last = count( $temp ) - 1;
											for ( $j = 0; $j < count( $temp ); $j++ ) {
												if ( $j != $last )
													$row[$key] .= $temp[$j] . $split_url;
												else 
													$row[$key] .= $temp[$j];
											}
										}
									}
									
									// if row field includes current site path
									if ( strpos( $row[$key], $basepath ) > 0 ) {
									
										$array = unserialize( $row[$key] );
										
										if ( is_array( $array ) ) {
										
											$array = $this->convert_array_path( $array );
											$row[$key] = serialize( $array );
											
										} else {
										
											$split_path = $basepath;
											$temp = explode( $split_path, $row[$key] );
											
											// split
											for ( $j = 0; $j < ( count( $temp ) - 1); $j++ ) {
												$iTemp = explode( ":", $temp[$j] );
												$iTempCnt = count( $iTemp );
												$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta_path;
												
												$buffer = "";
												
												// merge
												for ( $k = 0; $k < ( count( $iTemp ) - 1 ); $k++ ) {
													$buffer .= $iTemp[$k] . ":";
												}
												
												$temp[$j] = $buffer;
											}
											
											$row[$key] = "";
											$last = count( $temp ) - 1;
											
											for ( $j = 0; $j < count( $temp ); $j++ ) {
												if ( $j != $last )
													$row[$key] .= $temp[$j] . $split_path;
												else 
													$row[$key] .= $temp[$j];
											}
										}
									}
									
								}
							}
							
							// create sql query
							$tmp_field = explode( ",", implode( ", ", array_keys( $row ) ) );
							$field = "`" . $tmp_field[0] . "`";
							
							for ( $j = 1; $j < count( $tmp_field ); $j++ ) {
							
								$tmp_field[$j] = str_replace( " ", "", $tmp_field[$j] );
								$field .= ", `" . $tmp_field[$j] . "`";
								
							}
							
							$array_row = array_values( $row );
							
							if ( is_null( $array_row[0] ) ) 
								$value = "null";
							else 
								$value = "'" . $array_row[0] . "'";
								
							for ( $j = 1; $j < count( $array_row ); $j++ ) {
							
								if ( is_null( $array_row[$j] ) ) 
									$value .= ", null";
								else 
									$value .= ", '" . $array_row[$j] . "'";
							}
							
							$sql = "INSERT INTO $table_name (" . $field . ") VALUES (" . $value . ")";
							$sql = $this->string_replace_url( $sql );
							$sql = $this->string_replace_path( $sql );
							
							self::$db_conn->query( $sql ) or print( "insert table content : " . $sql . "</br>" );
							
						}
						@mysqli_free_result( $result );
						
					} else {
					
						// exception process 
						$conn_local = new DatabaseManager('mysql', DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
						$res = $conn_local->queryArray( $sql );
						
						foreach( $res as $row ) {
							if ( $table_name == $post_table ) {
								$row["post_content"] = $this->string_replace_url( $row["post_content"] );
							}
							
							if ( $table_name == $option_table ) {
								
								// option value includes current site url
								if ( strpos( $row["option_value"], $tmp1url ) > 0 ) {
								
									$array = unserialize( $row["option_value"] );
									
									if ( is_array( $array ) ) {
									
										$array = $this->convert_array_url( $array );
										$row["option_value"] = serialize( $array );
										
									} else {
									
										if ( strpos( $row["option_value"], $tmp1url ) > 0 ) {
											$split_url = $tmp1url;
										} else 
											$split_url = $baseurl;
											
										$temp = explode( $split_url, $row["option_value"] );
										
										// split
										for ( $k = 0; $k < ( count( $temp ) - 1 ); $k++ ) {
										
											$iTemp = explode( ":", $temp[$k] );
											$iTempCnt = count( $iTemp );
											$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta;
											
											$buffer = "";
											
											// merge
											for ( $j = 0; $j < ( count( $iTemp ) - 1 ); $j++ ) {
												$buffer .= $iTemp[$j] . ":";
											}
											
											$temp[$k] = $buffer;
										}
										
										$row["option_value"] = "";
										$last = count( $temp ) - 1;
										
										for ( $j = 0; $j < count( $temp ); $j++ ) {
											if ( $j != $last )
												$row["option_value"] .= $temp[$j] . $split_url;
											else 
												$row["option_value"] .= $temp[$i];
										}
									}
								}
								
								// option value includes current site path
								if ( strpos( $row["option_value"], $basepath ) > 0 ) {
								
									$array = unserialize( $row["option_value"] );
									
									if ( is_array( $array ) ) {
									
										$array = $this->convert_array_path( $array );
										$row["option_value"] = serialize( $array );
										
									} else {
									
										$split_path = $basepath;
										$temp = explode( $split_path, $row["option_value"] );
										
										// split
										for ( $j = 0; $j < ( count( $temp ) - 1 ); $j++ ) {
										
											$iTemp = explode( ":", $temp[$j] );
											$iTempCnt = count( $iTemp );
											$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta_path;
											
											$buffer = "";
											
											// merge
											for ( $k = 0; $k < ( count( $iTemp ) - 1 ); $k++ ) {
												$buffer .= $iTemp[$k] . ":";
											}
											
											$temp[$j] = $buffer;
										}
										
										$row["option_value"] = "";
										$last = count($temp) - 1;
										
										for ( $j = 0; $j < count( $temp ); $j++ ) {
											if ( $j != $last )
												$row["option_value"] .= $temp[$j] . $split_path;
											else 
												$row["option_value"] .= $temp[$j];
										}
									}
								}
							}
							
							
							foreach ( $row as $key => $data ) {
							
								if ( is_null( $row[$key] ) ) 
									$row[$key] = null; 
								else 
									$row[$key]=addslashes( $row[$key] );
									
								if ( $table_name != $post_table && $table_name != $option_table ) {
								
									if ( strpos( $row[$key], $tmp1url ) > 0 ) {
									
										$array = unserialize( $row[$key] );
										
										if ( is_array( $array ) ) {
										
											$array = $this->convert_array_url( $array );
											$row[$key] = serialize( $array );
											
										} else {
										
											if ( strpos( $row[$key], $tmp1url ) > 0 ) {
												$split_url = $tmp1url;
											} else 
												$split_url = $baseurl;
												
											$temp = explode( $split_url, $row[$key] );
											
											// split
											for ( $j = 0; $j < ( count( $temp ) - 1 ); $j++ ) {
											
												$iTemp = explode( ":", $temp[$j] );
												$iTempCnt = count( $iTemp );
												$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta;
												
												$buffer = "";
												
												// merge
												for ( $k = 0; $k < ( count( $iTemp ) - 1 ); $k++ ) {
													$buffer .= $iTemp[$k] . ":";
												}
												
												$temp[$j] = $buffer;
											}
											
											$row[$key] = "";
											$last = count( $temp ) - 1;
											
											for ( $j = 0; $j < count( $temp ); $j++ ) {
												if ( $j != $last )
													$row[$key] .= $temp[$j] . $split_url;
												else 
													$row[$key] .= $temp[$j];
											}
										}
									}
									
									// if row field includes current site path
									if ( strpos( $row[$key], $basepath ) > 0 ) {
									
										$array = unserialize( $row[$key] );
										
										if ( is_array( $array ) ) {
										
											$array = $this->convert_array_path( $array );
											$row[$key] = serialize( $array );
											
										} else {
										
											$split_path = $basepath;
											$temp = explode( $split_path, $row[$key] );
											
											// split
											for ( $j = 0; $j < ( count( $temp ) - 1); $j++ ) {
											
												$iTemp = explode( ":", $temp[$j] );
												$iTempCnt = count( $iTemp );
												$iTemp[$iTempCnt - 2] = $iTemp[$iTempCnt - 2] + $delta_path;
												
												$buffer = "";
												
												// merge
												for ( $k = 0; $k < ( count( $iTemp ) - 1 ); $k++ ) {
													$buffer .= $iTemp[$k] . ":";
												}
												
												$temp[$j] = $buffer;
											}
											
											$row[$key] = "";
											$last = count( $temp ) - 1;
											
											for ( $j = 0; $j < count( $temp ); $j++ ) {
												if ( $j != $last )
													$row[$key] .= $temp[$j] . $split_path;
												else 
													$row[$key] .= $temp[$j];
											}
											
										}
									}
								}
							}
							
							// create sql query
							$tmp_field = explode( ",", implode( ", ", array_keys( $row ) ) );
							$field = "`" . $tmp_field[0] . "`";
							
							for ( $j = 1; $j < count( $tmp_field ); $j++ ) {
								$tmp_field[$j] = str_replace( " ", "", $tmp_field[$j] );
								$field .= ", `" . $tmp_field[$j] . "`";
							}
							
							$array_row = array_values( $row );
							
							if ( is_null( $array_row[0] ) ) 
								$value = "null";
							else 
								$value = "'" . $array_row[0] . "'";
								
							for ( $j = 1; $j < count( $array_row ); $j++ ) {
								if ( is_null( $array_row[$j] ) ) 
									$value .= ", null";
								else 
									$value .= ", '" . $array_row[$j] . "'";
							}
							
							$sql = "INSERT INTO $table_name (" . $field . ") VALUES (" . $value . ")";
							$sql = $this->string_replace_url( $sql );
							$sql = $this->string_replace_path( $sql );
							
							self::$db_conn->query( $sql ) or print( "insert table content : " . $sql . "</br>" );
						}
					}
				}
			}
			
			// delete push blox from xiblox_tabs 
			if ( $table_name == 'xiblox_tabs' ) {
				$sql = "DELETE FROM xiblox_tabs WHERE blox_name = 'SitePush'";
				self::$db_conn->query( $sql );
			}
			
			// delete push site info from destination's xiblox_destination_info 
			if ( $table_name == 'xiblox_destination_info' ) {
				$sql = "DELETE FROM xiblox_destination_info WHERE destination_url = '" . self::$destination_url . "'";
				self::$db_conn->query( $sql );
			}	
				
		}
		
		/**
		 * copy the user
		 * @access	public
		 * @param	user id : $user_id
		 * @return	void
		 */
		public function copy_user( $user_id ) {

			if ($this->check_user( $user_id)) {
				return;
			}

			// get the current user info
			$sql = "SELECT * FROM " . self::$curr_dbhandler->base_prefix . "users WHERE ID = $user_id";
			$user_info = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
			// get the current user meta
			$sql = "SELECT * FROM " . self::$curr_dbhandler->base_prefix . "usermeta WHERE user_id = " . $user_info[0]['ID'];	
			$usermeta = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
			echo "Creating user " . $user_info[0]['user_login'];
			
			// create field key
			$cols = "user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name";
			
			// create field value
			$vals = sprintf( "'%s', '%s', '%s', '%s', '%s', '%s',
							'%s', '%s', '%s'", $user_info[0]['user_login'], $user_info[0]['user_pass'], $user_info[0]['user_nicename'], $user_info[0]['user_email'], $user_info[0]['user_url'], $user_info[0]['user_registered'], $user_info[0]['user_activation_key'], $user_info[0]['user_status'], $user_info[0]['display_name'] );
			
			// create new user to destination site
			$sql = "INSERT INTO " . self::$db_prefix . "users( " . $cols . " ) values ( " . $vals . " )";
			self::$db_conn->queryInsert( $sql );
			
			// get just created user id
			$new_id = self::$db_conn->getPrevInsertId();
			
			// copy user meta
			foreach ( $usermeta as $item ) {
			
				$metakey = $item['meta_key'];
				$metavalue = $item['meta_value'];
				
				$sql = "INSERT INTO " . self::$db_prefix . "usermeta ( user_id, meta_key, meta_value ) values ( " . $new_id . ", '" . $metakey . "','" . $metavalue . "')";
				$sql = $this->string_replace_url( $sql);
				
				self::$db_conn->queryInsert( $sql );
			}
		}
		
		/**
		 * copy the all posts ( including pages )
		 * @access	public
		 * @param	post id : $post_id
		 * @return	void
		 */
		public function copy_posts( $post_id , $media = false) {
			
			// get current post info
			$post = get_post( $post_id, ARRAY_A );
			$post_type = $post['post_type'];
			
			if ( $post['post_title'] != "" ) 
				echo "Copying post: " . $post['post_title'] . "<br />";
			
			$this->copy_user( $post['post_author'] );
			
			
			// check if this post exists on destination site
			$sql = "SELECT * FROM " . self::$db_prefix . "posts WHERE post_title = '" . self::$db_conn->Res( $post["post_title"] ) . "' AND post_name = '" . $post["post_name"] . "' AND post_type = '" . $post["post_type"] . "'";
			$res = self::$db_conn->queryArray( $sql );
			
			
			if ( $res[0]["ID"] != '' ) { // if this post exists on destination site, then update it
			
				// update the post
				$this->update_post( $post, $res[0]["ID"] );
				$des_post_id = $res[0]["ID"];
				
			} else { // if this post doesn't exist on destination site, then insert it
			
				// create new post	
				$des_post_id = $this->insert_post( $post );
				
			}
			
			// copy custom fields of this post 
			$this->copy_custom_field( $post_id, $des_post_id );
			
			// copy comments of this post 
			$this->copy_comment( $post_id, $des_post_id );
			
			// copy categories of this 
			$this->copy_category( $post_id, $des_post_id );
			
			// copy attachment if exists 
			$this->copy_attachment( $post_id , $des_post_id);
			
		}
		
		/**
		 * copy only posts
		 * @access	public
		 * @param	post id : $post_id
		 * @return	void
		 */
		public function copy_only_posts( $post_id ) {
			
			//get current post info
			$post = get_post( $post_id, ARRAY_A );
			echo "Page : " . $post["post_title"] . "</br>";
			
			// check that author exists on target blog
			$is_author = $this->check_user( $post['post_author'] );
			if ( $is_author == null ) {			
				$this->copy_user( $post['post_author'] );
			}
			
			// check if this post already exists on destination site
			$sql = "SELECT * FROM " . self::$db_prefix . "posts WHERE post_title = '" . $post["post_title"] . "' AND post_name = '" . $post["post_name"] . "' AND post_type = '" . $post["post_type"] . "'";
			$res = self::$db_conn->queryArray( $sql );
			
			if ( $res[0]["ID"] != "" ) { // if this post exists on destination site, then update it
				
				// update the post
				$this->update_post( $post, $res[0]["ID"] );
				$des_post_id = $res[0]["ID"];
				
				
			} else { // if this post doesn't exist on destination site, then insert it
				
				// create new post	
				$des_post_id = $this->insert_post( $post );
			}
			
			// copy custom fields of this post 
			$this->copy_custom_field( $post_id, $des_post_id );
			
			// copy comments of this post 
			$this->copy_comment( $post_id, $des_post_id );
			
			// copy categories of this 
			$this->copy_category( $post_id, $des_post_id );
			
			// copy attachment if exists 
			$this->copy_attachment( $post_id, $des_post_id );

			return $des_post_id;
			
		}
		
		/**
		 * copy links
		 * @access	public
		 * @param	post id
		 * @return	void
		 */
		public function copy_links( $post_id ) {
			
			// get all links of this post
			$sql = "SELECT * FROM " . self::$curr_dbhandler->base_prefix . "links WHERE link_id = " . $post_id . "";
			$links = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
			$link_url = $links[0]['link_url'];
			$link_name = $links[0]['link_name'];
			
			echo "Copying link : $link_name";
			
			// check this link exists on destination site
			$sql = "SELECT * FROM " . self::$db_prefix . "links WHERE link_url LIKE '" . $link_url . "' AND link_name LIKE '" . $link_name . "'";
			$res = self::$db_conn->queryRow( $sql );
			
			if ( count( $res ) > 0 ) { // if yes, update the link
			
				$link_id = $res[0][0];
				
				// create query
				$vals = sprintf( "link_url = '%s', link_name = '%s', link_image = '%s', link_target = '%s', link_description = '%s', link_visible = '%s', link_owner = '%s', link_rating = '%s', link_updated = '%s', link_rel = '%s', link_notes = '%s', link_rss = '%s'", self::$db_conn->Res( $links[0]['link_url'] ), self::$db_conn->Res( $links[0]['link_name'] ), self::$db_conn->Res( $links[0]['link_image'] ), self::$db_conn->Res( $links[0]['link_target'] ), self::$db_conn->Res( $links[0]['link_description'] ), $links[0]['link_visible'], $links[0]['link_owner'], $links[0]['link_rating'], $links[0]['link_updated'], self::$db_conn->Res( $links[0]['link_rel'] ), self::$db_conn->Res( $links[0]['link_notes'] ), self::$db_conn->Res( $links[0]['link_rss'] ) );
				
				$sql = "UPDATE " . self::$db_prefix . "links SET $vals WHERE link_id = $link_id";
				$sql = $this->string_replace_url( $sql );
				
				self::$db_conn->query( $sql );
				
				// copy categories 
				$this->copy_category( $post_id, $link_id );
				
			} else { // if no, insert the link
			
				$cols = "link_url, link_name,link_image, link_target, link_description, link_visible, link_owner, link_rating, link_updated, link_rel, link_notes, link_rss";
				$vals = sprintf( "'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
						self::$db_conn->Res( $links[0]['link_url'] ), self::$db_conn->Res( $links[0]['link_name'] ), self::$db_conn->Res( $links[0]['link_image'] ), self::$db_conn->Res( $links[0]['link_target'] ), self::$db_conn->Res( $links[0]['link_description'] ), $links[0]['link_visible'], $links[0]['link_owner'], $links[0]['link_rating'], $links[0]['link_updated'], self::$db_conn->Res( $links[0]['link_rel'] ), self::$db_conn->Res( $links[0]['link_notes'] ), self::$db_conn->Res( $links[0]['link_rss'] )
					);
					
				$sql = "INSERT INTO " . self::$db_prefix . "links (" . $cols . ") VALUES (" . $vals . ")";
				$sql = $this->string_replace_url( $sql );
				
				self::$db_conn->queryInsert( $sql ) or print("Failed Insert");
				$new_link_id = self::$db_conn->getPrevInsertId();
				
				//******************
				// copy categories *
				//******************
				$this->copy_category( $post_id, $new_link_id );
			}
			
		}
		
		/**
		 * delete all database tables
		 * @access 	public
		 * @return 	void
		 */		
		public function delete_all_posts() {
			
			// get all table name from current site
			$sql = "SHOW TABLES LIKE '%'";
			$results = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
			for ( $i = 0; $i < count($results); $i++ ) {
			
				foreach ( $results[$i] as $table_name ) {
					
					$check_table = $this->check_table( $table_name );
						
					if ( $check_table ) {
					
						echo "deleting content from : $table_name<br>";
						
						$sql = "DELETE FROM " . $table_name;
						self::$db_conn->query( $sql ) or print("failed to delete the content from $table_name table<br>");
						
					}
					
				}
			}
			
		}
		
		/**
		 * delete all database tables except option, user, usermeta table
		 * @access 	public
		 * @return 	void
		 */	
		public function delete_posts() {
		
			$option_table = self::$db_prefix . "options";
			$user_table = self::$db_prefix . "users";
			$meta_table = self::$db_prefix . "usermeta"; 
			
			// get all table name from current site
			$sql = "SHOW TABLES LIKE '%'";
			$results = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
			
			for ( $i = 0; $i < count($results); $i++ ) {
			
				foreach ( $results[$i] as $table_name ) {
				
					if ( ( $table_name != $option_table ) && ( $table_name != $meta_table ) && ( $table_name != $user_table ) ) {
						
						$check_table = $this->check_table( $table_name );
						
						if ( $check_table ) {
						
							echo "deleting content from : $table_name<br>";
							
							$sql = "DELETE FROM " . $table_name;
							self::$db_conn->query( $sql ) or print("Failed to delete the content from $table_name table.<br>");
							
						}
						
					}
					
				}
				
			}
			
		}
		
		/**
		 * delete directory
		 * @access 	public
		 * @param 	directory name $directory
		 * @return 	boolean
		 */	
		public function delete_dir( $index, $directory ) {
			
			if ( $index == 0 ) {
				echo "<span>Deleting $directory.</span><br>";
				$index ++;
			}
			
			if( !$dh = opendir( $directory ) ) {
				return false;
			}
			 
			while( $file = readdir( $dh ) ) {
			
				if ( $file == "." || $file == ".." ) {
					continue;
				}
				 
				if ( is_dir( $directory . "/" . $file ) ) {
					$this->delete_dir( $index, $directory . "/" . $file );
					continue;
				}
				 
				if ( is_file( $directory . "/" . $file ) ) {
					@unlink( $directory . "/" . $file );
				}
			}
			 
			closedir( $dh );
			 
			@rmdir( $directory );
			
		}
		
		/**
		 * copy theme
		 * @access 	public
		 * @param 	theme name $theme_name
		 * @return 	void
		 */	
		public function copy_theme( $theme_name ) {
		
			if ( empty( self::$ftp_host ) || empty( self::$ftp_user ) || empty( self::$ftp_password ) ) {
			
				if ( !empty( self::$destination_path ) ) {
				
					echo "Copying Theme $theme_name<br>";
					
					// check if this theme stored in xiblox_new_item
					$sql = "SELECT * FROM xiblox_new_item WHERE item_name = '" . $theme_name . "' and type = 'theme'";
					$res = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
					
					if ( $res[0]["item_name"] == "" ) 
						$res = self::$curr_dbhandler->insert( "xiblox_new_item", array( "item_name" => $theme_name, "type" => "theme"));
					
					$source = get_theme_root() . "/" . $theme_name;
					$destination_theme_path = self::$destination_path . "wp-content/themes/" . $theme_name;
					
					// delete destination directory first
					if ( is_dir( $destination_theme_path ) ) {
						$this->delete_dir( 0, $destination_theme_path );
					}
					
					$this->copy_directory( 0, $source, $destination_theme_path );
				}
			} else { // ftp connection
			
				if ( self::$ftp_ssl == 1 )
					$conn_id = ftp_ssl_connect( self::$ftp_host );
				else
					$conn_id = ftp_connect( self::$ftp_host );
					
				if ( $conn_id )
					$login_result = ftp_login( $conn_id, self::$ftp_user, self::$ftp_password );
					
				ftp_pasv( $conn_id, true );
				
				if ( ( !$conn_id ) || ( !$login_result ) ) 
					echo "<span class=\"error\">FTP Connect Error!</span>";
				else {
					echo "Copying Theme $theme_name<br>";
					
					// check if this theme stored in xiblox_new_item
					$sql = "SELECT * FROM xiblox_new_item WHERE item_name = '" . $theme_name . "' and type = 'theme'";
					$res = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
					
					if ( $res[0]["item_name"] == "" ) 
						$res = self::$curr_dbhandler->insert( "xiblox_new_item", array( "item_name" => $theme_name, "type" => "theme"));
					
					$source = get_theme_root() . "/" . $theme_name;
					$destination_theme_path = self::$destination_path . "wp-content/themes/" . $theme_name;
					
					$this->ftp_copy_all( $conn_id, $source, $destination_theme_path );
				}
				@ftp_close( $conn_id );
			}
			
		}
		
		/**
		 * copy plugin
		 * @access 	public
		 * @param 	plugin name $plugin_name
		 * @return 	void
		 */
		public function copy_plugin( $plugin_name ) {
			
			if ( empty( self::$ftp_host ) || empty( self::$ftp_user ) || empty( self::$ftp_password ) ) {
			
				if ( !empty( self::$destination_path ) ) {
				
					echo "Copying Plugin $plugin_name<br>";
					
					// check if this plugin stored in xiblox_new_item
					$sql = "SELECT * FROM xiblox_new_item WHERE item_name = '" . $plugin_name . "' and type = 'plugin'";
					$res = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
					
					if ( $res[0]["item_name"] == "" ) 
						$res = self::$curr_dbhandler->insert( "xiblox_new_item", array( "item_name" => $plugin_name, "type" => "plugin"));
																		
					$source = ABSPATH . "wp-content/plugins/" . $plugin_name;
					$destination_plugin_path = self::$destination_path . "wp-content/plugins/" . $plugin_name;
					
					// delete destination directory first
					if ( is_dir( $destination_plugin_path ) ) {
						$this->delete_dir( 0, $destination_plugin_path );
					}
					
					$this->copy_directory( 0, $source, $destination_plugin_path );
				}
			} else { // ftp connection
				if ( self::$ftp_ssl == 1 ) 
					$conn_id = ftp_ssl_connect( self::$ftp_host );
				else 
					$conn_id = ftp_connect( self::$ftp_host );
					
				if ( $conn_id ) 
					$login_result = ftp_login( $conn_id, self::$ftp_user, self::$ftp_password );
				
				if ( ( !$conn_id ) || ( !$login_result ) ) {
					echo "<span class=\"error\">FTP Connect Error!</span>";
				} else {
				
					ftp_pasv( $conn_id, true );
					
					echo "Copying Plugin $plugin_name";
					
					// check if this plugin stored in xiblox_new_item
					$sql = "SELECT * FROM xiblox_new_item WHERE item_name = '" . $plugin_name . "' and type = 'plugin'";
					$res = self::$curr_dbhandler->get_results( $sql, ARRAY_A );
					
					if ( $res[0]["item_name"] == "" ) 
						$res = self::$curr_dbhandler->insert( "xiblox_new_item", array( "item_name" => $plugin_name, "type" => "plugin"));
					
					$source = ABSPATH . "wp-content/plugins/" . $plugin_name;
					$destination_plugin_path = self::$destination_path . "wp-content/plugins/" . $plugin_name;
					
					$this->ftp_copy_all( $conn_id, $source, $destination_plugin_path );
				}
				@ftp_close( $conn_id );
			}
		}
		
		/**
		 * copy directory
		 * @access 	public
		 * @param 	root path, destination path
		 * @return 	void
		 */
		public function copy_directory( $index, $source, $dest_path ) {
		
			if ( is_dir( $source ) ) {
				echo $dest_path;
				if (!file_exists($dest_path)) {
					mkdir( $dest_path );
				}
				
				$hierarchy = scandir($source);
				
				foreach($hierarchy as $item) {
					if ( $item == '.' || $item == '..' ) 
						continue;
				
					$path_dir = $source . '/' . $item; 

					$index ++;
					$this->copy_directory( $index, $path_dir, $dest_path . '/' . $item );
					$index --;
					continue;

					if ( $index == 0 ) {
						echo "CurrentPath = " . $path_dir."<br>";
						echo "DestinationPath = " . $dest_path . '/' . $item."<br>";
					}
				}
			} else {
				copy( $source, $dest_path );
				echo "Copying " . $source;
			}
			
		}
		
		/**
		 * copy directory via ftp
		 * @access	public
		 * @return	void
		 */
		public function ftp_copy_all( $conn_id, $src_dir, $dst_dir ) {
		
			if ( is_dir( $dst_dir ) ) {
				return "<br> Directory <b> $dst_dir </b> Already exists  <br> ";
			} else {
				   $d = dir( $src_dir );
				   @ftp_mkdir( $conn_id, $dst_dir );  
				   
				   echo "<br>Creating dir <u> $dst_dir </u>";
				   
				   while ( $file = $d->read() ) { // do this for each file in the directory
				   
					  if ( $file != "." && $file != ".." ) { // to prevent an infinite loop
					  
						  if ( is_dir( $src_dir . "/" . $file ) ) { // do the following if it is a directory
						  
							  $this->ftp_copy_all( $conn_id, $src_dir . "/" . $file, $dst_dir . "/" . $file ); // recursive part
							  
						  } else {
						  
							$upload = @ftp_put( $conn_id, $dst_dir . "/" . $file, $src_dir . "/" . $file, FTP_BINARY ); // put the files
							echo "<br>Creating file <u>" . $dst_dir . "/" . $file . " </u>";
							
						  }
					  }
					ob_flush() ;
					sleep(1); 
				  }
				  $d->close();
			}
		}
		
		/**
		 * class destructor
		 */
		public function __destruct() {
			self::$db_conn = null;
		}
	}
?>