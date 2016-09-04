<?php
/*
Plugin Name: Republish Old Posts Pro
Version: 1.11
Plugin URI: http://infolific.com/technology/software-worth-using/republish-old-posts-for-wordpress/#pro-version
Description: Republish old posts automatically by setting the date to the current date. Puts your evergreen posts in front of your users via the front page and feeds.
Author: Marios Alexandrou
Author URI: http://infolific.com/technology/
License: GPLv2 or later
Text Domain: republish-old-posts
*/

/*
Copyright 2015 Marios Alexandrou

Forked from the Old Post Promoter Plugin by Blog Traffic Exchange that was once housed in the WordPress Plugin Repository.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ROP_OMIT_CUSTOM_FIELD', 'rop_ignore' );
define( 'ROP_OMIT_CUSTOM_FIELD_VALUE', 'true' );

define( 'ROP_FORCE_CUSTOM_FIELD', 'rop_force' );
define( 'ROP_FORCE_CUSTOM_FIELD_VALUE', 'true' );

define( 'ROP_MATCH_PHRASE', '' );

define( 'ROP_0_MINUTES', 0 );
define( 'ROP_1_MINUTE', 60 );
define( 'ROP_5_MINUTES', 5 * ROP_1_MINUTE );
define( 'ROP_15_MINUTES', 15 * ROP_1_MINUTE );
define( 'ROP_30_MINUTES', 30 * ROP_1_MINUTE );
define( 'ROP_1_HOUR', 60 * ROP_1_MINUTE ); 
define( 'ROP_4_HOURS', 4 * ROP_1_HOUR ); 
define( 'ROP_6_HOURS', 6 * ROP_1_HOUR ); 
define( 'ROP_12_HOURS', 12 * ROP_1_HOUR );
define( 'ROP_24_HOURS', 24 * ROP_1_HOUR );
define( 'ROP_48_HOURS', 48 * ROP_1_HOUR ); 
define( 'ROP_72_HOURS', 72 * ROP_1_HOUR ); 
define( 'ROP_168_HOURS', 168 * ROP_1_HOUR ); 
define( 'ROP_1_DAY', 24 * ROP_1_HOUR );
define( 'ROP_7_DAYS', 7 * ROP_1_DAY );
define( 'ROP_14_DAYS', 14 * ROP_1_DAY );
define( 'ROP_INTERVAL', ROP_12_HOURS ); 
define( 'ROP_INTERVAL_SLOP', ROP_4_HOURS ); 
define( 'ROP_AGE_LIMIT', 120); // 120 days
define( 'ROP_OMIT_CATS', "" ); 

register_activation_hook( __FILE__, 'rop_activate' );
register_deactivation_hook( __FILE__, 'rop_deactivate' );

add_action( 'init', 'rop' );
add_action( 'admin_menu', 'rop_options_setup' );
//add_action( 'admin_head', 'rop_head_admin' );
add_filter( 'the_content', 'rop_the_content' );
add_filter( 'plugin_row_meta', 'rop_plugin_meta', 10, 2 );

function rop_plugin_meta( $links, $file ) { // add some links to plugin meta row
	if ( strpos( $file, 'republish-old-posts-pro.php' ) !== false ) {
		$links = array_merge( $links, array( '<a href="' . esc_url( get_admin_url(null, 'options-general.php?page=republish-old-posts') ) . '">Settings</a>' ) );
	}

	return $links;
}

function rop_deactivate() {

}

function rop_activate() {
	add_option( 'rop_interval', ROP_INTERVAL );
	add_option( 'rop_interval_slop', ROP_INTERVAL_SLOP );
	add_option( 'rop_age_limit', ROP_AGE_LIMIT );
	add_option( 'rop_omit_cats', ROP_OMIT_CATS );
	add_option( 'rop_show_original_pubdate', 1 );	
	add_option( 'rop_pos', 0 );
	add_option( 'rop_at_top', 0 );
	add_option( 'rop_omit_custom_field', ROP_OMIT_CUSTOM_FIELD );
	add_option( 'rop_omit_custom_field_value', ROP_OMIT_CUSTOM_FIELD_VALUE );
	add_option( 'rop_force_custom_field', ROP_FORCE_CUSTOM_FIELD );
	add_option( 'rop_force_custom_field_value', ROP_FORCE_CUSTOM_FIELD_VALUE );
	add_option( 'rop_match_phrase', ROP_MATCH_PHRASE );
	add_option( 'rop_select_random_post', 0 );
}

function rop() {
	if ( rop_update_time( ) ) {
		update_option( 'rop_last_update', time() );
		rop_republish_old_post();
	}
}

function rop_republish_old_post () {
	global $wpdb;
	$rop_omit_cats = get_option( 'rop_omit_cats' );
	$rop_age_limit = get_option( 'rop_age_limit' );
	$rop_omit_custom_field = get_option( 'rop_omit_custom_field' );
	$rop_omit_custom_field_value = get_option( 'rop_omit_custom_field_value' );
	$rop_force_custom_field = get_option( 'rop_force_custom_field' );
	$rop_force_custom_field_value = get_option( 'rop_force_custom_field_value' );
	$rop_match_phrase = get_option( 'rop_match_phrase' );
	$rop_select_random_post = get_option( 'rop_select_random_post' );

	if ( !isset( $rop_omit_cats ) ) {
		$rop_omit_cats = ROP_OMIT_CATS;
	}
	if ( !isset( $rop_age_limit ) ) {
		$rop_age_limit = ROP_AGE_LIMIT;
	}
	if ( !isset( $rop_omit_custom_field) || $rop_omit_custom_field === "" ) {
		$rop_omit_custom_field = ROP_OMIT_CUSTOM_FIELD;
	}
	if ( !isset($rop_omit_custom_field_value) || $rop_omit_custom_field_value === "" ) {
		$rop_omit_custom_field_value = ROP_OMIT_CUSTOM_FIELD_VALUE;
	}

	if ( !isset( $rop_force_custom_field) || $rop_force_custom_field === "" ) {
		$rop_force_custom_field = ROP_FORCE_CUSTOM_FIELD;
	}
	if ( !isset($rop_force_custom_field_value) || $rop_force_custom_field_value === "" ) {
		$rop_force_custom_field_value = ROP_FORCE_CUSTOM_FIELD_VALUE;
	}

	if ( !isset( $rop_match_phrase ) || $rop_match_phrase === "" ) {
		$rop_match_phrase = ROP_MATCH_PHRASE;
	}

	if ( !isset( $rop_select_random_post ) ) {
		$rop_select_post_selection_order = 'post_date ASC';
	} else {
		$rop_select_post_selection_order = 'RAND()';		
	}

/*	
	$sql = "(SELECT ID, post_date
            FROM $wpdb->posts
            WHERE post_type = 'post'
                  AND post_status = 'publish'
                  AND post_date < NOW( ) - INTERVAL " . $rop_age_limit * 24 . " HOUR 
                  ";
*/

	$sql = "(SELECT ID, post_date
            FROM $wpdb->posts
            WHERE post_type = 'post'
                  AND post_status = 'publish'
                  AND post_date < '" . current_time( 'mysql' ) . "' - INTERVAL " . $rop_age_limit * 24 . " HOUR 
                  ";
	
	if ( $rop_omit_cats!='' ) {
    	$sql = $sql."AND NOT(ID IN (SELECT tr.object_id 
                                    FROM $wpdb->terms t 
                                          inner join $wpdb->term_taxonomy tax on t.term_id=tax.term_id and tax.taxonomy='category' 
                                          inner join $wpdb->term_relationships tr on tr.term_taxonomy_id=tax.term_taxonomy_id 
                                    WHERE t.term_id IN (".$rop_omit_cats.")))";
    }            
    if ( $rop_omit_custom_field != "" && $rop_omit_custom_field_value != "" ) {
		$sql = $sql."AND NOT(ID IN (SELECT pm.post_id
									FROM $wpdb->postmeta pm
									WHERE pm.meta_key = '$rop_omit_custom_field' AND pm.meta_value = '$rop_omit_custom_field_value'
									))";
	}
    if ( $rop_match_phrase != "" ) {
		$sql = $sql."AND (ID IN (SELECT p.id
									FROM $wpdb->posts p
									WHERE p.post_title LIKE '%$rop_match_phrase%'
									))";
	}
			
	$sql = $sql. ") UNION (" .
			"SELECT ID, post_date
             FROM $wpdb->posts
             WHERE post_type = 'post'
			 AND post_status = 'publish'
			 AND (ID IN (SELECT pm.post_id
						 FROM $wpdb->postmeta pm
						 WHERE pm.meta_key = '$rop_force_custom_field' AND pm.meta_value = '$rop_force_custom_field_value'
						)) )";

	$sql = $sql.
            "ORDER BY $rop_select_post_selection_order
            LIMIT 1 ";						

	//error_log ( $sql );
	
	$oldest_post = $wpdb->get_var( $sql, 0, 0 );
	if ( isset( $oldest_post ) ) {
		rop_update_old_post( $oldest_post );
	}
}

