<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once GCWI_ABSPATH . 'integrations/woocommerce/class-gcwi-wc-products.php';
require_once GCWI_ABSPATH . 'integrations/woocommerce/class-gcwi-wc-categories.php';
require_once GCWI_ABSPATH . 'integrations/woocommerce/class-gcwi-wc-attributes.php';
require_once GCWI_ABSPATH . 'integrations/gestaoclick/class-gcwi-gc-venda.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    GCWI
 * @subpackage GCWI/admin
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class GCWI_Admin 
{
	private $products;
	private $categories;

	public function declare_wc_compatibility()
	{
		if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class))
		{
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', GCWI_PLUGIN_FILE, true);
		}
	}

	public function add_cron_interval($schedules)
	{
		$schedules['fifteen_minutes'] = array
		(
			'interval' => 900,
			'display'  => __('Every Fifteen Minutes', 'gcwintegra'),
		);

		return $schedules;
	}

	public function add_woocommerce_integration($integrations)
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'integrations/woocommerce/class-gcwi-wc-integration.php';
		$integrations[] = 'GCWI_WC_Integration';

		return $integrations;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style('gcwi-admin', plugin_dir_url(__FILE__) . 'assets/css/gcwi-admin.css', array(), GCWI_VERSION, 'all');
		wp_enqueue_script('gcwi-admin', plugin_dir_url( __FILE__ ) . 'assets/js/gcwi-admin.js', array( 'jquery' ), GCWI_VERSION, false );
		wp_localize_script('gcwi-admin', 'gcwi_admin_ajax_object', 
			array
			(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('gcwi_nonce')
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
		register_setting('gcwi_credentials', 'gcwi-api-access-token', 				'string');
		register_setting('gcwi_credentials', 'gcwi-api-secret-access-token', 		'string');
		register_setting('gcwi_settings', 	'gcwi-settings-auto-imports', 			'boolean');
		register_setting('gcwi_settings', 	'gcwi-settings-categories-selection', 	'array');
		register_setting('gcwi_settings', 	'gcwi-settings-attributes-selection', 	'array');
		register_setting('gcwi_settings', 	'gcwi-settings-products-blacklist', 	'array');
		register_setting('gcwi_settings', 	'gcwi-settings-export-orders', 			'boolean');
		register_setting('gcwi_settings', 	'gcwi-settings-export-transportadora', 	'string');
		register_setting('gcwi_settings', 	'gcwi-settings-export-situacao',		'string');
	}

	/**
	 * Execute the importations of categories and products by cron schdeduled event.
	 * 
	 * @since    1.0.0
	 */
    public function import_all()
	{
		$this->categories = new GCWI_WC_Categories();
		$this->categories->import('all');

		$this->products = new GCWI_WC_Products();
		$this->products->import('all');

		update_option('gcwi_last_import', current_time('d/m/Y, H:i'));
    }

	public function export_order($order_id) 
	{
		$gc_venda = new GCWI_GC_Venda($order_id);
		$gc_venda->export();
	}
}
