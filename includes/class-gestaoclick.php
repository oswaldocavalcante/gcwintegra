<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://github.com/oswaldocavalcante/gestaoclick
 * @since      1.0.0
 * @package    Gestaoclick
 * @subpackage Gestaoclick/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class Gestaoclick
{

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
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Gestaoclick_Admin. Defines all hooks for the admin area.
	 * - Gestaoclick_Public. Defines all hooks for the public side of the site.
	 *
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		require_once GCW_ABSPATH . 'admin/class-gcw-admin.php';
		require_once GCW_ABSPATH . 'public/class-gcw-public.php';
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
		$plugin_admin = new GCW_Admin();

		add_action('admin_init', 					array($plugin_admin, 'register_settings'));
		add_action('admin_menu', 					array($plugin_admin, 'add_admin_menu'));
		add_action('admin_enqueue_scripts', 		array($plugin_admin, 'enqueue_scripts'));
		add_action('before_woocommerce_init',       array($plugin_admin, 'declare_wc_compatibility'));
		add_filter('woocommerce_integrations', 		array($plugin_admin, 'add_woocommerce_integration'));
		add_action('gestaoclick_update', 			array($plugin_admin, 'import_all'));
		add_action('wp_ajax_gestaoclick_update', 	array($plugin_admin, 'import_all'));
		add_action('wp_ajax_gcw_nfe', 				array($plugin_admin, 'ajax_gcw_nfe'));

		if (get_option('gcw-settings-export-orders') == 'yes') 
		{
			add_action('woocommerce_order_status_processing', array($plugin_admin, 'export_order'));
		} 
		else 
		{
			remove_action('woocommerce_order_status_processing', array($plugin_admin, 'export_order'));
		}
	}

	private function define_public_hooks()
	{
		$plugin_public = new GCW_Public();

		if(get_option('gcw-settings-shipping-calculator') == 'yes')
		{
			add_action('woocommerce_single_product_summary', 		array($plugin_public, 'shipping_calculator'), 41);
			add_action('wp_ajax_gcw_calculate_shipping', 			array($plugin_public, 'ajax_calculate_shipping'));
			add_action('wp_ajax_nopriv_gcw_calculate_shipping', 	array($plugin_public, 'ajax_calculate_shipping'));
		}
		else
		{
			remove_action('woocommerce_single_product_summary', 	array($plugin_public, 'shipping_calculator'));
			remove_action('wp_ajax_gcw_calculate_shipping', 		array($plugin_public, 'ajax_calculate_shipping'));
			remove_action('wp_ajax_nopriv_gcw_calculate_shipping', 	array($plugin_public, 'ajax_calculate_shipping'));
		}
	}
}
