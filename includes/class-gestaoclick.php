<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Gestaoclick
 * @subpackage Gestaoclick/includes
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
 * @package    Gestaoclick
 * @subpackage Gestaoclick/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class Gestaoclick
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gestaoclick_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

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
	public function __construct()
	{
		if (defined('GCW_VERSION')) {
			$this->version = GCW_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'gestaoclick';

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
	 * - Gestaoclick_Loader. Orchestrates the hooks of the plugin.
	 * - Gestaoclick_i18n. Defines internationalization functionality.
	 * - Gestaoclick_Admin. Defines all hooks for the admin area.
	 * - Gestaoclick_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-gestaoclick-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-gestaoclick-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-gcw-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-gcw-public.php';

		$this->loader = new Gestaoclick_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Gestaoclick_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Gestaoclick_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new GCW_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('init', $plugin_admin, 'register_quote_endpoint');
		$this->loader->add_action('init', $plugin_admin, 'create_quote_post_type');
		$this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('template_include', $plugin_admin, 'include_template_quote');
		$this->loader->add_action('display_post_states', $plugin_admin, 'add_display_post_states', 10, 2);
		$this->loader->add_filter('woocommerce_integrations', 	$plugin_admin, 'add_woocommerce_integration');
		$this->loader->add_action('gestaoclick_update', $plugin_admin, 'import_gestaoclick');

		if (get_option('gcw-settings-export-orders') == 'yes') {
			add_action('woocommerce_order_status_processing', array( $plugin_admin, 'export_order'));
		} else {
			remove_action('woocommerce_order_status_processing', 'export_order');
		}

		$this->loader->add_action('rest_api_init', $plugin_admin, 'add_stone_webhook');
	}

	private function define_public_hooks()
	{
		$plugin_public = new GCW_Public($this->get_plugin_name(), $this->get_version());
		
		add_action('init', 									array($plugin_public, 'session_start'));
		add_action('woocommerce_after_add_to_cart_button', 	array($plugin_public, 'add_to_quote_button'));
		add_shortcode('gestaoclick_orcamento_formulario', 	array($plugin_public, 'shortcode_quote_form'));
		add_shortcode('gestaoclick_orcamento', 				array($plugin_public, 'shortcode_quote'));
		add_shortcode('gestaoclick_finalizar_orcamento', 	array($plugin_public, 'shortcode_quote_checkout'));
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Gestaoclick_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
