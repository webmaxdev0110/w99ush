<?php
	
	/*************************************
	** XIBLOX database connection Class **
	**								    **
	** @class	DatabaseManager		    **
	** @package	XIBLOX/classes		    **
	** @author	itabix				    **
	*************************************/
	
	
	class DatabaseManager {
		private     $my_db;             // for Any DB
		private     $my_dbname;         // connected database name
		private     $my_prevInsertId;

		function __construct() {
			if ( func_num_args() < 5 ) {
				echo "Error : </br>";
				echo "Usage cv_dbmanager </br>";
				echo "   cv_dbmanager(kindOfDatabase, hostname, userid, userpassword, databasename);  <br/>";
				$this->$my_db = NULL;
				return;
			}

			$args = func_get_args();
			if ( $args[0] == 'mysql' ) {
				$this->my_db = new mysqli($args[1], $args[2], $args[3], $args[4]);
				if ( $this->my_db->connect_error ) {
					echo '<br>Error : Could not connect to database. Please put the info exactly or try again later : [' . $this->my_db->connect_error . ']';
					$this->my_db = NULL;
					exit;
				}

				$this->my_dbname = 'mysql';

			$this->my_db->set_charset('utf8');
			}

			//echo 'Success... ' . $mysqli->host_info . "\n";
		}

		function __destruct() {
			if ( $this->my_db != NULL) {
				$this->close();
			}
		}

		public function error() {
			return $this->my_db->error;
		}

		public function close() {
			if ($this->my_dbname == 'mysql') {
				$this->my_db->close();
				$this->my_db = NULL;
			}
		}

		public function real_escape_string($string) {
			return $this->my_db->real_escape_string($string);
		}

		public function query( $query ) {
			if ( $this->my_db != NULL )
				return $this->my_db->query( $query );
			else {
				echo "Error : Cannot connect to database";
				exit;
			}
		}

		public function queryArray( $query ){
			if ( $this->my_db != NULL ) {
				$rows = NULL;
				$result = $this->my_db->query( $query );
				for ( $i = 0; $i < $result->num_rows; $i++ )
					$rows[$i] = $result->fetch_assoc();

				if ( $result )
					$result->free();
					return $rows;
			}
			else {
				echo "Error : Cannot connect to database(queryArray)";
				exit;
			}
		}

		public function queryRow( $query )
		{
			if ( $this->my_db != NULL ) {
				$rows = NULL;
				$result = $this->my_db->query( $query );
				$i = 0;
				if ( count( $result ) > 0 ) {
					while ( $rowData = $result->fetch_row() )
						$rows[$i++] = $rowData;
				}

				if ( $result )
					$result->free();
					return $rows;
			}
			else {
				echo "Error : Cannot connect to database(queryRow)";
				exit;
			}
		}

		public function queryCount( $query ) {
			if ( $this->my_db != NULL ){
				$result = $this->my_db->query( $query );
				return count( $result );
			}
			else {
				echo "Error : Cannot connect to database(queryCount)";
				exit;
			}
			return 0;
		}

		public function queryInsert( $query )
		{
			/* ------------------------------ don't used..... -----------------
			$this->my_prevInsertId = 0;
			if (!empty($query) && $this->my_db != NULL)
			{
				$statement = $this->my_db->prepare($query);         // for Inser ID
				$statement->execute();
				if ($this->my_db->affected_rows < 1)
				{
					echo "Error : Cannot insert to database";
					return false;
				}
				else
				{
					$this->my_prevInsertId = $statement->insert_id;
				}
			}
			else
			{
				echo "Error : Cannot connect to database(queryInsert)";
				exit;
			}
			------------------------------ don't used..... ----------------- */
			$this->my_prevInsertId = 0;
			if ( !empty( $query ) && $this->my_db != NULL ) {
				$this->my_db->query( $query );
				$this->my_prevInsertId = $this->my_db->insert_id;
				return true;
			}
			else {
				echo "Error : Cannot connect to database(queryInsert)";
				exit;
			}

			return true;
		}

		public function getPrevInsertId() {
			return mysqli_insert_id($this->my_db);
		}

		public function getCharactSet() {
			return $this->my_db->get_client_version;
		}

		public function RES( $str ) {
		return $this->my_db->real_escape_string( $str );
		}
	}


	function fncReadUDL( $filepath ) {
		global $DATABASE; 

		$lines = file( $filepath );
		foreach ( $lines as $line_num => $line ) {
			$temp = htmlspecialchars( $line );
			$firstTok = explode( '=', $temp );
			if ( strcasecmp( trim( $firstTok[0] ), 'DATABASE_DRIVER' ) == 0 ) {
				$secondTok = explode( ';', $firstTok[1] );
				$DATABASE['DRIVER'] = trim( $secondTok[0] );
			}
			else if ( strcasecmp( trim( $firstTok[0] ), 'DATABASE_HOSTNAME' ) == 0 ) {
				$secondTok = explode( ';', $firstTok[1] );
				$DATABASE['HOSTNAME'] = trim( $secondTok[0] );
			}
			else if ( strcasecmp( trim( $firstTok[0] ), 'DATABASE_USER' ) == 0 ) {
				$secondTok = explode( ';', $firstTok[1] );
				$DATABASE['USER'] = trim( $secondTok[0] );
			}
			else if ( strcasecmp( trim($firstTok[0] ), 'DATABASE_PASSWORD' ) == 0 ) {
				$secondTok = explode( ';', $firstTok[1] );
				$DATABASE['PASSWORD'] = trim( $secondTok[0] );
			}
			else if ( strcasecmp( trim($firstTok[0] ), 'DATABASE_NAME' ) == 0 ) {
				$secondTok = explode( ';', $firstTok[1] );
				$DATABASE['NAME'] = trim( $secondTok[0] );
			}
	   }
	}
?>