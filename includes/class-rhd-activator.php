<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.pragmasoftwares.com/
 * @since      1.0.0
 *
 * @package    RHD
 * @subpackage RHD/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    RHD
 * @subpackage RHD/includes
 * @author     Pankaj Dadure <pankaj.pragma@gmail.com>
 */
class RHD_Activator {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$current_user = wp_get_current_user();
		$user_id = get_current_user_id();
		$rhd_hash_key = $current_user->user_login.$user_id;
		$fields = array(
			'rhd_website' => 'source',
	        'rhd_site_url' => "",
	        'rhd_destination_url' =>"",
	        'rhd_shash_key' => wp_hash($rhd_hash_key),	
			'rhd_dhash_key' => "",	        
	        'rhd_media_exclude' => "no",
	        'rhd_operation' => "add_update",
	        'rhd_author' => $user_id,
	        'rhd_comment'  => "no",
	        "rhd_posts" => "",
	        "rhd_media_ext" => "pdf,gif,svg,jpg,jpeg,png,mp4"
	    );
	    foreach( $fields as $field => $val ) {
	        $data = get_option( $field );
	        if ( $data == FALSE ) {
	            update_option( $field, $val);
	        }
	    }
	}
}