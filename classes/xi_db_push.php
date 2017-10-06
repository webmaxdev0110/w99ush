<?php
	
	/*********************************
	* XIBLOX Push Database Class     *
	*								 *
	* @class	xiblox_push			 *
	* @package	XIBLOX/classes		 *
	* @author	itabix				 *
	*********************************/
	
	class xiblox_db_push {
	
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
		}
		
		/**
		 * replace current site url to destination one
		 * @ access	public
		 * @ param 	string wanted to convert $string
		 * @ return string // converted url
		 */
		public function string_replace_url( $string ) {
			
			$baseurl = get_site_url();
			$destination = self::$destination_url;
			$string = str_replace( $baseurl, $destination, $string );
			return $string;
		}
		
		/**
		 * replace current site path to destination one
		 * @ access	public
		 * @ param 	string wanted to convert $string
		 * @ return string // converted path
		 */
		public function string_replace_path( $string ) {
			// $basepath = ABSPATH;
			// $string = str_replace( $basepath, self::$destination_path, $string );
			$baseurl = get_site_url();
			$string = str_replace( $baseurl, self::$destination_url, $string );
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
        
        public function truncate_table($table_name) {
			$dest_table_name = str_replace(self::$curr_dbhandler -> prefix, self::$db_prefix, $table_name);
			echo "Truncating table $dest_table_name.";
			
            $sql = "TRUNCATE `" . $dest_table_name . "`";
            $res = self::$db_conn->query($sql);
		}
		
		public function get_replace_fields($table_names) {
			$table_name = str_replace(self::$curr_dbhandler -> prefix, '', $table_names);
			$required_fields = array();
			switch($table_name) {
				case 'commentmeta':
					$required_fields = array('meta_value');
					break;
				case 'comments':
					$required_fields = array('comment_content');
					break;
				case 'links':
					$required_fields = array('link_description');
					break;
				case 'options':
					$required_fields = array('option_value');
					break;
				case 'postmeta':
					$required_fields = array('meta_value');
					break;
				case 'posts':
					$required_fields = array('post_content', 'post_excerpt', 'guid');
					break;
				case 'termmeta':
					$required_fields = array('meta_value');
					break;
				case 'terms':
					$required_fields = array();
					break;
				case 'term_relationships':
					$required_fields = array();
					break;
				case 'term_taxonomy':
					$required_fields = array();
					break;
				case 'usermeta':
					$required_fields = array('meta_value');
					break;
				case 'users':
					$required_fields = array();
					break;
			}
			return $required_fields;
		}

        public function migrate_table($table_name) {
			echo "Migrating table " . $table_name . ".";
			$required_fields = $this -> get_replace_fields($table_name);
			$dest_table_name = str_replace(self::$curr_dbhandler -> prefix, self::$db_prefix, $table_name);
			$this->check_db($table_name);
            $sql = "SHOW COLUMNS FROM `" . $table_name . "`";
            $columns = self::$curr_dbhandler -> get_results($sql, ARRAY_A);

            $sql = "SELECT * FROM `" . $table_name . "`";
            $rows = self::$curr_dbhandler -> get_results($sql, ARRAY_A);
            foreach ($rows as $row) {
				if (!empty($required_fields)) {
					foreach ($required_fields as $field) {
						if ($row[$field]) {
							//$row[$field] = $this -> string_replace_path($row[$field]);
							$row[$field] = $this -> string_replace_url($row[$field]);
						}
							
					}
				}
				$sql = "INSERT INTO `" . $dest_table_name . "` VALUES('" . implode("','", array_map(array(self::$db_conn, 'real_escape_string'), $row)) . "')";
                if (!$res = self::$db_conn->query($sql)) {
					printf("Sql Error: %s\n</br>", self::$db_conn->error());
				}
            }
            
		}
		
		public function check_db($table_name) {
			$dest_table_name = str_replace(self::$curr_dbhandler -> prefix, self::$db_prefix , $table_name);
			$sql = "SELECT 1 FROM `$dest_table_name` LIMIT 1";
			if (self::$db_conn->query($sql) === FALSE) {
				echo "Creating table $table_name</br>";
				$sql = "SHOW CREATE TABLE $table_name";
				$db = self::$curr_dbhandler -> get_results($sql, ARRAY_A);
				$table_sql = array_values($db[0]);
				$table_sql = $table_sql[1];
				$table_sql = str_replace($table_name, $dest_table_name, $table_sql);
				self::$db_conn->query($table_sql);
			}
			return false;
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
		 * class destructor
		 */
		public function __destruct() {
			self::$db_conn = null;
		}
	}
?>