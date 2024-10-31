<?php

if ( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
	exit();

if ( !defined('RCAL_UPLOAD_DIR') ){
	$uploads = wp_upload_dir();
	define( 'RCAL_UPLOAD_DIR', $uploads['basedir'].DIRECTORY_SEPARATOR.'resource'.DIRECTORY_SEPARATOR);
}



function resource_calendar_delete_plugin() {
	global $wpdb;

	delete_option( 'rcal_holiday' );
	$wpdb->query( "DROP  TABLE IF EXISTS ".$wpdb->prefix."rcal_category" );
	$wpdb->query( "DROP  TABLE IF EXISTS ".$wpdb->prefix."rcal_log" );
	$wpdb->query( "DROP  TABLE IF EXISTS ".$wpdb->prefix."rcal_photo" );
	$wpdb->query( "DROP  TABLE IF EXISTS ".$wpdb->prefix."rcal_reservation" );
	$wpdb->query( "DROP  TABLE IF EXISTS ".$wpdb->prefix."rcal_repeat" );
	$wpdb->query( "DROP  TABLE IF EXISTS ".$wpdb->prefix."rcal_resource" );

	$id = get_option('rcal_confirm_page_id');
	if (! empty($id)  ){
		if (wp_delete_post( $id, true ) === false) error_log('delete post error ID:'.$id."\n", 3, ABSPATH.'/'.date('Y').'.txt');
	}
	delete_option( 'RCAL_CONFIG' );
	if (defined('RCAL_UNIT_DETAIL_SHOW'))
 		echo (" A-> ". get_option('rcal_installed'));
	delete_option( 'rcal_installed' );
	if (defined('RCAL_UNIT_DETAIL_SHOW'))
		echo (" B-> ". get_option('rcal_installed'));
	delete_option( 'rcal_holiday' );
	delete_option( 'rcal_confirm_page_id' );
	delete_option( 'RCAL_SP_DATES' );


	if(file_exists(RCAL_UPLOAD_DIR)){
		remove_directory(RCAL_UPLOAD_DIR);
	}
}

function remove_directory($dir) {
	if ($handle = opendir("$dir")) {
		while (false !== ($item = readdir($handle))) {
			if ($item != "." && $item != "..") {
				if (is_dir("$dir/$item")) {
					remove_directory("$dir/$item");
				} else {
					unlink("$dir/$item");
				}
			}
		}
		closedir($handle);
		rmdir($dir);
	}
}

resource_calendar_delete_plugin();
