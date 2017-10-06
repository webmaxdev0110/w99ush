<?php

if ( ! defined( 'ABSPATH' ) ) {
	require_once( "../../../../wp-load.php" );
}

require_once( "../classes/xi_dbconn_class.php");
require_once( "../classes/xi_push_class.php" );



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

?>