function rop_update_old_post( $oldest_post ) {
	global $wpdb;

	$post = get_post( $oldest_post );
	$rop_original_pub_date = get_post_meta( $oldest_post, 'rop_original_pub_date', true ); 

	if ( !( isset( $rop_original_pub_date ) && $rop_original_pub_date!='' ) ) {
	    $sql = "SELECT post_date from ".$wpdb->posts." WHERE ID = '$oldest_post'";
		$rop_original_pub_date=$wpdb->get_var( $sql );
		add_post_meta($oldest_post, 'rop_original_pub_date', $rop_original_pub_date );
		$rop_original_pub_date = get_post_meta($oldest_post, 'rop_original_pub_date', true ); 
	}

	$rop_pos = get_option('rop_pos');
	if ( !isset( $rop_pos ) ) {
		$rop_pos = 0;
	}

	if ( $rop_pos == 1 ) {
//		$new_time = date( 'Y-m-d H:i:s' );
//		$new_time = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
		$new_time = current_time( 'mysql' );
		$gmt_time = get_gmt_from_date( $new_time );
	} else {
		$lastposts = get_posts( 'numberposts=1&offset=1' );
		foreach ( $lastposts as $lastpost ) {
			$post_date = strtotime( $lastpost->post_date );
			$new_time = date( 'Y-m-d H:i:s', mktime( date( "H", $post_date ), date( "i", $post_date ), date( "s", $post_date ) + 1, date( "m", $post_date ), date( "d",$post_date ), date( "Y",$post_date ) ) );
			$gmt_time = get_gmt_from_date( $new_time );
		}
	}

	$sql = "UPDATE $wpdb->posts SET post_date = '$new_time', post_date_gmt = '$gmt_time', post_modified = '$new_time', post_modified_gmt = '$gmt_time' WHERE ID = '$oldest_post'";		
	$wpdb->query($sql);
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}		
		
	//do_action( 'old_post_promoted', $post );
}

