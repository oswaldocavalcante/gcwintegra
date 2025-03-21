<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once GCWC_ABSPATH . 'integrations/woocommerce/class-gcwc-wc-products.php';
require_once GCWC_ABSPATH . 'integrations/woocommerce/class-gcwc-wc-categories.php';
require_once GCWC_ABSPATH . 'integrations/woocommerce/class-gcwc-wc-attributes.php';
require_once GCWC_ABSPATH . 'integrations/gestaoclick/class-gcwc-gc-venda.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    GCWC
 * @subpackage GCWC/admin
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class GCWC_Admin 
{
	private $products;
	private $categories;

	public function declare_wc_compatibility()
	{
		if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class))
		{
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', GCWC_PLUGIN_FILE, true);
		}
	}

	public function add_cron_interval($schedules)
	{
		$schedules['fifteen_minutes'] = array
		(
			'interval' => 900,
			'display'  => __('Every Fifteen Minutes', 'gcwc'),
		);

		return $schedules;
	}

	public function add_woocommerce_integration($integrations)
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'integrations/woocommerce/class-gcwc-wc-integration.php';
		$integrations[] = 'GCWC_WC_Integration';

		return $integrations;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style('gcwc-admin', plugin_dir_url(__FILE__) . 'assets/css/gcwc-admin.css', array(), GCWC_VERSION, 'all');
		wp_enqueue_script('gcwc-admin', plugin_dir_url( __FILE__ ) . 'assets/js/gcwc-admin.js', array( 'jquery' ), GCWC_VERSION, false );
		wp_localize_script('gcwc-admin', 'gcwc_admin_ajax_object', 
			array
			(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('gcwc_nonce')
			)
		);
	}
	
	/**
	 * Register custom fields for settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings()
	{
		register_setting('gcwc_credentials', 'gcwc-api-access-token', 				'string');
		register_setting('gcwc_credentials', 'gcwc-api-secret-access-token', 		'string');
		register_setting('gcwc_settings', 	'gcwc-settings-auto-imports', 			'boolean');
		register_setting('gcwc_settings', 	'gcwc-settings-categories-selection', 	'array');
		register_setting('gcwc_settings', 	'gcwc-settings-attributes-selection', 	'array');
		register_setting('gcwc_settings', 	'gcwc-settings-products-blacklist', 	'array');
		register_setting('gcwc_settings', 	'gcwc-settings-export-orders', 			'boolean');
		register_setting('gcwc_settings', 	'gcwc-settings-export-transportadora', 	'string');
		register_setting('gcwc_settings', 	'gcwc-settings-export-situacao',		'string');
	}

	/**
	 * Execute the importations of categories and products by cron schdeduled event.
	 * 
	 * @since    1.0.0
	 */
    public function import_all()
	{
		$this->categories = new GCWC_WC_Categories();
		$this->categories->import('all');

		$this->products = new GCWC_WC_Products();
		$this->products->import('all');

		update_option('gcwc_last_import', current_time('d/m/Y, H:i'));
    }

	public function export_order($order_id) 
	{
		$gc_venda = new GCWC_GC_Venda($order_id);
		$gc_venda->export();
	}
}
