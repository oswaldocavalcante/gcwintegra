<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://github.com/oswaldocavalcante/gcwc
 * @since      1.0.0
 * @package    GCWC
 * @subpackage GCWC/includes
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class GCWC
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
	 * - GCWC_Admin. Defines all hooks for the admin area.
	 * - GCWC_Public. Defines all hooks for the public side of the site.
	 *
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		require_once GCWC_ABSPATH . 'admin/class-gcwc-admin.php';
		require_once GCWC_ABSPATH . 'public/class-gcwc-public.php';
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
		$plugin_admin = new GCWC_Admin();

		add_action('admin_init', 				array($plugin_admin, 'register_settings'));
		add_filter('cron_schedules',            array($plugin_admin, 'add_cron_interval'));
		add_action('admin_enqueue_scripts', 	array($plugin_admin, 'enqueue_scripts'));
		add_action('before_woocommerce_init',   array($plugin_admin, 'declare_wc_compatibility'));
		add_filter('woocommerce_integrations', 	array($plugin_admin, 'add_woocommerce_integration'));
		add_action('gcwc_update', 				array($plugin_admin, 'import_all'));
		add_action('wp_ajax_gcwc_update', 		array($plugin_admin, 'import_all'));

		if (get_option('gcwc-settings-export-orders') == 'yes') 
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
		$plugin_public = new GCWC_Public();

		if(get_option('gcwc-settings-shipping-calculator') == 'yes')
		{
			add_action('woocommerce_single_product_summary', 		array($plugin_public, 'shipping_calculator'), 41);
			add_action('wp_ajax_gcwc_calculate_shipping', 			array($plugin_public, 'ajax_calculate_shipping'));
			add_action('wp_ajax_nopriv_gcwc_calculate_shipping', 	array($plugin_public, 'ajax_calculate_shipping'));
		}
		else
		{
			remove_action('woocommerce_single_product_summary', 	array($plugin_public, 'shipping_calculator'));
			remove_action('wp_ajax_gcwc_calculate_shipping', 		array($plugin_public, 'ajax_calculate_shipping'));
			remove_action('wp_ajax_nopriv_gcwc_calculate_shipping', 	array($plugin_public, 'ajax_calculate_shipping'));
		}
	}
}
