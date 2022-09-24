<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.pragmasoftwares.com/
 * @since      1.0.0
 *
 * @package    RHD
 * @subpackage RHD/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    RHD
 * @subpackage RHD/includes 
 * @author     Pankaj Dadure <pankaj.pragma@gmail.com>
 */
class RHD {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      RHD_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $rhd    The string used to uniquely identify this plugin.
	 */
	protected $rhd;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RHD_VERSION' ) ) {
			$this->version = RHD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->rhd = 'rhd-migration';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - RHD_Loader. Orchestrates the hooks of the plugin.
	 * - RHD_i18n. Defines internationalization functionality.
	 * - RHD_Admin. Defines all hooks for the admin area.
	 * - RHD_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rhd-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rhd-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rhd-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rhd-public.php';

		$this->loader = new RHD_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the RHD_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new RHD_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		global $rhd_admin;
		$rhd_admin  = new RHD_Admin( $this->get_rhd(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $rhd_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $rhd_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $rhd_admin, 'menu' );
		$this->loader->add_action( 'wp_ajax_load_rhd_log', $rhd_admin, 'load_rhd_log' );
		$this->loader->add_action( 'rest_api_init', $rhd_admin, 'rhd_init_call_request');
		$this->loader->add_action( 'rest_api_init', $rhd_admin, 'rhd_image_call_request');
		$this->loader->add_action( 'rest_api_init', $rhd_admin, 'rhd_check_call_request');

		$this->loader->add_action('admin_footer',  $rhd_admin, 'rhd_footer');
		$this->loader->add_filter( 'plugin_action_links_' .  $this->rhd.'/rhd-migration.php', $rhd_admin,  'rhd_settings_link' ); 
		$this->loader->add_filter('page_row_actions',$rhd_admin,  'add_rhd_post_action_menu', 10, 2);
		$this->loader->add_filter('post_row_actions',$rhd_admin,  'add_rhd_post_action_menu', 10, 2);
		$this->loader->add_action('post_submitbox_misc_actions', $rhd_admin, 'rhd_page_custom_button_classic');
		$this->loader->add_action('post_submitbox_misc_actions', $rhd_admin, 'rhd_page_custom_button_classic');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new RHD_Public( $this->get_rhd(), $this->get_version() );
		
		#$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		#$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_rhd() {
		return $this->rhd;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    rhd_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
