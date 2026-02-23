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
	 * Import categories and products by cron schdeduled event.
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
		$gc_venda_id = $gc_venda->export();

		if(is_wp_error($gc_venda_id)) 
		{
			error_log('GCWI – Erro ao exportar venda ' . $order_id . ': ' . $gc_venda_id->get_error_message());
			return;
		}
		else
		{
			$wc_order = wc_get_order($order_id);
			$wc_order->add_order_note('Venda exportada ao GestãoClick com sucesso. ID: ' . $gc_venda_id);
			$wc_order->save();
		}
	}

	public function generate_invoice($order_id)
	{
		$gc_nota_fiscal = new GCWI_GC_Notas_Fiscais();
		$gc_nota_fiscal_id = $gc_nota_fiscal->generate($order_id);

		if(is_wp_error($gc_nota_fiscal_id)) 
		{
			error_log('GCWI – Erro ao criar nota fiscal para o pedido ' . $order_id . ': ' . $gc_nota_fiscal_id->get_error_message());
			return;
		}
		else
		{
			$wc_order = wc_get_order($order_id);
			$wc_order->add_order_note('Nota fiscal criada com sucesso. ID: ' . $gc_nota_fiscal_id);
			$wc_order->save();
		}
	}
}
