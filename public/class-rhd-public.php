<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.pragmasoftwares.com/
 * @since      1.0.0

 * @package    RHD
 * @subpackage RHD/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 *
 * @package    RHD
 * @subpackage RHD/public
 */
class RHD_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $rhd    The ID of this plugin.
	 */
	private $rhd;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $rhd       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $rhd, $version ) {

		$this->rhd = $rhd;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		 
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		 
	}

}
