<?php

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-orcamento.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-cliente.php';
require_once GCW_ABSPATH . 'public/views/shortcodes/class-gcw-shortcode-quote-form.php';
require_once GCW_ABSPATH . 'public/views/shortcodes/class-gcw-shortcode-quote-woocommerce.php';

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'assets/css/gestaoclick-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'assets/js/gestaoclick-public.js', array('jquery'), $this->version, false);
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

		$orcamento = new GCW_Shortcode_Orcamento();

		return $orcamento->render_form();
	}

	public function shortcode_quote_woocommerce()
	{
		$quote = new GCW_Shortcode_Quote();

		return $quote->render_quote();
	}

	public function add_to_quote_button()
	{
		$product = wc_get_product(get_the_ID());

		if ($product) {
			if ($product->get_stock_status() == 'onbackorder') 
			{
				wp_enqueue_script(	$this->plugin_name . '-add-to-quote-button', 	plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-button.js', 	array('jquery'), $this->version, 	false);
				wp_enqueue_style(	$this->plugin_name . '-add-to-quote-button', 	plugin_dir_url(__FILE__) . 'assets/css/gcw-add-to-quote-button.css', 	array(), $this->version, 			'all');
				echo '<div id="gcw_add_to_quote_button" class="disabled" product_id="' . get_the_ID() . '">Adicionar ao orçamento</div>';

				// Differs the script for variable and simple products
				if ($product->has_child()) {
					wp_enqueue_script($this->plugin_name . '-add-to-quote-variation', 	plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-variation.js', 	array('jquery'), $this->version, true);
					wp_localize_script($this->plugin_name . '-add-to-quote-variation', 'gcw_add_to_quote_variation', array(
						'url' => admin_url('admin-ajax.php'),
						'nonce' => wp_create_nonce('gcw_add_to_quote_variation')
					));
				} else {
					wp_enqueue_script($this->plugin_name . '-add-to-quote-simple', 	plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-simple.js', 	array('jquery'), $this->version, true);
					wp_localize_script($this->plugin_name . '-add-to-quote-simple', 'gcw_add_to_quote_simple', array(
						'url' => admin_url('admin-ajax.php'),
						'nonce' => wp_create_nonce('gcw_add_to_quote_simple')
					));
				}
			}
		}
	}

	public function ajax_add_to_quote_variation()
	{
		if(isset($_POST['variation_id'])) {
			$parent_id 		= sanitize_text_field($_POST['parent_id']);
			$variation_id 	= sanitize_text_field($_POST['variation_id']);
			$quantity 		= sanitize_text_field($_POST['quantity']);
			$user_id 		= get_current_user_id();

			// Verificar se o usuário já possui uma cotação aberta
			$args = array(
				'post_type' 	=> 'quote',
				'post_status' 	=> 'draft',
				'author' 		=> $user_id,
				'meta_query' 	=> array(
										array(
											'key' 		=> 'status',
											'value' 	=> 'open',
											'compare' 	=> '='
										)
				)
			);

			$quotes = get_posts($args);

			if (empty($quotes)) {
				// Criar uma nova cotação
				$quote_id = wp_insert_post(array(
					'post_title' 	=> 'Orçamento ' . sizeof($quotes) + 1,
					'post_status' 	=> 'draft',
					'post_type' 	=> 'quote',
					'post_author' 	=> $user_id
				));

				// Marcar a cotação como aberta
				update_post_meta($quote_id, 'status', 'open');
			} else {
				// Usar a cotação existente
				$quote_id = $quotes[0]->ID;
			}

			// Adicionar o produto à lista de produtos da cotação
			$items = get_post_meta($quote_id, 'items', true);
			if (empty($items)) {
				$items = array();
			}
			$items[] = array(
				'product_id'=> $variation_id,
				'quantity'	=> $quantity,
			);
			
			if(update_post_meta($quote_id, 'items', $items)) {
				$message = 'Produto adicionado ao orçamento com sucesso! <a href="' . esc_url(get_permalink($quote_id)) . '" class="button">Ver orçamento</a>';
				wc_add_notice($message, 'success');
				// Redireciona para a página do produto com uma mensagem de sucesso
				$redirect_url = get_permalink($parent_id);
				wp_send_json_success(array('redirect_url' => $redirect_url));
			}

		} else {
			wp_send_json_error('Não foi possíve adicionar o item ao orçamento.');
		}
	}

	public function ajax_add_to_quote_simple()
	{
		if (isset($_POST['product_id'])) {
			$product_id = sanitize_text_field($_POST['product_id']);
			$quantity 	= sanitize_text_field($_POST['quantity']);
			$user_id 	= get_current_user_id();

			// Verificar se o usuário já possui uma cotação aberta
			$args = array(
				'post_type' => 'quote',
				'post_status' => 'draft',
				'author' => $user_id,
				'meta_query' => array(
					array(
						'key' => 'status',
						'value' => 'open',
						'compare' => '='
					)
				)
			);

			$quotes = get_posts($args);

			if (empty($quotes)) {
				// Criar uma nova cotação
				$quote_id = wp_insert_post(array(
					'post_title' 	=> 'Orçamento',
					'post_status' 	=> 'draft',
					'post_type' 	=> 'quote',
					'post_author' 	=> $user_id
				));

				// Marcar a cotação como aberta
				update_post_meta($quote_id, 'status', 'open');
			} else {
				// Usar a cotação existente
				$quote_id = $quotes[0]->ID;
			}

			// Adicionar o produto à lista de produtos da cotação
			$items = get_post_meta($quote_id, 'items', true);
			if (empty($items)) {
				$items = array();
			}
			$items[] = array(
				'product_id' => $product_id,
				'quantity'	=> $quantity,
			);

			if (update_post_meta($quote_id, 'items', $items)) {
				$message = 'Produto adicionado ao orçamento com sucesso! <a href="' . esc_url(get_permalink($quote_id)) . '" class="button">Ver orçamento</a>';
				wc_add_notice($message, 'success');
				// Redireciona para a página do produto com uma mensagem de sucesso
				$redirect_url = get_permalink($product_id);
				wp_send_json_success(array('redirect_url' => $redirect_url));
			}

		} else {
			wp_send_json_error('Não foi possíve adicionar o item ao orçamento.');
		}
	}
}
