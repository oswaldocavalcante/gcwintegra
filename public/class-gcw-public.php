<?php

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-orcamento.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-cliente.php';
require_once GCW_ABSPATH . 'public/views/shortcodes/class-gcw-shortcode-quote-form.php';
require_once GCW_ABSPATH . 'public/views/shortcodes/class-gcw-shortcode-quote.php';
require_once GCW_ABSPATH . 'public/views/shortcodes/class-gcw-shortcode-quote-checkout.php';

class GCW_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $quote;
	private $quote_checkout;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name 	= $plugin_name;
		$this->version 		= $version;

		$this->quote = new GCW_Shortcode_Quote();
		$this->quote_checkout = new GCW_Shortcode_Quote_Checkout();
	}

	public function session_start()
	{
		if (!session_id()) {
			session_start();
		}
	}

	/**
	 * Insert the Orçamento form in its shortcode place.
	 *
	 * @since    1.0.0
	 */
	public function shortcode_quote_form()
	{
		if (isset($_POST['gcw_nonce_orcamento']) && wp_verify_nonce($_POST['gcw_nonce_orcamento'], 'gcw_form_orcamento')) {
			$gc_cliente = new GCW_GC_Cliente($_POST, 'form');
			$gc_cliente->export();

			$gc_orcamento = new GCW_GC_Orcamento($_POST, $gc_cliente->get_id(), 'form');
			$gc_orcamento->export();
		}

		wp_enqueue_script($this->plugin_name . '-shortcode-quote-form', plugin_dir_url(__FILE__) . 'assets/js/gcw-shortcode-quote-form.js', array('jquery'), $this->version, false);
		wp_enqueue_style($this->plugin_name . '-shortcode-quote-form', plugin_dir_url(__FILE__) . 'assets/css/gcw-shortcode-quote-form.css', array(), $this->version, 'all');
		$quote_form = new GCW_Shortcode_Quote_Form();

		return $quote_form->render();
	}

	public function shortcode_quote()
	{
		wp_enqueue_script('gcw-shortcode-quote', GCW_URL . 'public/assets/js/gcw-shortcode-quote.js',     array('jquery'), GCW_VERSION, false);
		wp_enqueue_style('gcw-shortcode-quote', GCW_URL . 'public/assets/css/gcw-shortcode-quote.css',     array(), GCW_VERSION, 'all');
		wp_localize_script('gcw-shortcode-quote', 'gcw_quote_ajax_object', array(
			'url'   => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('gcw_quote_nonce'),
		));
		
		return $this->quote->render();
	}

	public function shortcode_quote_checkout()
	{
		wp_enqueue_script('gcw-shortcode-quote-checkout', GCW_URL . 'public/assets/js/gcw-shortcode-quote-checkout.js', array('jquery'), GCW_VERSION, false);
		wp_enqueue_style('gcw-shortcode-quote', GCW_URL . 'public/assets/css/gcw-shortcode-quote.css', 	array(), GCW_VERSION, 'all');

		return $this->quote_checkout->render();
	}

	public function add_to_quote_button()
	{
		$product = wc_get_product(get_the_ID());

		if ($product) {
			if ($product->get_stock_status() == 'onbackorder') 
			{
				wp_enqueue_script(	$this->plugin_name . '-add-to-quote-button', plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-button.js', array('jquery'), $this->version, false);
				wp_enqueue_style(	$this->plugin_name . '-add-to-quote-button', plugin_dir_url(__FILE__) . 'assets/css/gcw-add-to-quote-button.css', array(), $this->version, 'all');
				echo '<div id="gcw_add_to_quote_button" class="disabled" product_id="' . get_the_ID() . '">Adicionar ao orçamento</div>';

				// Differs the script for variable and simple products
				if ($product->has_child()) {
					wp_enqueue_script(	$this->plugin_name 	. '-add-to-quote-variation', plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-variation.js', 	array('jquery'), $this->version, true);
					wp_localize_script(	$this->plugin_name 	. '-add-to-quote-variation', 'gcw_add_to_quote_variation', array(
						'url' 	=> admin_url('admin-ajax.php'),
						'nonce' => wp_create_nonce('gcw_add_to_quote_variation')
					));
				} else {
					wp_enqueue_script(	$this->plugin_name . '-add-to-quote-simple', plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-simple.js', 	array('jquery'), $this->version, true);
					wp_localize_script(	$this->plugin_name . '-add-to-quote-simple', 'gcw_add_to_quote_simple', array(
						'url' 	=> admin_url('admin-ajax.php'),
						'nonce' => wp_create_nonce('gcw_add_to_quote_simple_nonce')
					));
				}
			}
		}
	}
}