function rop_the_content( $content ) {
	global $post;
	$rop_show_original_pubdate = get_option( 'rop_show_original_pubdate' );
	if ( !isset( $rop_show_original_pubdate ) ) {
		$rop_show_original_pubdate = 1;
	}
	$rop_original_pub_date = get_post_meta( $post->ID, 'rop_original_pub_date', true );
	$dateline = '';
	if ( isset( $rop_original_pub_date ) && $rop_original_pub_date != '' ) {
		if ( $rop_show_original_pubdate ) {
			$dateline .= '<p id="rop"><small>';
			if ( $rop_show_original_pubdate ) {
				$dateline .= 'Originally posted ' . $rop_original_pub_date . '. ';
			}
			$dateline.='</small></p>';
		}
	}
	$rop_at_top = get_option( 'rop_at_top' );
	if ( isset( $rop_at_top ) && $rop_at_top ) {
		$content = $dateline.$content;
	} else {
		$content = $content.$dateline;
	}
	return $content;
}

function rop_update_time() {
	$last = get_option( 'rop_last_update' );
	$interval = get_option( 'rop_interval' );
	$time = time();

	if ( !( isset( $interval ) && is_numeric( $interval ) ) ) {
		$interval = ROP_INTERVAL;
	}

	$slop = get_option( 'rop_interval_slop' );
	if ( !( isset( $slop ) && is_numeric( $slop ) ) ) {
		$slop = ROP_INTERVAL_SLOP;
	}

	//error_log( 'last: ' . $last );
	//error_log( 'time: ' . $time );
	//error_log( 'time minus last: ' . ( $time - $last ) );
	//error_log( 'interval: ' . $interval );
	//error_log( 'slop: ' . $slop );
	
	if ( false === $last ) {
		$ret = 1;
		//error_log( 'ret (forced): ' . $ret );
	} else if ( is_numeric( $last ) ) {
		if ( $slop == 0 ) {
			if ( ( $time - $last ) >= $interval ) {
				$ret = 1;
			} else {
				$ret = 0;
			}
		} else {
			if ( ( $time - $last ) >= ( $interval + rand( 0, $slop ) ) ) {
				$ret = 1;
			} else {
				$ret = 0;
			}
		}
		//error_log( 'ret (calculated): ' . $ret );
	}
	
	return $ret;
}

define( 'EDD_ROP_STORE_URL', 'http://infolific.com' );
define( 'EDD_ROP_PLUGIN_NAME', 'Republish Old Posts Pro for WordPress' );

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include_once( dirname( __FILE__ ) . '/inc/EDD_SL_Plugin_Updater.php' );
}

function rop_edd_sl_plugin_updater() {
	$license_key = trim( get_option( 'rop_edd_license_key' ) );

	$edd_updater = new EDD_SL_Plugin_Updater( EDD_ROP_STORE_URL, __FILE__, array(
			'version' 	=> '1.11',					// current version number
			'license' 	=> $license_key,			// license key (used get_option above to retrieve from DB)
			'item_name' => EDD_ROP_PLUGIN_NAME, 	// name of this plugin
			'author' 	=> 'Marios Alexandrou'		// author of this plugin
		)
	);

}
add_action( 'admin_init', 'rop_edd_sl_plugin_updater', 0 );

function rop_edd_register_option() {
	// creates our settings in the options table
	register_setting('rop_edd_license', 'rop_edd_license_key', 'rop_edd_sanitize_license' );
}
add_action('admin_init', 'rop_edd_register_option');

