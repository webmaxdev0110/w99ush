<?php
	
	/*********************************
	* XIBLOX Tags Class 			 *
	*								 *
	* @class	xiblox_tags			 *
	* @package	XIBLOX/classes		 *
	* @author	itabix				 *
	*********************************/
	
	require_once 'xi_ui_class.php';
	
	class xiblox_tags {
		/**
		 * @var database handler
		 */
		private static 	$dbhandler		= 	null;
		
		/**
		 * @var database name used in blox
		 */
		private static 	$databasename 	= 	null;
		
		/**
		 * @var database table name used in blox
		 */
		private static 	$tablename 		= 	null;
		
		/**
		 * @var database table field name used in blox
		 */
		private static	$fieldname		= 	null;
		
		/**
		 * @var converted content
		 */
		public static 	$content		= 	"";
		
		/**
		 * @var 'where' paragraph of sql query
		 */
		public static 	$where			= 	"";
		
		/**
		 * @var 'order' paragraph of sql query
		 */
		public static 	$order			=	"";
		
		/**
		 * @var 'limit' paragraph of sql query
		 */
		public static 	$limit			=	"";
		
		/**
		 * @var offset of pulled query array
		 */
		public static 	$index			= 	0;
		
		/**
		 * @var count of pulled query array
		 */
		public static	$prev_count		=	0;
		
		/**
		 * @var display limit of pulling query array
		 */
		public static 	$disp_limit		=	100;
		
		public function __construct( $db ) {
		
			// start the session_cache_expire
			@session_start();
			
			// set the database handler of this class as argument
			self::$dbhandler = $db;
		}
		
		public function set_theme( $themename ) {
		
			// set the xiblox theme name
			$this->template_id = $themename;
		}
		
		public function get_theme() {
		
			// return the xiblox theme name
			return $this->template_id;
		}
		
		public function set_tablename( $tbname ) {
		
			// set the table name used in this class as argument
			self::$tablename = $tbname;
		}
		
		public static function get_dbhandler() {
		
			// return the database handler of this class
			return self::$dbhandler;
		}
		
		public static function get_field_name() {
		
			// return the field name using in query
			if ( self::$fieldname != null ) 
				return self::$fieldname;
			else 
				return false;
		}
		
		/**
		 * set the field, prev_count variable used in this class
		 */
		public function set_field( $field_name ) {
			
			if ( self::$tablename != null ) {
			
				// set the field value used in this class
				self::$fieldname = $field_name;
				
				// retrieve the field
				$wpdb = self::$dbhandler;
				self::$limit = "limit 0 , { self::$disp_limit }";
				$sql  = "select {$field_name} from " . self::$tablename . " " . self::$where . self::$limit;
				$result = $wpdb->get_results( $sql, ARRAY_A );
				
				// set the prev_count value used in this class
				self::$prev_count = count( $result );
			}
		}
		
		/**
		 * set limit paragraph used in query
		 */
		public function set_range( $start, $limit ) {
			if ( is_numeric( $start ) && is_numeric( $limit ) ) {
				self::$limit = "limit {$start},{$limit} ";
			}
		}
		
		/**
		 * set where paragraph used in query
		 */
		public function set_condition( $fieldname, $value ) {
			self::$where = "where {$fieldname} like '" . $value . "' ";
		}
		
		/**
		 * return the table name
		 */
		public static function get_table_name() {
			if ( self::$tablename != null ) 
				return self::$tablename;
			else
				return false;
		}
		
		/**
		 * return the database name
		 */
		public static function get_db_name() {
			if ( $this->databasename != null ) 
				return $this->databasename;
			else
				return false;
		}
		
		/**
		 * retrieve the field data
		 */
		public function get_field( $field_name = null, $start = '', $count = 1 ) {
		
			if ( $field_name == null ) 
				$field_name = self::$fieldname;
				
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) && ( $field_name != null ) ) {
			
				if ( $start == '' ) 
					$start = self::$index;
					
				if ( $count == -1 ) 
					$count = self::$disp_limit; 
					
				$wpdb = self::$dbhandler;
				self::$limit = "limit {$start} , {$count}";
				$sql  = "select {$field_name} from " . self::$tablename . " " . self::$where . ' ' . self::$order . ' ' . self::$limit;
				$result = $wpdb->get_results( $sql, ARRAY_A );
				
				self::$prev_count ++;
				
				return $result;
			}
			else 
				return false;
		}
		
		/**
		 * retrieve the field by ID
		 */
		public function get_field_by_id( $field_arr, $primary_field, $id ) {
		
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) ) {
				$wpdb = self::$dbhandler;
				$sql  = "select " . implode( ',', $field_arr ) . " from " . self::$tablename . " where " . $primary_field . " = '" . $id . "'";
				
				return $wpdb->get_results( $sql, ARRAY_A );
			}
			else 
				return false;
		}
		
		/**
		 * retrieve the multiple field
		 */
		public function get_multi_fields( $field_name_arr, $start = '', $count = 1 ) {
		
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) && ( is_array( $field_name_arr ) ) ) {
			
				if ( $count == -1 ) 
					$count = self::$disp_limit; 
					
				$wpdb = self::$dbhandler;
				self::$limit = "limit {$start} , {$count}";
				$sql  = "select " . implode ( ',', $field_name_arr ) . " from " . self::$tablename . " " . self::$where . ' ' . self::$order . self::$limit;
				$result = $wpdb->get_results( $sql, ARRAY_A );
				
				self::$prev_count = count( $result );
				
				return $wpdb->get_results( $sql, ARRAY_A );
			}
			else 
				return false;
		}
		
		/**
		 * retrieve the filed by where paragraph
		 */
		public function search_field( $fieldname = null , $keyword ) {
		
			if ( $fieldname == null ) 
				$fieldname = self::$fieldname;
				
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) && ( $fieldname != null ) ) {
			
				$wpdb = self::$dbhandler;
				self::$where = " where " . $fieldname . " like '%" . $keyword . "%' ";	
				$sql  = "select {$fieldname} from " . self::$tablename . " " . self::$where . ' ' . self::$order;
				$result = $wpdb->get_results( $sql, ARRAY_A );
				
				// init the where paragraph
				self::$where = '';
				
				self::$prev_count = count( $result );
				
				return $result;
			}
			else 
				return false;
		}
		
		/**
		 * retrieve the multiple filed by where paragraph
		 */
		public function search_multi_fields( $field_name_arr , $keyword, $type ) {
		
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) && ( is_array( $field_name_arr ) ) ) {
			
				$wpdb = self::$dbhandler;
				$query = array();
				foreach ( $field_name_arr as $field_name ) {
					$query[] = "$field_name like '%" . $keyword . "%'";
				}
				self::$where = "where ";
				if ( $type == 'and' ) 
					self::$where .= implode( ' and ', $query );
				else if ( $type == 'or' ) {
					self::$where .= implode( ' or ', $query );
				}
				$sql  = "select " . implode( ',', $field_name_arr ) . " from " . self::$tablename . " " . self::$where . ' ' . self::$order;
				$result = $wpdb->get_results( $sql, ARRAY_A );
				
				// init the where paragraph
				self::$where = '';
				
				self::$prev_count = count( $result );
				return $result;
			}
			else 
				return false;
		}
		
		/**
		 * set the order used in this class
		 */
		public function sort_field( $field_name = null, $types='asc' ) {
		
			if ( $field_name == null ) 
				$field_name = self::$fieldname;
				
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) && ( $field_name != null ) ) {
				switch ( $types ) {
					case 'asc':
						$type = "asc";
						break;
					case 'desc':
						$type = "desc";
						break;
					default:
						$type = "asc";
						break;
				}
				self::$order = " order by `" . self::$tablename . "`.`" . $field_name . "` {$type} ";
			}
			else 
				return false;
		}
		
		/**
		 * set the display limit used in this class
		 */
		public function set_display_limit( $limit ) {
			self::$disp_limit = $limit;
		}
		
		/**
		 * return the count of retrieved field data
		 */
		public function get_num_rows() {
		
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) ) {
			
				if ( is_numeric( self::$prev_count ) ) 
					return self::$prev_count;
				else 
					return 0;
			}
			else 
				return false;
		}
		
		/**
		 * return the total count of retrieved data
		 */
		public function get_total_num_rows() {
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) ) {
				$wpdb = self::$dbhandler;
				$sql  = "select count(*) as count from " . self::$tablename;
				$result = $wpdb->get_results( $sql, ARRAY_A );
				$_SESSION['max'] = $result[0]['count'];
				
				return $result[0]['count'];
			}
			else 
				return false;
		}
		
		/**
		 * insert the field
		 */
		public function insert_field( $field_name, $value ) {
		
			if ( $field_name == null ) 
				$field_name = self::$fieldname;
				
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) && ( $field_name != null ) ) {
			
				$wpdb = self::$dbhandler;
				$wpdb->insert( self::$tablename, array( $field_name => $value ), array( '%s' ) );
				return $wpdb->insert_id;
			}
		}
		
		/**
		 * insert multiple field
		 */
		public function insert_multi_fields( $post_array ) {
		
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) && ( is_array( $post_array ) ) ) {
			
				$wpdb = self::$dbhandler;
				$key_array = array();
				$insert_array = array();
				foreach ( $post_array as $key => $value ) {
					if ( $key != '' ) {
						$key_array[] = $key;
						$insert_array[] = $value;
					}
				}
				$sql  = "insert into " . self::$tablename . "(" . implode( ',', $key_array ) . ") values('" . implode( "','", $insert_array ) . "')";
				$wpdb->query( $wpdb->prepare( $sql ) );
			}
		}
		
		/**
		 * update multiple fields
		 */
		public function update_multi_fields( $post_array, $primary_field, $id ) {
		
			if ( ( self::$dbhandler != null ) && ( self::$tablename != null ) && ( is_array( $post_array ) ) ) {
			
				$wpdb = self::$dbhandler;
				$key_array = array();
				$insert_array = array();
				$sql_arr = array();
				$sql = '';
				foreach ( $post_array as $key => $value ) {
					if ( $key != '' ) {
						$sql_element = '\'' . $key . '\' = \'' . $value . '\'';
						$sql_arr[] = $sql_element;
					}
				}
				$sql  = "update set " . implode( ',', $sql_arr ) . " where $primary_field = '" . $id . "'";
				$wpdb->query( $wpdb->prepare( $sql ) );
			}
		}
		
		/**
		 * convert the blox shortcode to php/html content in content/excerpt hooking
		 */
		public function mobilize( $content ) {
			
			// replace the blox shortcode
			$content = $this->replace_tag( $content );
			return $content;
		}
		
		/**
		 * convert the blox shortcode to php/html content in widget hooking
		 */
		public function mobilizeWidget( $content ) {
		
			// replace the blox shortcode
			$content = $this->replace_tag_widget( $content );
			return $content;
		}
		
		public function replace_tag_widget( $content ) {
		
			$vertical_bar = html_entity_decode('&#124;');
			
			// extract only blox ( remove vertical bar )
			@preg_match_all( '/\|\|(.*)\|\|/U', $content, $out, PREG_PATTERN_ORDER );
			
			if ( !empty( $out ) ) {
			
				$tags_arr = array();
				$tags_contents = array();

				foreach ( $out[1] as $blox_name ) {
					if ( strstr( $blox_name, '|') ) { //n parameter
						$params = @explode( '|', $blox_name );
						switch( count( $params ) ) {
							case 2: //2 params
								$start = '';
								$end = '';
								$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
								$tags_arr[] = $tag_name;
								$tags_contents[] = $this->get2paramtag( $params );
								break;
							case 3:	//3 params	
								$start = '';
								$end = '';
								$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
								$tags_arr[] = $tag_name;
								$tags_contents[] = $this->get3paramtag( $params );
								break;
						}
					} else {	// 1 parameter
						if ( strstr( $blox_name, ':' ) ) {
							$tags = @explode( ':', $blox_name );
							$real_blox_name = $tags[0];
							$version = $tags[1];
						} else {
							$real_blox_name = $blox_name;
							$version = '';
						}
						if ( in_array( $real_blox_name, array( 'loop', 'endloop', 'if_field', 'if_record', 'endif', 'end_data_field', 'countNumberStart' ) ) ) { //predefined blox
							switch ( $real_blox_name ) {	
								case 'loop' : {
									if ( $version == '' ) 
										$count_limit = self::$disp_limit;
									else {
										$count_limit = $version;
										self::$disp_limit = $version;
									}
									$out = '
									<?php
										$count_limit = \'' . $count_limit . '\';
										$rows = $this->tags_handler->get_total_num_rows();
										if ( $rows < $count_limit ) 
											$count_limit = $rows;
										$start_index = 0;
										if ( isset( $_GET[\'loop_no\'] ) && is_numeric( $_GET[\'loop_no\'] ) ) 
											$start_index = $_GET[\'loop_no\'];
										$disp_count = 0;
										for ( $looper = 0; $looper < $count_limit; $looper ++ ) {
											$disp_count++;
											xiblox_tags::$index = $looper + $start_index;
									?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'countNumberStart' : {
									$out = '
									<?php echo "1"; ?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}	
								break;
								case 'endloop' : {
									$out = '
									<?php }
										xiblox_tags::$prev_count = $disp_count;
										xiblox_tags::$index = 0;
									?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'if_field' : {
									switch( count( $tags ) ) {
										case 1 : {
											$out = '<?php { ?>';
										}
										break;
										case 2 : {
											$fieldname = $tags[1];
											if ( substr( $fieldname, 0, 1 ) == '-' ) {
												$fieldname = substr( $fieldname, 1 );
												$out = '
												<?php
													$if_result = $this->tags_handler->get_field( \'' . $fieldname . '\');
													if ( $if_result != false && count( $if_result ) != 0 ) {
												?>
												';
											}
											else {
												$out = '
												<?php
													$if_result = $this->tags_handler->get_field( \'' . $fieldname . '\');
													if ( $if_result == false || count( $if_result ) == 0 ) {
												?>
												';										
											}
										}
										break;
										case 3 : {
											$tablename = $tags[1];
											$fieldname = $tags[2];
											if ( substr( $fieldname, 0, 1 ) == '-' ) {
												$fieldname = substr( $fieldname, 1 );
												$out = '
												<?php
													$this->tags_handler->set_tablename( \'' . $fieldname . '\' );
													$if_result = $this->tags_handler->get_field( \'' . $fieldname . '\');
													if ( $if_result != false && count( $if_result ) != 0 ) {
												?>
												';
											}
											else {
												$out = '
												<?php
													$this->tags_handler->set_tablename( \'' . $fieldname . '\' );
													$if_result = $this->tags_handler->get_field( \'' . $fieldname . '\' );
													if ( $if_result == false || count( $if_result ) == 0 ) {
												?>
												';										
											}
										}
										break;
									}
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'if_record' : {
									$if_second_params = $tags[1];
									$per = substr( $if_second_params, 0, 1 );
									if ( $per == '%' ) {
										$num = substr( $if_second_params, 1 );
										if ( is_numeric( $num ) ) {
											$out = '
											<?php
												if( xiblox_tags::$index %' . $num . ' == 0 ) {
													$disp_count -= ' . ( $num - 1 ) . '
											?>
											';
										}
									}
									$per = substr( $if_second_params, 0, 2 );
									if ( $per == '-%' ) {
										$num = substr( $if_second_params, 2 );
										if ( is_numeric( $num ) ) {
											$out = '
											<?php
												if(xiblox_tags::$index %' . $num . ' != 0) {
													$disp_count -= 1;
											?>
											';
										}
									}
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'endif' : {
									$out = '
									<?php }
									?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'end_data_field' : {
									$out = '
									</form>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
							}
						}
						
						$sql = "SELECT * FROM xiblox_tabs WHERE status = 1 AND blox_name LIKE '" . trim( $real_blox_name ) . "' LIMIT 0,1";
						$results = self::$dbhandler->get_results( $sql, ARRAY_A );
						
						if ( count( $results ) != 0 ) {
							$data = $results[0];
							$replace_content = $this->generate_file( $data, $version );
							ob_start();
							$replace_content = str_replace( "\n", "", $replace_content );
							echo $replace_content;
							$out = ob_get_contents();
							ob_end_clean();
							$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
							$tags_arr[] = $tag_name;
							$tags_contents[] = $out;
						}
					}
				}
				$content = str_replace( $tags_arr, $tags_contents, $content );
			}
			
			return $content;
		}
		
		public function replace_tag( $content ) {
			
			$start = '';
			$end = '';
			$vertical_bar = html_entity_decode('&#124;');
			@preg_match_all( '/\|\|(.*)\|\|/U', $content, $out, PREG_PATTERN_ORDER );
			
			if ( !empty( $out ) ) {
				
				$tags_arr = array();
				$tags_contents = array();
				
				foreach ( $out[1] as $blox_name ) {
					if ( strstr( $blox_name, '|' ) ) { //n parameter
						$params = @explode( '|', $blox_name );
						switch ( count( $params ) ) {
							case 2 : //2 params
								$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
								$tags_arr[] = $tag_name;
								$tags_contents[] = $this->get2paramtag( $params );
								break;
							case 3:	//3 params
								$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
								$tags_arr[] = $tag_name;
								$tags_contents[] = $this->get3paramtag( $params );
								break;
						}
					} else {	// 1 parameter
						if ( strstr( $blox_name, ':' ) ) {
							$tags = @explode( ':', $blox_name );
							$real_blox_name = $tags[0];
							$version = $tags[1];
						} else {
							$real_blox_name = $blox_name;
							$version = '';
						}
						
						if ( in_array( $real_blox_name, array( 'loop', 'endloop', 'if_field', 'if_record', 'endif', 'end_data_field', 'countNumberStart', 'countNumber', 'countNumberEnd' ) ) ) { //predefined blox
							switch ( $real_blox_name ) {
								case 'countNumberStart' : {
									$out = '<?php
										$_SESSION["countNumber"] = 0; ?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}	
								break;	
								case 'countNumber' : {
									$out = '
									<?php 
										$_SESSION["countNumber"] ++;
										echo $_SESSION["countNumber"];
									?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}	
								break;	
								case 'countNumberEnd' : {
									$out = '
										<?php 
											$_SESSION["countNumber"] = 0; 
										?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}	
								break;	
								case 'loop' : {
									if ( $version == '' ) {
										$count_limit = self::$disp_limit;
									} else {
										$count_limit = $version;
										self::$disp_limit = $version;
									}
									$out = '
										<?php
											$count_limit = \'' . $count_limit . '\';
											$rows = $this->tags_handler->get_total_num_rows();
											if ( $rows < $count_limit ) {
												$count_limit = $rows;
											}
											$start_index = 0;
											if ( isset( $_GET[\'loop_no\'] ) && is_numeric( $_GET[\'loop_no\'] ) ) 
												$start_index = $_GET[\'loop_no\'];
											$disp_count = 0;
											for ( $looper = 0; $looper < $count_limit; $looper ++ ) {
												$disp_count++;
												xiblox_tags::$index = $looper + $start_index;
												if ( xiblox_tags::$index >= $rows ) {
													$disp_count--;
													break;
												}
										?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'endloop' : {
									$out = '
									<?php }
										xiblox_tags::$prev_count = $disp_count;
										xiblox_tags::$index = 0;
									?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'if_field' : {
									switch ( count( $tags ) ) {
										case 1 : {
											$out = '<?php { ?>';
										}
										break;
										case 2 : {
											$fieldname = $tags[1];
											if ( substr( $fieldname, 0, 1 ) == '-' ) {
												$fieldname = substr( $fieldname, 1 );
												$out = '
													<?php
														$if_result = $this->tags_handler->get_field(\'' . $fieldname . '\' );
														if ( $if_result != false && count( $if_result )!= 0 ) {
													?>
												';
											}
											else {
												$out = '
												<?php
													$if_result = $this->tags_handler->get_field( \'' . $fieldname . '\' );
													if ($if_result == false || count($if_result) == 0)
													{
												?>
												';										
											}
										}
										break;
										case 3 : {
											$tablename = $tags[1];
											$fieldname = $tags[2];
											if ( substr( $fieldname, 0, 1 ) == '-' ) {
												$fieldname = substr( $fieldname, 1 );
												$out = '
													<?php
														$this->tags_handler->set_tablename( \'' . $fieldname . '\' );
														$if_result = $this->tags_handler->get_field( \'' . $fieldname . '\' );
														if ( $if_result != false && count( $if_result ) != 0 ) {
													?>
												';
											}
											else {
												$out = '
													<?php
														$this->tags_handler->set_tablename( \'' . $fieldname . '\' );
														$if_result = $this->tags_handler->get_field( \'' . $fieldname . '\' );
														if ( $if_result == false || count( $if_result ) == 0 ) {
													?>
												';										
											}
										}
										break;
									}
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'if_record' : {
									$if_second_params = $tags[1];
									$per = substr( $if_second_params, 0, 1 );
									if ( $per == '%' ) {
										$num = substr( $if_second_params, 1 );
										if ( is_numeric( $num ) ) {
											$out = '
												<?php
													if ( xiblox_tags::$index %' . $num . ' == 0 ) {
														$disp_count -= ' . ( $num - 1 ) . '
												?>
											';
										}
									}
									$per = substr( $if_second_params, 0, 2 );
									if ( $per == '-%' ) {
										$num = substr( $if_second_params, 2 );
										if ( is_numeric( $num ) ) {
											$out = '
											<?php
												if ( xiblox_tags::$index %' . $num . ' != 0 ) {
													$disp_count -= 1;
											?>
											';
										}
									}
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'endif' : {
									$out = '
										<?php 
											}
										?>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
								case 'end_data_field' : {
									$out = '
										</form>
									';
									$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
									$tags_arr[] = $tag_name;
									$tags_contents[] = htmlentities( $out );
								}
								break;
							}
						}
						
						$sql = "SELECT * FROM xiblox_tabs WHERE status = 1 AND blox_name LIKE '" . trim( $real_blox_name ) . "' LIMIT 0,1";
						$results = self::$dbhandler->get_results( $sql, ARRAY_A );
						
						if ( count( $results ) != 0 ) {
							$data = $results[0];
							$replace_content = $this->generate_file( $data, $version );
							ob_start();
							echo $replace_content;
							$out = ob_get_contents();
							ob_end_clean();
							$tag_name = $start . $vertical_bar . $vertical_bar . $blox_name . $vertical_bar . $vertical_bar . $end;
							$tags_arr[] = $tag_name;
							$tags_contents[] = $out;
						}
					}
				}
				$content = str_replace( $tags_arr, $tags_contents, $content );
			}
			
			return $content; 
		}
		
		/**
		 * get the content from compiled cache file
		 */
		public function generate_file( $data, $version ) {
			$dir = ABSPATH . "wp-content/plugins/XIBLOX/compiled/";
			$id = $data['id'];
			
			// check the compiled file exist
			$sql = "SELECT * FROM xiblox_compiled_versions WHERE blox_id = $id";
			$dbhandle = self::$dbhandler;
			$results = $dbhandle->get_results( $sql, ARRAY_A );
			
			if ( count( $results ) != 0 ) { // exist compiled file
				if ( isset( $version ) && $version != "" ) {
					$sql = "SELECT * FROM xiblox_compiled_versions WHERE blox_id = $id AND id = $version LIMIT 0,1";
					$version_results = $dbhandle->get_results( $sql, ARRAY_A );
					
					if ( count( $version_results ) != 0 ) 
						$filename = $version_results[0]['filename'];
					else 
						$filename = $results[0]['filename'];
				} else {
					$filename = $results[0]['filename'];
				}
				
				// get the converted content from buffer memory
				ob_start();
				require( $dir . $filename );
				$content = ob_get_contents();
				ob_end_clean();
				
				$content = str_replace( '\n', '', $content );
				
				$content = $this->replace_tag( $content );
				return $content;
			} else 
				return '';
		}
		
		/** 
		 * convert blox tag to corresponding content
		 */
		public function get3paramtag( $params ) {
			$tag_name = $params[0];
			switch ( $tag_name ) {
				case 'set_data' : {
					$tablename = $params[1];
					$thirdargs = $params[2];
					$hasattribute = @explode( ':', $thirdargs );
					switch ( count( $hasattribute ) ) {
						case 1 :	{			//no attribute
							$fieldname = $thirdargs;
							$phpcode = '
								<?php
									$this->tags_handler->set_tablename( \'' . $tablename . '\');
									$this->tags_handler->set_field( \'' . $fieldname . '\');
								?>
							';
							return $phpcode;
						}
						break;
						case 2: {			//1 attribute	<||set_data|tablename|start:limit||>
							$fieldname = $hasattribute[0];
							$phpcode = '
								<?php
									$this->tags_handler->set_tablename( \'' . $tablename . '\' );
									$this->tags_handler->set_field( \'' . $fieldname . '\' );
								';
							$attribute = $hasattribute[1];
							$isrange = @explode( '-', $attribute );
							if ( count( $isrange ) == 2 ) {	//yes
								$start = $isrange[0];
								$limit = $isrange[1];
								$phpcode .= '
									$this->tags_handler->set_range( \'' . $start . '\',\'' . $limit . '\');
								';
							}
							$phpcode .= '?>';
							return $phpcode;
						}
						break;
					}
				}
				break;
				case 'search' : {
					$phpcode = '<?php
					';
					$fieldnames = $params[1];
					$keyword = $params[2];
					$fields = explode( ',', $fieldnames );
					if ( count( $fields ) == 1 ) {
						$fields = explode( '+', $fieldnames );
						if ( count( $fields ) == 1 ) {
							$phpcode .= '
								$field_name = \'' . $fieldnames . '\';
								$keyword = \'' . $keyword . '\';
								$return_value = $this->tags_handler->search_field( $field_name, $keyword );
								if ( $return_value == false ) 
									echo \'\';
								else {
									//**THEME**//
									$return_content = \'\';
									$return_content = \'
									<ul id="\' . $this->tags_handler->get_theme() . \'">
									\';
									foreach ( $return_value as $each_item ) {
										$return_content .= \'
										<li>\' . $each_item[$field_name] . \'</li>
										\';
									}
									$return_content .= \'
									</ul>
									\';
									//**END THEME**//
									echo $return_content;
								}
							';
						} else {
							$phpcode .= '
								$field_name_arr = array( \'' . implode( '\', \'', $fields ) . '\');
								$keyword = \'' . $keyword . '\';
								$return_value = $this->tags_handler->search_multi_fields( $field_name_arr, $keyword, \'and\' );
								if ( $return_value == false )
									echo \'\';
								else {
									//**THEME**//
									$return_content = \'\';
									$return_content = \'
									<ul id="\' . $this->tags_handler->get_theme() . \'">
									\';
									foreach ( $return_value as $each_item ) {
										$row = \'<ul id="\' . $this->tags_handler->get_theme() . \'_row">\';
										foreach ( $each_item as $item ) {
											$row .= \'
												<li>\' . $item . \'</li>
											\';
										}
										$row .= \'</ul>\';
										$return_content .= \'
										<li>\' . $row . \'</li>
										\';
									}
									$return_content .= \'
									</ul>\';
									//**END THEME**//
									echo $return_content;
								}
							';
						}
					} else {
						$phpcode .= '
							$field_name_arr = array(\'' . implode( '\', \'', $fields ) . '\');
							$keyword = \'' . $keyword . '\';
							$return_value = $this->tags_handler->search_multi_fields( $field_name_arr, $keyword, \'or\' );
							if ( $return_value == false )
								echo \'\';
							else {
								//**THEME**//
								$return_content = \'\';
								$return_content = \'
								<ul id="\' . $this->tags_handler->get_theme() . \'">
								\';
								foreach ( $return_value as $each_item ) {
									$row = \'<ul id="\' . $this->tags_handler->get_theme() . \'_row">\';
									foreach ( $each_item as $item ) {
										$row .= \'
											<li>\' . $item . \'</li>
										\';
									}
									$row .= \'</ul>\';
									$return_content .= \'
									<li>\' . $row . \'</li>
									\';
								}
								$return_content .= \'
								</ul>\';
								//**END THEME**//
								echo $return_content;
							}
						';
					}
				}
				break;
			}
			$phpcode .= '
			?>';
			return $phpcode;
		}
		
		/** 
		 * convert blox tag to corresponding content
		 */
		public function get2paramtag( $params ) {
			$tag_name = $params[0];
			$args = $params[1];
			switch ( $tag_name ) {
				case 'tabs' : {
					$temp = explode( ";", $args );
					$array = explode( "database:", $temp[0] );
					$array1 = explode( ":", $array[1] );
					$connection_name = explode( ",", $array1[0] );
					$pubType = explode( ",", $array1[1] );
					
					$wpdb = self::$dbhandler;
					$sql = "SELECT count(id) as cnt FROM xiblox_destination_info";
					$result = $wpdb->get_results( $sql, ARRAY_A );
					
					$cnt = $result[0]["cnt"];
					$count = 0;
					$countE = 0;
					
					for ( $i = 0; $i < count( $connection_name ); $i++ ) {
						
						// get all connection
						$sql = "SELECT id FROM xiblox_destination_info WHERE connection_name = '" . $connection_name[$i] . "'";
						$result = $wpdb->get_results( $sql, ARRAY_A );
						
						if ( $result[0]["id"] != '' ) {
							$number[$count] = $result[0]["id"];  // this variable is used in xi_publish.php
							$count ++;
						} else {
							$error[$countE] = $connection_name[$i]; // this variable is used in xi_publish.php
							$countE ++;
						}
					}
					if ( $countE == 0 ) 
						$error = "";
						
					/**
					 * Add Blox 
					 * @start
					 */
					for ( $i = 1; $i < count( $temp ); $i++ ) {
					
						$sql = "UPDATE xiblox_tabs SET menu = 4 WHERE blox_name = '" . $temp[$i] . "'";
						$res = $wpdb->query( $sql );
						
						$sql = "UPDATE xiblox_tabs SET modified_date = '" . $modified_date . "' WHERE id = $temp[$i]";
						$result = $wpdb->query( $sql );
					}
					/**
					 * Add Blox 
					 * @end
					 */
					
					require_once( ABSPATH . 'wp-content/plugins/XIBLOX/xi_publish.php' );
					
					return $php;
				}	
				break;
				
				
				case 'useApp': {
					// extract the app name and parameters
					$tempArray = explode( ":params[", $args );
					$appName = $tempArray[0];
					$tempParams = explode( "]", $tempArray[1] );
					$params = $tempParams[0];
					
					$wpdb = self::$dbhandler;
					$sql = "SELECT id FROM xiblox_tabs WHERE blox_name = '" . $appName . "'";
					$result = $wpdb->get_results( $sql, ARRAY_A );
					
					// get app blox Id
					$appId = $result[0]["id"];
					
					// get compiled app blox 
					$sql = "SELECT filename FROM xiblox_compiled_versions WHERE blox_id = '" . $appId . "'";
					$result = $wpdb->get_results( $sql, ARRAY_A );
					$appFile = $result[0]["filename"];
					
					if ( $appFile != '' ) {
						require( ABSPATH . 'wp-content/plugins/XIBLOX/compiled/' . $appFile );
						$php = $this->replace_tag( app( $params ) );
					} else 
						$php = "<h3>You have to compile '" . $appName . "' blox first.</h3>";
					return $php;
				}
				break;
				
				
				case 'ajaxUrl' : {
					// return ajax url used in app
					$wpdb = self::$dbhandler;
					$sql = "SELECT id FROM xiblox_tabs WHERE blox_name = '" . $args . "'";
					$result = $wpdb->get_results( $sql, ARRAY_A );
					
					$sql = "SELECT filename FROM xiblox_compiled_versions WHERE blox_id = '" . $result[0]["id"] . "'";
					$result = $wpdb->get_results( $sql, ARRAY_A );
					$ajaxUrl = $result[0]["filename"];
					
					return plugins_url() . "/XIBLOX/compiled/" . $ajaxUrl;
				}
				break;
				
				case 'AddScript' : {
					// return script url
					$wpdb = self::$dbhandler;
					$sql = "SELECT blox_custom FROM xiblox_tabs WHERE blox_name = '" . $args . "'";
					$result = $wpdb->get_results( $sql, ARRAY_A );
					
					return "<script>" . $result[0]["blox_custom"] . "</script>";
				}
				break;
				
				case 'AddStyle' : {
					// return ajax url used in app
					$wpdb = self::$dbhandler;
					$sql = "SELECT blox_custom FROM xiblox_tabs WHERE blox_name = '" . $args . "'";
					$result = $wpdb->get_results( $sql, ARRAY_A );
					return "<style>" . $result[0]["blox_custom"] . "</style>";
				}
				break;
				
				case 'add_style': {
					$php = '';
					$arg_variation = @explode( ':', $args );
					if ( count( $arg_variation ) == 2 ) {
						$classname = $arg_variation[0];
						$mini_blox = $arg_variation[1];
						$sql = "select * from xiblox_mini_blox where blox_name like '" . $mini_blox . "' limit 0,1";					
						$result = self::$dbhandler->get_results( $sql, ARRAY_A );
						if ( count( $result ) != 0 ) {
							$content = $result[0]['blox_content'];
							$php .= '
								<style>	.' . $classname . ' {' . htmlentities( $content ) . '}
								</style>
							';
						}
					}
					if ( count( $arg_variation ) == 1 ) {
						$mini_blox = $arg_variation[0];
						$sql = "select * from xiblox_mini_blox where blox_name like '" . $mini_blox . "' limit 0,1";					
						$result = self::$dbhandler->get_results( $sql, ARRAY_A );
						if ( count( $result ) != 0 ) {
							$content = stripslashes( html_entity_decode( $result[0]['blox_content'] ) );
							$php .= '
								<style>
								' . $content . '
								</style>
							';
						}
					}
					return $php;
				}
				break;
				
				case 'LoadBlox' : {
					// return blox url used in app
					$wpdb = self::$dbhandler;
					$sql = "SELECT blox_custom FROM xiblox_tabs WHERE blox_name = '" . $args . "'";
					$result = $wpdb->get_results( $sql, ARRAY_A );
					
					return $result[0]["blox_custom"];
				}
				break;
				
				case 'chartVote' : {
					$wpdb = self::$dbhandler;
					
					$fields = @explode( ',', $args );
					$table_name = xiblox_tags::get_table_name();
					
					$query = "SELECT post_id, SUM(value) AS cnt FROM " . $table_name . " GROUP BY post_id ORDER BY cnt";
					$results = $wpdb->get_results( $query, ARRAY_A );
					
					$command = "";
					$commandWrap = "";
					
					if ( $results ) {
					
						for ( $i = 0; $i < count( $results ); $i++ ) {
						
							$x[$i] = $results[$i]["cnt"];  // x axis position
							
							$postId = $results[$i]["post_id"];
							$sql = "SELECT post_title FROM " . $wpdb->prefix . "posts WHERE id = " . $postId;
							$results1 = $wpdb->get_results( $sql, ARRAY_A );
							
							$y[$i] = esc_html($results1[0]["post_title"]); // y axis position
							
							$command .= "[" . $x[$i] . ",'" . $y[$i] . "']";
							$commandWrap .= "[0,'" . $y[$i] . "']";
							
							if ( $i != ( count( $results ) - 1 ) ) {
								$command .= ", ";
								$commandWrap .= ", "; 
							}
						}
						
						$height = count( $results ) * 50;
						$height .= "px";
						
						// create javascript for chart
						$js_script = "	
									<script type='text/javascript'>
										jQuery(document).ready(function() {
										
											var plot2;
											var renderGraph = function() {
											
												plot2 = jQuery.jqplot('chart2', [[" . $command . "]], {
													seriesDefaults	: {
														renderer		:	jQuery.jqplot.BarRenderer,
														pointLabels		: 	{ 
															show			:	false, 
															location		:	'e', 
															edgeTolerance	: -15 
														},
														shadowAngle		:	135,
														rendererOptions	:	{
															barDirection	:	'horizontal'
														}
													},
													axes : {
														yaxis	:	{
															renderer	:	jQuery.jqplot.CategoryAxisRenderer,
															autoscale	: 	true
														}
													},
													resetAxes : {
														yaxis	:	true
													}
												});	
												
												jQuery('.jqplot-yaxis').find('div').each(function() {
													jQuery(this).css('max-width', '100%');
													jQuery(this).css('word-wrap', 'break-word');
													jQuery(this).css('line-height', '10px');
												});
												
											}
											
											renderGraph();
											
											var resizeGraph = function() {
												if ( plot2 )
													plot2.destroy();
												renderGraph();
											}
											
											jQuery(window).resize(function() {
												resizeGraph();
											})
										});
								  </script>";
								  
						$phpcode = $js_script . '
							<?php';
						$phpcode .= '
								$return_content = \'<div id="chart2" style="height:' . $height . '"></div>\';
								echo $return_content;
							';
						$phpcode .= '
						?>';
					} else 
						$phpcode = "<?php echo 'There are no any data.'; ?>";
					
					return $phpcode;
				}
				break;
				
				
				case 'chart' : {
				
					$arg_variation = @explode( ':', $args );
					$fields = @explode( ',', $arg_variation[0] );
					
					if ( count( $fields ) == 1 ) {
					
						if ( isset( $arg_variation[1] ) ) { 
							$num_rows = intval( $arg_variation[1] );
							if ( !is_numeric( $num_rows ) )
								$num_rows = 0;
						}
						
						if ( isset( $arg_variation[2] ) ) 
							$axis_label = @explode( ',', $arg_variation[2] ); // axis label
							
						if ( isset( $arg_variation[3] ) ) 
							$line_style = @explode( ',', $arg_variation[3] );
							
						require_once "../includes/xi_chart.php";
						
					} else {
						
						if ( isset( $arg_variation[2] ) ) {
						
							$num_rows = intval( $arg_variation[2] );
							
							if ( !is_numeric( $num_rows ) || $num_rows == 0 ) {
								$num_rows = 10;
							}
						}
						
						if ( isset( $arg_variation[3] ) ) {
							$axis_label = @explode( ',', $arg_variation[3] ); // axis labeloption
						}
						
						if ( isset( $arg_variation[3] ) ) {
							$temp = @explode( ',', $arg_variation[0] );
							$fieldstemp[0] = $temp[0];
							$line_style[0] = $temp[1]; // marker option ( such as circle, diamond, square, filledCircle )
							$line_style[1] = $temp[2]; // line option
							$line_style[2] = $temp[3]; // color option
							
							$temp = @explode( ',', $arg_variation[1] );
							$fieldstemp[1] = $temp[0];
							$line_style[3] = $temp[1]; // second maker option
							$line_style[4] = $temp[2]; // second line option
							$line_style[5] = $temp[3]; // second color option
							
							$str = '';
							$str = $fieldstemp[0] . "," . $fieldstemp[1];
							
							$fields = @explode( ',', $str );
						}
						
						$GLOBALS['disp_limit'] = $num_rows;
						
						require_once ABSPATH . "wp-content/plugins/XIBLOX/includes/xi_chart.php";
						
					}
					
					return $phpcode;
				}
				break;
				
				
				case 'datatable' : {
					
					$arg_variation = @explode(':', $args);
					
					if ( count( $arg_variation ) >= 1 ) {
					
						$field_name = $arg_variation[0];
						$primary_field = $arg_variation[1];
						
						if ( $primary_field == '' ) 
							$primary_field = 'id';
							
						$options = $arg_variation[2];
						$options_arr = @explode( ',', $options );
						$field_names = @explode( ',', $field_name );
						
						$start = 0;
						$count = self::$disp_limit;
						
						if ( count( $field_names ) == 1 ) 
							require_once ABSPATH / "wp-content/plugins/XIBLOX/includes/xi_datatable_field.php";
						else 
							require_once ABSPATH . "wp-content/plugins/XIBLOX/includes/xi_datatable_fields.php";
					}
					return $phpcode;
				}
				break;
				
				
				case 'var_field': {
				
					$php = '';
					$arg_variation = @explode( ':', $args );
					
					if ( count( $arg_variation ) == 2 ) {
						$type = $arg_variation[0];
						$name = $arg_variation[1];
						$php = '<?php 
							$type = \'' . $type . '\';
							$name = \'' . $name . '\';
							if ( $type == \'select\' ) 
								echo xiblox_ui::gen_select( $type, $name );
							else 
								echo xiblox_ui::gen_input( $type, $name );
						?>';
					} else if ( count( $arg_variation ) == 3 ) {
					
						$type = $arg_variation[0];
						$name = $arg_variation[1];
						$label = $arg_variation[2];
						$php = '<?php 
							$type = \'' . $type . '\';
							$name = \'' . $name . '\';
							$label = \'' . $label . '\';
							if ( ( $type == \'submit\' ) || ( $type == \'button\' ) ) 
								echo xiblox_ui::gen_input( $type, $name, $label );
							else {
								if ( $type == \'select\' ) 
									echo xiblox_ui::gen_labeled_ctrl( $label, xiblox_ui::gen_select( $type, $name ) );
								else 
									echo xiblox_ui::gen_labeled_ctrl( $label, xiblox_ui::gen_input( $type, $name ) );
							}
						?>';
					}
					return $php;
				}
				break;
				
				
				case 'data_field': {
				
					$php = '<?php
					';
					$arg_variation = @explode( ':', $args );
					
					switch( count( $arg_variation ) ) {
						case 1 : {
							$field_name = $arg_variation[0];
							$php .= '
								$this->tags_handler->set_tablename( \'' . $field_name . '\' );
								
								if ( isset( $_POST[\'xiblox\'] ) && ( !empty( $_POST[\'xiblox\'] ) ) ) {
									if ( isset( $_POST[\'xiblox\'][\'update\'] ) && ( $_POST[\'xiblox\'][\'update\'] != \'\' ) ) 
										$this->tags_handler->update_multi_fields( $_POST[\'xiblox\'], $primary_field, $id );
									else 
										$this->tags_handler->insert_multi_fields( $_POST[\'xiblox\'] );
									}
								}
							';
						}
						break;
						case 2 : {
							$table_name = $arg_variation[0];
							$field_name = $arg_variation[1];
							$php .= '
								$this->tags_handler->set_tablename( \'' . $table_name . '\' );
								$this->tags_handler->set_field( \'' . $field_name . '\' );
								if ( isset( $_POST[\'xiblox\'] ) && ( !empty($_POST[\'xiblox\'] ) ) ) {
									if ( isset( $_POST[\'xiblox\'][\'update\'] ) && ( $_POST[\'xiblox\'][\'update\'] != \'\' ) ) 
										$this->tags_handler->update_multi_fields( $_POST[\'xiblox\'] );
									else 
										$this->tags_handler->insert_multi_fields( $_POST[\'xiblox\'] );
								}
							';
						}
						break;
						case 3 : {
							
						}
						break;
						case 4 : {
							
						}
						break;
					}
					$php .= ' ?>
						<form action="<?php echo $_SERVER[\'REQUEST_URI\']; ?>" method="post">
					';
					return $php;
				}
				break;
				
				
				case 'sort' : {
				
					$arg_variation = @explode( ':', $args );
					
					if ( count( $arg_variation ) == 2 ) {
						$fieldname = $arg_variation[0];
						$type = $arg_variation[1];
						$phpcode = '<?php
							$this->tags_handler->sort_field( \'' . $fieldname . '\',\'' . $type . '\');
						?>
						';
						return $phpcode;
					}
				}
				break;
				
				
				case 'set_data' : {
				
					$arg_variation = @explode( ':', $args );
					
					switch ( count( $arg_variation ) ) {
					
						case 1 : {
							$table_name = $arg_variation[0];
							
							$this->set_tablename( $table_name );
							$phpcode = '
								<?php
									$this->tags_handler->set_tablename( \'' . $table_name . '\' );
								?>
							';
							return $phpcode;
							break;
						}
						/*case 2: {
							$database_name	= $arg_variation[0];
							$table_name 	= $arg_variation[1];
							$this->tags_handler = new xiblox_tags($database_name,$table_name,$this->dbhandler);
							break;
						}*/
					}
				}
				break;
				
				
				case 'field': {
				
					$arg_variation = @explode( ':', $args );
					
					switch ( count( $arg_variation ) ) {
						case 2 : {
							$second_param = $arg_variation[1];
							$range_arr = @explode( '-', $second_param );
							
							if ( count( $range_arr ) == 1 ) {	//field|tablename:fieldname
							
								$tablename 	= $arg_variation[0];
								$field_name = $second_param;
								$phpcode = '
								<?php
									$this->tags_handler->set_tablename( \'' . $tablename . '\' );
								';
								//$this->set_tablename($tablename);
								$field_names = @explode( ',', $field_name );
								
								if ( count( $field_names ) == 1 ) {
									$phpcode .= '
										$return_value = $this->tags_handler->get_field( \'' . $field_name . '\' );
										if ( $return_value != false ) 
											echo $return_value[0][\'' . $field_name . '\'];
										else 
											echo \'\';
									';
								/*	$return_value = $this->get_field($field_name);
									if ($return_value != false){
										return $return_value[0][$field_name];
									}
									else {return '';}	*/
								} else {
									$phpcode .= '
										$return_value = $this->tags_handler->get_multi_fields( array( \'' . implode( '\', \'', $field_names ) . '\' ) );
										if ( $return_value != false ) {
											$row = $return_value[0];
											echo @implode( \', \', $row );
										}
										else 
											echo \'\';
									';
									/*$return_value = $this->get_multi_fields($field_names);
									if ($return_value != false){
										$row = $return_value[0];
										return @implode(',',$row);
									}
									else {return '';}	*/
								}
								$phpcode .= '
								?>';
							} else {							//field|fieldname:start-limit
								$field_name = $arg_variation[0];	
								$start = $range_arr[0];
								$count = $range_arr[1];
								if ( is_numeric( $start ) && is_numeric( $count ) ) {  //$count = -1 : to limit
									$start --;				//field|fieldname:1-limit
									if ( $start < 0 ) 
										$start = 0;
									$phpcode = '<?php
									';
									$field_names = @explode( ',', $field_name );
									if ( count( $field_names ) == 1 ) {
										$phpcode .= '
											$field_name = \'' . $field_name . '\';
											$start = \'' . $start . '\';
											$count = \'' . $count . '\';
											$return_value = $this->tags_handler->get_field( $field_name, $start, $count );
											if ( $return_value == false ) 
												echo \'\';
											else {
												//**THEME**//
												$return_content = \'\';
												$return_content = \'
												<ul id="\' . $this->tags_handler->get_theme() . \'">
												\';
												foreach ( $return_value as $each_item ) {
													$return_content .= \'
													<li>\' . $each_item[$field_name] . \'</li>
													\';
												}
												$return_content .= \'
												</ul>
												\';
												//**END THEME**//
												echo $return_content;
											}
										';
										/*$return_value = $this->get_field($field_name,$start,$count);
										if ($return_value == false){ return '';}
										else {
											
											$return_content = '';
											$return_content = '
											<ul id="xiblox_table_default_theme">
											';
											foreach ($return_value as $each_item){
												$return_content.= '
												<li>'.$each_item[$field_name].'</li>
												';
											}
											$return_content .= '
											</ul>
											';
											
											return $return_content;
										} */
									} else {
										$phpcode .= '
											$field_name = array(\'' . implode( '\', \'', $field_names ) . '\');
											$start = \'' . $start . '\';
											$count = \'' . $count . '\';
											$return_value = $this->tags_handler->get_multi_fields( $field_name, $start, $count );
											if ( $return_value == false )
												echo \'\';
											else {
												//**THEME**//
												$return_content = \'\';
												$return_content = \'
												<ul id="\' . $this->tags_handler->get_theme() . \'">
												\';
												foreach ( $return_value as $each_item ) {
													$row = \'<ul id="\' . $this->tags_handler->get_theme() . \'_row">\';
													foreach ( $each_item as $item ) {
														$row .= \'
															<li>\' . $item . \'</li>
														\';
													}
													$row .= \'</ul>\';
													$return_content .= \'
													<li>\' . $row . \'</li>
													\';
												}
												$return_content .= \'
												</ul>\';
												//**END THEME**//
												echo $return_content;
											}
										';
										/*$return_value = $this->get_multi_fields($field_names,$start,$count);
										if ($return_value == false){ return '';}
										else {
											
											$return_content = '';
											$return_content = '
											<ul id="xiblox_table_default_theme">
											';
											foreach ($return_value as $each_item){
												$row = '<ul id="xiblox_table_default_row">';
												foreach ($each_item as $item){
													$row.= '
														<li>'.$item.'</li>
													';
												}
												$row .= '</ul>';
												$return_content.= '
												<li>'.$row.'</li>
												';
											}
											$return_content .= '
											</ul>';
											
											return $return_content;
										}*/
									}
									$phpcode .= '
									?>';
								} else 
									return ''; 
							}
						}
						break;
					}
				}
				break;
				
				
				case 'paginate' : {
				
					$arg_variation = @explode( ':', $args );
					$label = $arg_variation[1];
					
					switch ( $arg_variation[0] ) {
					
						case 'first' : {
							$phpcode = '<?php
								if ( isset( $_GET[\'loop_no\'] ) ) {
									$req_arr = @explode( \'?\', $_SERVER[\'REQUEST_URI\'] );
									$uri = $req_arr[0].\'?\';
									foreach ( $_GET as $key => $request ) {
										if ( $key != \'loop_no\' ) 
											$uri .= $key . \'=\' . $request . \'&\';
									}
									if ( $_GET[\'loop_no\'] == 0 ) 
										echo "<a href=\'javascript:void(0)\' style=\'color:#999\'>' . $label . '</a>";
									else 
										echo "<a href=\'".$uri."\'>' . $label . '</a>";
								}
								else 
									echo "<a href=\'javascript:void(0)\' style=\'color:#999\'>' . $label . '</a>";
							?>';
						}
						break;
						
						
						case 'previous' : {
							$phpcode = '<?php
								if ( isset( $_GET[\'loop_no\'] ) ) {
									$req_arr = @explode( \'?\', $_SERVER[\'REQUEST_URI\'] );
									$uri = $req_arr[0] . \'?\';
									foreach ( $_GET as $key => $request ) {
										if ( $key != \'loop_no\' ) 
											$uri .= $key . \'=\' . $request . \'&\';
										else {
											$req_val = 0;
											if( ( $request - $GLOBALS["disp_limit"]) <=0 ) 
												$req_val = 0;
											else 
												$req_val = $request - $GLOBALS["disp_limit"];
											$uri .= $key . \'=\' . $req_val . \'&\';
										}
									}
									if ( $_GET[\'loop_no\'] == 0 ) 
										echo "<a href=\'javascript:void(0)\' style=\'color:#999\'>' . $label . '</a>";
									else 
										echo "<a href=\'".$uri."\'>' . $label . '</a>";
								} else 
									echo "<a href=\'javascript:void(0)\' style=\'color:#999\'>' . $label . '</a>";
							?>';
						}
						break;
						
						
						case 'next' : {
							$phpcode = '<?php
								if ( isset($_GET[\'loop_no\'] ) ) {
									$req_arr = @explode( \'?\', $_SERVER[\'REQUEST_URI\'] );
									$uri = $req_arr[0] . \'?\';
									foreach ( $_GET as $key => $request ) {
										if ( $key != \'loop_no\' )
											$uri .= $key . \'=\' . $request . \'&\';
										else 
											$uri .= $key . \'=\' . ( $request + $GLOBALS["disp_limit"]) . \'&\';
									}
									if ( ( $_GET[\'loop_no\'] + $GLOBALS["disp_limit"] ) >= xiblox_tags::get_total_num_rows() ) 
										echo "<a href=\'javascript:void(0)\' style=\'color:#999\'>' . $label . '</a>";
									else 
										echo "<a href=\'" . $uri . "\'>' . $label . '</a>";
								} else {
									if ( strstr( $_SERVER[\'REQUEST_URI\'], \'?\' ) ) 
										echo "<a href=\'" . $_SERVER[\'REQUEST_URI\'] . "&loop_no=" . $GLOBALS["disp_limit"] . "\'>' . $label . '</a>";
									else 
										echo "<a href=\'" . $_SERVER[\'REQUEST_URI\'] . "?loop_no=" . $GLOBALS["disp_limit"] . "\'>' . $label . '</a>";
								}
							?>';
						}
						break;
						
						
						case 'last' : {
							$phpcode = '<?php
								$tempUri = xiblox_tags::get_total_num_rows() %  $GLOBALS["disp_limit"];
								if ( isset( $_GET[\'loop_no\'] ) ) {
									$req_arr = @explode( \'?\', $_SERVER[\'REQUEST_URI\'] );
									$uri = $req_arr[0] . \'?\';
									foreach ( $_GET as $key => $request ) {
										if ( $key != \'loop_no\' ) 
											$uri .= $key . \'=\' . $request . \'&\';
										else 
											$uri .= $key . \'=\' . ( xiblox_tags::get_total_num_rows() - $tempUri ) . \'&\';
									}
									if ( ( $_GET[\'loop_no\'] + $GLOBALS["disp_limit"] ) >= xiblox_tags::get_total_num_rows() ) 
										echo "<a href=\'javascript:void(0)\' style=\'color:#999\'>' . $label . '</a>";
									else 
										echo "<a href=\'".$uri."\'>' . $label . '</a>";
								}
								else {
									if ( strstr( $_SERVER[\'REQUEST_URI\'], \'?\' ) ) {
										echo "<a href=\'" . $_SERVER[\'REQUEST_URI\'] . "&loop_no=" . ( xiblox_tags::get_total_num_rows() - $tempUri ) . "\'>' . $label . '</a>";
									}
									else {
										echo "<a href=\'" . $_SERVER[\'REQUEST_URI\'] . "?loop_no=" . ( xiblox_tags::get_total_num_rows() - $tempUri ) . "\'>' . $label . '</a>";
									}
								}
							?>';
						}
						break;
						
						
						case 'number' : {
						
							$first_no = $arg_variation[1];
							$last_no = $arg_variation[2];
							$delta_no = $last_no - $first_no;
							
							$currUrl = $_SERVER['REQUEST_URI'];
							
							$temp1 = strrpos( $currUrl, "loop_no" );
							$temp = @explode( "loop_no=", $currUrl );
							$ttemp = $_SESSION['max'] - $GLOBALS["disp_limit"];
							
							if ( $GLOBALS["disp_limit"] == 0  ) 
								$GLOBALS["disp_limit"] = 1;
								
							$calc = ( $temp[1] - $temp[1] % $GLOBALS["disp_limit"] + $GLOBALS["disp_limit"] ) / $GLOBALS["disp_limit"];
							
							if ( $temp[1] == $ttemp  ) {
								$last_no = '' ;
								$first_no = '';
								$_SESSION['first'] = $first_no;
								$_SESSION['last'] = $last_no;
							} else {
							
								if ( $temp1 == false ) {
									$first_no = $arg_variation[1];
									$last_no = $arg_variation[2];
									$_SESSION['first'] = $first_no;
									$_SESSION['last'] = $last_no;
								} else {
									$first_no = $calc - 5;
									if ( $first_no < 1 ) $first_no = 1;
									$last_no = $first_no + $delta_no;
									$testMax = $_SESSION['max'] +  $GLOBALS["disp_limit"];
									if ( ( ( $last_no - 1 ) * $GLOBALS["disp_limit"] ) > $testMax ) {
										$tempMax = $_SESSION['max'] / $GLOBALS["disp_limit"];
										$last_no = $tempMax + 1;
									} 
									$_SESSION['first'] = $first_no;
									$_SESSION['last'] = $last_no;
								}
								
								if ( is_numeric( $temp[1] ) ) {
								
									if ( $calc > $last_no ) {
										$first_no = $calc - 5;
										$last_no = $first_no + $delta_no;
										$testMax = $_SESSION['max'] +  $GLOBALS["disp_limit"];
										
										if ( ( ( $last_no - 1 ) * $GLOBALS["disp_limit"] ) > $testMax ) {
											$tempMax = $_SESSION['max'] / $GLOBALS["disp_limit"];
											$last_no = $tempMax + 1;
										} 	
									}
									
									if ( $calc < $first_no ) {
										$last_no = $calc + 5;
										$first_no = $last_no - $delta_no;
									}
									
									$_SESSION['first'] = $first_no;
									$_SESSION['last'] = $last_no;
								} else {
									$first_no = 1;
									$last_no = 1 + $delta_no;
								}
							}
							
							if ( is_numeric( $first_no ) && is_numeric( $last_no ) ) {
								$phpcode = '<?php
									if ( isset( $_GET[\'loop_no\'] ) ) {
										$req_arr = @explode( \'?\', $_SERVER[\'REQUEST_URI\'] );
										$uri = $req_arr[0] . \'?\';
										foreach ( $_GET as $key => $request ) {
											if ( $key != \'loop_no\' ) {
												$uri .= $key . \'=\' . $request . \'&\';
											}
										}
										for ( $mark = ' . $first_no . ';$mark<=' . $last_no . ';$mark++) {
											if ( ( $mark * $GLOBALS["disp_limit"] ) >= ( xiblox_tags::get_total_num_rows() + $GLOBALS["disp_limit"] ) ) 
												echo "  <a href=\'javascript:void(0)\' style=\'color:#999\'>" . $mark . "</a>  ";
											else {
												if ( $_GET[\'loop_no\'] == ( ( $mark - 1 ) * $GLOBALS["disp_limit"] ) ) 
													echo "  <a href=\'javascript:void(0)\' style=\'color:#999\'>" . $mark . "</a>  ";
												else 
													echo "  <a href=\'" . $uri . "loop_no=" . ( ( $mark - 1 ) * $GLOBALS["disp_limit"]) . "\'>" . $mark . "</a>  ";
											}
										}
									} else {
										if ( strstr( $_SERVER[\'REQUEST_URI\'], \'?\' ) ) {
											for ( $mark = ' . $first_no . ';$mark<=' . $last_no . ';$mark++ ) {
												if ( $mark == 1 ) 
													echo "  <a href=\'javascript:void(0)\' style=\'color:#999\'>" . $mark . "</a>  ";
												else 
													echo "  <a href=\'" . $_SERVER[\'REQUEST_URI\'] . "&loop_no=" . ( $GLOBALS["disp_limit"] * ( $mark - 1 ) ) . "\'>" . $mark . "</a>  ";
											}
										} else {
											if ( $mark == 1 )
												echo "  <a href=\'javascript:void(0)\' style=\'color:#999\'>" . $mark . "</a>  ";
											else {
												for( $mark = ' . $first_no . '; $mark <= ' . $last_no . '; $mark++ ) {
													if ( ( $mark * $GLOBALS["disp_limit"] ) >= ( xiblox_tags::get_total_num_rows() + $GLOBALS["disp_limit"] ) ) 
														echo "  <a href=\'javascript:void(0)\' style=\'color:#999\'>" . $mark . "</a>  ";
													else {
														if ( $_GET[\'loop_no\'] == ( ( $mark - 1 ) * $GLOBALS["disp_limit"] ) ) 
															echo "  <a href=\'javascript:void(0)\' style=\'color:#999\'>" . $mark . "</a>  ";
														else 
															echo "  <a href=\'" . $uri . "?loop_no=" . ( ( $mark - 1 ) * $GLOBALS["disp_limit"] ) . "\'>" . $mark . "</a>  ";
													}
												}
											}
										}
									}
								?>';
							}
						}
					}
					
					return $phpcode;
				}
				break;
				
				case 'data' : {
				
					$arg_variation = @explode( ':', $args );
					
					switch ( count( $arg_variation ) ) {
					
						case 1 : {
							$field_name = $arg_variation[0];
							
							switch ( $field_name ) {
							
								case 'number_displayed' : {
								
									$phpcode = '
										<?php
											if ( $this->tags_handler->get_num_rows() != false ) {
												$return_value = $this->tags_handler->get_num_rows();
												echo $return_value;
											} else 
												echo 0;
										?>
									';
									return $phpcode;
								}
								break;
								
								case 'number_total' : {
									$phpcode = '
									<?php
										if ( $this->tags_handler->get_total_num_rows() != false ) {
											$return_value = $this->tags_handler->get_total_num_rows();
											echo $return_value;
										} else 
											echo 0;
									?>';
									return $phpcode;
								}
								break;
								
								case 'show' : {
									$phpcode = '
										<?php
											$return_value = $this->tags_handler->get_field();
											if ( $return_value != false ) 
												echo $return_value[0][xiblox_tags::get_field_name()];
											else 
												echo \'\';
										?>
									';
									return $phpcode;
								}
								break;
								
								default: {
									$phpcode = '
									<?php
										$return_value = $this->tags_handler->get_field( \'' . $field_name . '\' );
										if ( $return_value != false ) 
											echo $return_value[0][\'' . $field_name . '\'];
										else 
											echo \'\';
									?>
									';
									return $phpcode;
								}
								break;
							}
						}
						break;
						
						
						case 2: {
							$second_param = $arg_variation[1];
							$range_arr = @explode( '-', $second_param );
							
							if ( count( $range_arr ) == 1 ) {	//data|tablename:fieldname
								$tablename 	= $arg_variation[0];
								$field_name = $second_param;
								
								$phpcode = '<?php';
								$phpcode .= '
									$tablename = \'' . $tablename . '\';
									$field_name = \'' . $field_name . '\';
									$this->tags_handler->set_tablename( $tablename ); 
								';
								
								$field_names = @explode( ',', $field_name );
								
								if ( count( $field_names ) == 1 ) {
									$phpcode .= '
										$return_value = $this->tags_handler->get_field( $field_name );
										if ( $return_value != false )
											echo $return_value[0][$field_name];
										else 
											echo \'\';
									';
								} else {
									$phpcode .= '
										$field_names = array( \'' . implode( '\', \'', $field_names ) . '\');
										$return_value = $this->tags_handler->get_multi_fields( $field_names );
										if ( $return_value != false ) {
											$row = $return_value[0];
											echo @implode( \',\', $row );
										}
										else 
											echo \'\';
									';
								}
								
								$phpcode .= ' ?>';
								
								return $phpcode;
							} else {	//data|fieldname:start-limit
							
								$field_name = $arg_variation[0];	
								$start = $range_arr[0];
								
								$count = $range_arr[1];
								
								if ( !is_numeric( $count ) ) {
								
									if ( strcmp( $count, 'end' ) == 0 ) 
										$count = $this->disp_limit;
										
								}
								
								if ( is_numeric( $start ) ) {  // ? $count == end : to limit
									$start --;				//data|fieldname:1-limit
								
									if ( $start < 0 ) 
										$start = 0;
										
									$phpcode = '<?php';
									$field_names = @explode( ',', $field_name );
									
									if ( count( $field_names ) == 1 ) {
										$phpcode .= '
											$field_name = \'' . $field_name . '\';
											$start = \'' . $start . '\';
											$count = \'' . $count . '\';
											$return_value = $this->tags_handler->get_field( $field_name, $start, $count );
											if ( $return_value == false )
												echo \'\';
											else {
												//**THEME**//
												$return_content = \'\';
												$return_content = \'
												<ul id="\' . $this->tags_handler->get_theme() . \'">
												\';
												foreach ( $return_value as $each_item ) {
													$return_content .= \'
													<li>\' . $each_item[$field_name] . \'</li>
													\';
												}
												$return_content .= \'
												</ul>
												\';
												//**END THEME**//
												echo $return_content;
											}
										';
									} else {
										$phpcode .= '
										$field_names = array(\'' . implode( '\', \'', $field_names ) . '\');
										$start = \'' . $start . '\';
										$count = \'' . $count . '\';
										$return_value = $this->tags_handler->get_multi_fields( $field_names, $start, $count );
										if ( $return_value == false )
											echo \'\';
										else {
											//**THEME**//
											$return_content = \'\';
											$return_content = \'
											<ul id="\' . $this->tags_handler->get_theme() . \'">
											\';
											foreach ( $return_value as $each_item ) {
												$row = \'<ul id="\' . $this->tags_handler->get_theme() . \'_row">\';
												foreach ( $each_item as $item ) {
													$row .= \'
														<li>\' . $item . \'</li>
													\';
												}
												$row .= \'</ul>\';
												$return_content .= \'
												<li>\' . $row . \'</li>
												\';
											}
											$return_content .= \'
											</ul>\';
											//**END THEME**//
											echo $return_content;
										}';
									}
									
									$phpcode .= ' ?>';
									
									return $phpcode;
								} else 
									return ''; 
							}
						}
						break;
						
					}
				}
				break;
			
			}
			
			return '';
		}
	}
?>