function rop_edd_sanitize_license( $new ) {
	$old = get_option( 'rop_edd_license_key' );

	if( $old && $old != $new ) {
		delete_option( 'rop_edd_license_status' ); // new license has been entered, so must reactivate
	}

	return $new;
}

function rop_edd_activate_license() {
	if( isset( $_POST['rop_edd_license_activate'] ) ) {

		if( ! check_admin_referer( 'rop_edd_nonce', 'rop_edd_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}
		
		$license = trim( get_option( 'rop_edd_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( EDD_ROP_PLUGIN_NAME ),
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( EDD_ROP_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "valid" or "invalid"
		update_option( 'rop_edd_license_status', $license_data->license );
	}
}
add_action('admin_init', 'rop_edd_activate_license');

function rop_edd_deactivate_license() {
	if( isset( $_POST['rop_edd_license_deactivate'] ) ) {

		if( ! check_admin_referer( 'rop_edd_nonce', 'rop_edd_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}

		$license = trim( get_option( 'rop_edd_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( EDD_ROP_PLUGIN_NAME ),
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( EDD_ROP_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'rop_edd_license_status' );
		}
	}
}
add_action('admin_init', 'rop_edd_deactivate_license');

function rop_edd_check_license() {
	global $wp_version;

	$license = trim( get_option( 'rop_edd_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( EDD_ROP_PLUGIN_NAME ),
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( EDD_ROP_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if( $license_data->license == 'valid' ) {
		return true;
	} else {
		return false;
	}
}

function rop_options_page() {	 	
	$message = null;
	$message_updated = __("Options Updated.", 'rop');

	if (!empty($_POST['rop_action'])) {
		$message = $message_updated;

		if (isset($_POST['rop_interval'])) {
			$rop_interval = $_POST['rop_interval'];
			$rop_interval = intval( $rop_interval );
			update_option( 'rop_interval', $rop_interval );
		}

		if (isset($_POST['rop_interval_slop'])) {
			$rop_interval_slop = $_POST['rop_interval_slop'];
			$rop_interval_slop = intval( $rop_interval_slop );
			update_option( 'rop_interval_slop', $rop_interval_slop );
		}

		if ( isset( $_POST['rop_age_limit'] ) ) {
			if ( is_numeric( $_POST['rop_age_limit'] ) ) {
				$rop_age_limit = $_POST['rop_age_limit'];
			} else {
				$rop_age_limit = ROP_AGE_LIMIT;
			}
			update_option( 'rop_age_limit', $rop_age_limit );
		}

		if (isset($_POST['rop_show_original_pubdate'])) {
			$rop_show_original_pubdate = $_POST['rop_show_original_pubdate'];
			$rop_show_original_pubdate = intval( $rop_show_original_pubdate );
			update_option( 'rop_show_original_pubdate', $rop_show_original_pubdate );
		}

		if (isset($_POST['rop_pos'])) {
			$rop_pos = $_POST['rop_pos'];
			$rop_pos = intval( $rop_pos );
			update_option( 'rop_pos', $rop_pos );
		}

		if (isset($_POST['rop_at_top'])) {
			$rop_at_top = $_POST['rop_at_top'];
			$rop_at_top = intval( $rop_at_top );
			update_option( 'rop_at_top', $rop_at_top );
		}

		if (isset($_POST['rop_select_random_post'])) {
			$rop_select_random_post = $_POST['rop_select_random_post'];
			$rop_select_random_post = intval( $rop_select_random_post );
			update_option( 'rop_select_random_post', $rop_select_random_post );
		}		
		
		if (isset($_POST['rop_omit_custom_field'])) {
			$rop_omit_custom_field = $_POST['rop_omit_custom_field'];
			$rop_omit_custom_field = sanitize_text_field( $rop_omit_custom_field );
			update_option( 'rop_omit_custom_field', $rop_omit_custom_field );
		}

		if (isset($_POST['rop_omit_custom_field_value'])) {
			$rop_omit_custom_field_value = $_POST['rop_omit_custom_field_value'];
			$rop_omit_custom_field_value = sanitize_text_field( $rop_omit_custom_field_value );
			update_option( 'rop_omit_custom_field_value', $rop_omit_custom_field_value );
		}

		if (isset($_POST['rop_force_custom_field'])) {
			$rop_force_custom_field = $_POST['rop_force_custom_field'];
			$rop_force_custom_field = sanitize_text_field( $rop_force_custom_field );
			update_option( 'rop_force_custom_field', $rop_force_custom_field );
		}	

		if (isset($_POST['rop_force_custom_field_value'])) {
			$rop_force_custom_field_value = $_POST['rop_force_custom_field_value'];
			$rop_force_custom_field_value = sanitize_text_field( $rop_force_custom_field_value );
			update_option( 'rop_force_custom_field_value', $rop_force_custom_field_value );
		}

		if (isset($_POST['rop_match_phrase'])) {
			$rop_match_phrase = $_POST['rop_match_phrase'];
			$rop_match_phrase = sanitize_text_field( $rop_match_phrase );
			update_option( 'rop_match_phrase', $rop_match_phrase );
		}

		if (isset($_POST['post_category'])) {
			$rop_omit_custom_field_value = implode( ',', $_POST['post_category'] );
			$rop_omit_custom_field_value = sanitize_text_field( $rop_omit_custom_field_value );
			update_option( 'rop_omit_cats', $rop_omit_custom_field_value );
		} else {
			update_option('rop_omit_cats','');			
		}
		
		print('
			<div id="message" class="updated fade">
				<p>'.__( 'Options Updated.', 'republish-old-posts' ) . '</p>
			</div>');

	} else if ( isset( $_POST['rop_edd_license_save'] ) ) {
		update_option( 'rop_edd_license_key', trim( $_POST['rop_edd_license_key'] ) );
	}
		
	$rop_omit_cats = sanitize_text_field( get_option( 'rop_omit_cats' ) );
	if (!isset($rop_omit_cats)) {
		$rop_omit_cats = ROP_OMIT_CATS;
	}
	
	$rop_omit_custom_field = sanitize_text_field( get_option( 'rop_omit_custom_field' ) );
	if (!isset($rop_omit_custom_field) || $rop_omit_custom_field === "") {
		$rop_omit_custom_field = ROP_OMIT_CUSTOM_FIELD;
	}

	$rop_omit_custom_field_value = sanitize_text_field( get_option( 'rop_omit_custom_field_value' ) );
	if (!isset($rop_omit_custom_field_value ) || $rop_omit_custom_field_value === "" ) {
		$rop_omit_custom_field_value = ROP_OMIT_CUSTOM_FIELD_VALUE;
	}

	$rop_force_custom_field = sanitize_text_field( get_option( 'rop_force_custom_field' ) );
	if (!isset($rop_force_custom_field) || $rop_force_custom_field === "") {
		$rop_force_custom_field = ROP_FORCE_CUSTOM_FIELD;
	}

	$rop_force_custom_field_value = sanitize_text_field( get_option( 'rop_force_custom_field_value' ) );
	if (!isset($rop_force_custom_field_value ) || $rop_force_custom_field_value === "" ) {
		$rop_force_custom_field_value = ROP_FORCE_CUSTOM_FIELD_VALUE;
	}

	$rop_match_phrase = sanitize_text_field( get_option( 'rop_match_phrase' ) );
	if (!isset($rop_match_phrase ) || $rop_match_phrase === "" ) {
		$rop_match_phrase = ROP_MATCH_PHRASE;
	}
	
	if ( is_numeric( get_option( 'rop_age_limit' ) ) ) {
		$rop_age_limit = get_option( 'rop_age_limit' ) ;
	}
	if ( !isset( $rop_age_limit ) || $rop_age_limit == 0 ) {
		$rop_age_limit = ROP_AGE_LIMIT;
	}

	$rop_show_original_pubdate = intval( get_option( 'rop_show_original_pubdate' ) );
	if ( !isset( $rop_show_original_pubdate ) && !( $rop_show_original_pubdate == 0 || $rop_show_original_pubdate == 1 ) ) {
		$rop_show_original_pubdate = 1;
	}

	$rop_at_top = intval( get_option( 'rop_at_top' ) );
	if ( !( isset( $rop_at_top ) ) ) {
		$rop_at_top = 0;
	}

	$rop_pos = intval( get_option( 'rop_pos' ) );
	if ( !( isset( $rop_pos ) ) ) {
		$rop_pos = 1;
	}

	$rop_select_random_post = intval( get_option( 'rop_select_random_post' ) );
	if ( !( isset( $rop_select_random_post ) ) ) {
		$rop_select_random_post = 0;
	}

	$interval = intval( get_option( 'rop_interval' ) );
	if ( !( isset( $interval ) ) ) {
		$interval = ROP_INTERVAL;
	}

	$slop = intval( get_option( 'rop_interval_slop' ) );
	if ( !( isset( $slop ) ) ) {
		$slop = ROP_INTERVAL_SLOP;
	}

	print('
	<div class="wrap" style="padding-bottom: 5em">
		<h2>Republish Old Posts Pro</h2>
		<p>Posts on your site will be republished based on the conditions you specify below.</p>
		<p>A republished post will have its date reset to the current date and so it will appear in feeds, on your front page and at the top of archive pages.</p>
		<p><strong>WARNING:</strong> If your permalinks contain dates, disable this plugin immediately.</p>
		<div id="rop-items" class="postbox">
			<form id="rop" name="rop" action="' . $_SERVER['REQUEST_URI'] . '" method="post">
				<input type="hidden" name="rop_action" value="rop_update_settings" />
				<fieldset class="options">
					<div class="option">
						<label for="rop_interval">' . __( 'Minimum Interval Between Post Republishing: ', 'republish-old-posts' ) . '</label>
						<select name="rop_interval" id="rop_interval">
							<option value="' . ROP_5_MINUTES . '" ' . rop_option_selected( ROP_5_MINUTES,$interval ) . '>' . __( '5 Minutes', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_15_MINUTES . '" ' . rop_option_selected( ROP_15_MINUTES,$interval ) . '>' . __( '15 Minutes', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_30_MINUTES . '" ' . rop_option_selected( ROP_30_MINUTES,$interval ) . '>' . __( '30 Minutes', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_1_HOUR . '" ' . rop_option_selected( ROP_1_HOUR,$interval ) . '>' . __( '1 Hour', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_4_HOURS . '" ' . rop_option_selected( ROP_4_HOURS,$interval ) . '>' . __( '4 Hours', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_6_HOURS . '" ' . rop_option_selected( ROP_6_HOURS,$interval ) . '>' . __( '6 Hours', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_12_HOURS . '" ' . rop_option_selected( ROP_12_HOURS,$interval ) . '>' . __( '12 Hours', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_24_HOURS . '" ' . rop_option_selected( ROP_24_HOURS,$interval ) . '>' . __( '24 Hours (1 day)', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_48_HOURS . '" ' . rop_option_selected( ROP_48_HOURS,$interval ) . '>' . __( '48 Hours (2 days)', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_72_HOURS . '" ' . rop_option_selected( ROP_72_HOURS,$interval ) . '>' . __( '72 Hours (3 days)', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_168_HOURS . '" ' . rop_option_selected( ROP_168_HOURS,$interval ) . '>' . __( '168 Hours (7 days)', 'republish-old-posts' ) . '</option>
						</select>
					</div>
					<div class="option">
						<label for="rop_interval_slop">'.__( 'Randomness Interval (added to minimum interval): ', 'republish-old-posts' ) . '</label>
						<select name="rop_interval_slop" id="rop_interval_slop">
							<option value="' . ROP_0_MINUTES . '" ' . rop_option_selected( ROP_0_MINUTES,$slop ) . '>' . __( '0 Minutes', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_5_MINUTES . '" ' . rop_option_selected( ROP_5_MINUTES,$slop ) . '>' . __( 'Upto 5 Minutes', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_15_MINUTES . '" ' . rop_option_selected( ROP_15_MINUTES,$slop ) . '>' . __( 'Upto 15 Minutes', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_30_MINUTES . '" ' . rop_option_selected( ROP_30_MINUTES,$slop ) . '>' . __( 'Upto 30 Minutes', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_1_HOUR.'" ' . rop_option_selected( ROP_1_HOUR,$slop ) . '>' . __( 'Upto 1 Hour', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_4_HOURS.'" ' . rop_option_selected( ROP_4_HOURS,$slop ) . '>' . __( 'Upto 4 Hours', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_6_HOURS.'" ' . rop_option_selected( ROP_6_HOURS,$slop ) . '>' . __( 'Upto 6 Hours', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_12_HOURS.'" ' . rop_option_selected( ROP_12_HOURS,$slop ) . '>' . __( 'Upto 12 Hours', 'republish-old-posts' ) . '</option>
							<option value="' . ROP_24_HOURS.'" ' . rop_option_selected( ROP_24_HOURS,$slop ) . '>' . __( 'Upto 24 Hours (1 day)', 'republish-old-posts' ) . '</option>
						</select>
					</div>
					<div class="option">
						<label for="rop_age_limit">'.__( 'Post Age Before Eligible for Republishing: ', 'republish-old-posts' ) . '</label>
						<select name="rop_age_limit" id="rop_age_limit">
							<option value=".25" ' . rop_option_selected( .25, $rop_age_limit ) . '>' . __( '6 Hours', 'republish-old-posts' ) . '</option>
							<option value=".5" ' . rop_option_selected( .5, $rop_age_limit ) . '>' . __( '12 Hours', 'republish-old-posts' ) . '</option>
							<option value="1" ' . rop_option_selected( 1, $rop_age_limit ) . '>' . __( '1 Day', 'republish-old-posts' ) . '</option>
							<option value="7" ' . rop_option_selected( 7, $rop_age_limit ) . '>' . __( '1 Week', 'republish-old-posts' ) . '</option>
							<option value="14" ' . rop_option_selected( 14, $rop_age_limit ) . '>' . __( '2 Weeks', 'republish-old-posts' ) . '</option>
							<option value="30" ' . rop_option_selected( 30, $rop_age_limit ) . '>' . __( '30 Days', 'republish-old-posts' ) . '</option>
							<option value="60" ' . rop_option_selected( 60, $rop_age_limit ) . '>' . __( '60 Days', 'republish-old-posts' ) . '</option>
							<option value="90" ' . rop_option_selected( 90, $rop_age_limit ) . '>' . __( '90 Days', 'republish-old-posts' ) . '</option>
							<option value="120" ' . rop_option_selected( 120, $rop_age_limit ) . '>' . __( '120 Days', 'republish-old-posts' ) . '</option>
							<option value="240" ' . rop_option_selected( 240, $rop_age_limit ) . '>' . __( '240 Days', 'republish-old-posts' ) . '</option>
							<option value="365" ' . rop_option_selected( 365, $rop_age_limit ) . '>' . __( '365 Days', 'republish-old-posts' ) . '</option>
							<option value="730" ' . rop_option_selected( 730, $rop_age_limit ) . '>' . __( '730 Days', 'republish-old-posts' ) . '</option>
						</select>
					</div>
					<div class="option">
						<label for="rop_pos">'.__( 'Republish post to position (choosing the 2nd position will leave the most recent post in place): ', 'republish-old-posts' ) . '</label>
						<select name="rop_pos" id="rop_pos">
							<option value="1" ' . rop_option_selected( 1, $rop_pos) . '>' . __( '1st Position', 'republish-old-posts' ) . '</option>
							<option value="2" ' . rop_option_selected( 2, $rop_pos) . '>' . __( '2nd Position', 'republish-old-posts' ) . '</option>
						</select>
					</div>
					<div class="option">
						<label for="rop_show_original_pubdate">'.__( 'Show Original Publication Date at Post End? ', 'republish-old-posts' ) . '</label>
						<select name="rop_show_original_pubdate" id="rop_show_original_pubdate">
							<option value="1" ' . rop_option_selected( 1, $rop_show_original_pubdate ) . '>' . __( 'Yes', 'republish-old-posts' ) . '</option>
							<option value="0" ' . rop_option_selected( 0, $rop_show_original_pubdate ) . '>' . __( 'No', 'republish-old-posts' ) . '</option>
						</select>
					</div>
					<div class="option">
						<label for="rop_at_top">' . __( 'Show Original Publication Date At Top of Post? ', 'republish-old-posts' ) . '</label>
						<select name="rop_at_top" id="rop_at_top">
							<option value="1" ' . rop_option_selected( 1, $rop_at_top ) . '>' . __( 'Yes', 'republish-old-posts' ) . '</option>
							<option value="0" ' . rop_option_selected( 0, $rop_at_top ) . '>' . __( 'No', 'republish-old-posts' ) . '</option>
						</select>
					</div>
					<div class="option">
						<label for="rop_select_random_post">' . __( 'Select Random Post? ', 'republish-old-posts' ) . '</label>
						<select name="rop_select_random_post" id="rop_select_random_post">
							<option value="1" ' . rop_option_selected( 1, $rop_select_random_post ) . '>' . __( 'Yes', 'republish-old-posts' ) . '</option>
							<option value="0" ' . rop_option_selected( 0, $rop_select_random_post ) . '>' . __( 'No', 'republish-old-posts' ) . '</option>
						</select>
					</div>
					<div class="option">
						<label for="rop_omit_custom_field">' . __( 'Omit from republishing posts with the following custom field:', 'republish-old-posts' ) . '</label>
						<input type="text" name="rop_omit_custom_field" id="rop_omit_custom_field" value="' . $rop_omit_custom_field . '" autocomplete="off" />
					</div>	
					<div class="option">
						<label for="rop_omit_custom_field_value">' . __( 'Custom field value that should match to omit republishing:', 'republish-old-posts' ) . '</label>
						<input type="text" name="rop_omit_custom_field_value" id="rop_omit_custom_field_value" value="' . $rop_omit_custom_field_value . '" autocomplete="off" />
					</div>
					<div class="option">
						<label for="rop_force_custom_field">' . __( 'Force republishing posts with the following custom field:', 'republish-old-posts' ) . '</label>
						<input type="text" name="rop_force_custom_field" id="rop_force_custom_field" value="' . $rop_force_custom_field . '" autocomplete="off" />
					</div>	
					<div class="option">
						<label for="rop_force_custom_field_value">' . __( 'Custom field value that should match to force republishing:', 'republish-old-posts' ) . '</label>
						<input type="text" name="rop_force_custom_field_value" id="rop_force_custom_field_value" value="' . $rop_force_custom_field_value . '" autocomplete="off" />
					</div>	
					<div class="option">
						<label for="rop_match_phrase">' . __( 'Must contain this phrase in the title to be republished e.g. word will match word, keyword, words:', 'republish-old-posts' ) . '</label>
						<input type="text" name="rop_match_phrase" id="rop_match_phrase" value="' . $rop_match_phrase . '" autocomplete="off" />
					</div>	
					<div class="clearpad"></div>
					<div class="option">
						'.__( 'Select Categories to Omit from Republishing: ', 'republish-old-posts' ).'
					</div>
					<ul>
					');
	wp_category_checklist( 0, 0, explode( ',', $rop_omit_cats ) );
	print('			</ul>
				</fieldset>
				<div id="divTxt"></div>
				<div class="clearpad"></div>');
				if ( rop_edd_check_license() ) {
				print '<input type="submit" name="submit" value="' .__("Update Options", "republish-old-posts") . '" />';
				} else {
				print '<input type="submit" name="submit" value="' . __("Update Options", "republish-old-posts") . '" onClick="alert( \'Please buy/activate a license for this plugin. A license is required per domain other than localhost.\' ); return false;" />';
				} ?>
				<div class="clearpad"></div>
			</form>
		</div>
		<div id="rop-sb">
			<div class="postbox" id="rop-sbzero">
				<h3 class="hndle"><span>License</span></h3>
				<div class="inside">
					<?php
					$license 	= get_option( 'rop_edd_license_key' );
					$status 	= get_option( 'rop_edd_license_status' );
					?>
					<div class="wrap">
						<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
							<?php settings_fields('rop_edd_license'); ?>
							<input class="textbox-sidebar" id="rop_edd_license_key" name="rop_edd_license_key" type="text" value="<?php esc_attr_e( $license ); ?>" />
							<div id="license-buttons" style="display: inline-block; padding-top: 5px;">
								<div style="float: left;">
									<input type="submit" class="button" name="rop_edd_license_save" value="Save License"/>
								</div>
								<div style="float: left;">
									<?php if( false !== $license ) { ?>
										<?php if( $status !== false && $status == 'valid' ) {
											wp_nonce_field( 'rop_edd_nonce', 'rop_edd_nonce' ); ?>
											<input type="submit" class="button" name="rop_edd_license_deactivate" value="<?php _e( 'Deactivate License' ); ?>"/>
										<?php } else {
											wp_nonce_field( 'rop_edd_nonce', 'rop_edd_nonce' ); ?>
											<input type="submit" class="button" name="rop_edd_license_activate" value="<?php _e( 'Activate License' ); ?>"/>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>

			<div class="postbox" id="rop-sbtwo">
				<h3 class="hndle"><span>Support</span></h3>
				<div class="inside">
					<p>Your best bet is to post on the <a href="http://infolific.com/technology/software-worth-using/republish-old-posts-for-wordpress/#comment">support page</a> for the pro version.</p>
					<p>Please consider supporting me by <a href="https://wordpress.org/support/view/plugin-reviews/republish-old-posts">rating this plugin</a>. Thanks!</p>
				</div>
			</div>
			<div class="postbox" id="rop-sbthree">
				<h3 class="hndle"><span>Other Plugins</span></h3>
				<div class="inside">
					<ul>
						<li><a href="https://wordpress.org/plugins/real-time-find-and-replace/">Real-Time Find and Replace</a>: Set up find and replace rules that are executed AFTER a page is generated by WordPress, but BEFORE it is sent to a user's browser.</li>
						<li><a href="https://wordpress.org/plugins/republish-old-posts/">Republish Old Posts</a>: Republish old posts automatically by resetting the date to the current date. Puts your evergreen posts back in front of your users.</li>
						<li><a href="https://wordpress.org/extend/plugins/rss-includes-pages/">RSS Includes Pages</a>: Modifies RSS feeds so that they include pages and not just posts. My most popular plugin!</li>
						<li><a href="https://wordpress.org/extend/plugins/enhanced-plugin-admin">Enhanced Plugin Admin</a>: At-a-glance info (rating, review count, last update date) on your site's plugin page about the plugins you have installed (both active and inactive).</li>
						<li><a href="https://wordpress.org/extend/plugins/add-any-extension-to-pages/">Add Any Extention to Pages</a>: Add any extension of your choosing (e.g. .html, .htm, .jsp, .aspx, .cfm) to WordPress pages.</li>
						<li><a href="https://wordpress.org/extend/plugins/social-media-email-alerts/">Social Media E-Mail Alerts</a>: Receive e-mail alerts when your site gets traffic from social media sites of your choosing. You can also set up alerts for when certain parameters appear in URLs.</li>				</ul>
					</ul>
				</div>
			</div>
		</div>
	</div>
<?php
}

function rop_option_selected( $option_value, $value ) {
	if($option_value == $value) {
		return 'selected="selected"';
	}
	return '';
}

function rop_options_setup() {	
	$page = add_submenu_page( 'options-general.php', 'Republish Old Posts Pro', 'Republish Old Posts Pro', 'activate_plugins', 'republish-old-posts', 'rop_options_page' );
	add_action( "admin_print_scripts-$page", "rop_admin_scripts" );
	
}

/*
* Scripts needed for the admin side
*/
function rop_admin_scripts() {
	wp_enqueue_style( 'rop_styles', plugins_url( 'css/rop.css', __FILE__ ) );
}
