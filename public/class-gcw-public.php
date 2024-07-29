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

	public function session_start()
	{
		if (!session_id()) {
			$session = session_start();
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

		return $quote_form->render_form();
	}

	public function shortcode_quote_woocommerce()
	{
		wp_enqueue_script($this->plugin_name . '-shortcode-quote-woocommerce', plugin_dir_url(__FILE__) . 'assets/js/gcw-shortcode-quote-woocommerce.js', array('jquery'), $this->version, false);
		wp_enqueue_style($this->plugin_name . '-shortcode-quote-woocommerce', plugin_dir_url(__FILE__) . 'assets/css/gcw-shortcode-quote-woocommerce.css', 	array(), $this->version, 'all');
		wp_localize_script($this->plugin_name . '-shortcode-quote-woocommerce', 'gcw_quote_ajax_object', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('gcw_quote_nonce')
		));
		$quote = new GCW_Shortcode_Quote_WooCommmerce();
		return $quote->render();
	}

	public function add_to_quote_button()
	{
		$product = wc_get_product(get_the_ID());

		if ($product) {
			if ($product->get_stock_status() == 'onbackorder') 
			{
				wp_enqueue_script($this->plugin_name . '-add-to-quote-button', plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-button.js', array('jquery'), $this->version, false);
				wp_enqueue_style($this->plugin_name . '-add-to-quote-button', plugin_dir_url(__FILE__) . 'assets/css/gcw-add-to-quote-button.css', array(), $this->version, 'all');
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
						'nonce' => wp_create_nonce('gcw_add_to_quote_simple_nonce')
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

			$this->add_item_to_quote($variation_id, $quantity, $parent_id);
		} else {
			wp_send_json_error('Não foi possível adicionar o item ao orçamento.');
		}
	}

	public function ajax_add_to_quote_simple()
	{
		if (isset($_POST['product_id'])) {
			$product_id = sanitize_text_field($_POST['product_id']);
			$quantity 	= sanitize_text_field($_POST['quantity']);

			$this->add_item_to_quote($product_id, $quantity);
		} else {
			wp_send_json_error('Não foi possível adicionar o item ao orçamento.');
		}
	}

	function add_item_to_quote($product_id, $quantity, $parent_id = null)
	{
		// Obter os itens do orçamento da sessão
		$quote_items = isset($_SESSION['quote_items']) ? $_SESSION['quote_items'] : array();

		// Verificar se o item já está no orçamento
		$item_found = false;
		foreach ($quote_items as &$item) {
			if ($item['product_id'] == $product_id) {
				$item['quantity'] += $quantity;
				$item_found = true;
			}
		}

		// Se o item não estiver no orçamento, adicioná-lo
		if (!$item_found) {
			$quote_items[] = array(
				'product_id' => $product_id,
				'quantity'   => $quantity,
			);
		}

		// Salvar os itens do orçamento na sessão
		$_SESSION['quote_items'] = $quote_items;

		$message = 'Produto adicionado ao orçamento com sucesso! <a href="' . esc_url(home_url() . '/orcamento') . '" class="button">Ver orçamento</a>';

		wc_add_notice($message);

		// Redireciona para a página do produto com uma mensagem de sucesso
		$redirect_url = get_permalink($parent_id ? $parent_id : $product_id);
		wp_send_json_success(array('redirect_url' => $redirect_url));

		wc_print_notices();
	}

	public function ajax_gcw_remove_quote_item()
	{
		if(isset($_POST['item_id'])){
			$item_id = sanitize_text_field($_POST['item_id']);
			$items = $_SESSION['quote_items'];

			if(is_array($items)){
				foreach ($items as $key => $item) {
					if ($item['product_id'] == $item_id) {
						unset($items[$key]);
					}
				}
			}

			$_SESSION['quote_items'] = $items;
		}
	}

	public function ajax_update_shipping()
	{
		if (isset($_POST['shipping_postcode'])) {
			$shipping_postcode = sanitize_text_field($_POST['shipping_postcode']);
			$quote_items = isset($_SESSION['quote_items']) ? $_SESSION['quote_items'] : array();

			// Criar um pacote para o cálculo do frete
			if (is_array($quote_items) && !empty($quote_items)) {
				$package = array(
					'contents' => array(),
					'contents_cost' => 0,
					'destination' => array(
						'country' => 'BR',
						'postcode' => $shipping_postcode,
					)
				);

				foreach ($quote_items as $quote_item) {
					$product_id = $quote_item['product_id'];
					$_product = wc_get_product($product_id);
					$package['contents'][$product_id] = array(
						'data' => $_product,
						'quantity' => $quote_item['quantity'],
						'line_total' => $_product->get_price() * $quote_item['quantity'],
						'line_tax' => 0,
						'line_subtotal' => $_product->get_price() * $quote_item['quantity'],
						'line_subtotal_tax' => 0,
					);
					$package['contents_cost'] += $_product->get_price() * $quote_item['quantity'];
				}

				// Calcular frete para o pacote
				$available_rates = $this->calculate_shipping_for_package($package);

				if (!empty($available_rates)) {
					$html = '<form><ul>';
					foreach ($available_rates as $rate) {
						$html .= '<li><input class="gcw_shipping_method_radio" name="shipping_method" type="radio" value="' . esc_attr($rate->cost) . '" data-method-id="' . esc_attr($rate->id) . '" ><label>'
						. esc_html($rate->label) . ': ' . wc_price($rate->cost) . 
						'</label></li>';
					}
					$html .= '</ul></form>';
					wp_send_json_success(array('html' => $html));
				} else {
					wp_send_json_error('Nenhuma opção de frete disponível.');
				}
			}
		}
		wp_die();
	}

	private function calculate_shipping_for_package($package)
	{
		// Obter a zona de envio correspondente ao pacote
		$shipping_zone = WC_Shipping_Zones::get_zone_matching_package($package);
		$shipping_methods = $shipping_zone->get_shipping_methods(true);

		$available_rates = array();
		foreach ($shipping_methods as $method) {
			$method->calculate_shipping($package);
			$available_rates = array_merge($available_rates, $method->rates);
		}

		return $available_rates;
	}

	public function ajax_selected_shipping_method()
	{
		if (isset($_POST['shipping_method']) && isset($_POST['shipping_cost'])) {
			$shipping_method = sanitize_text_field($_POST['shipping_method']);
			$shipping_cost = floatval($_POST['shipping_cost']);

			// Calcula o custo total do orçamento (frete + subtotal)
			$quote_items = $_SESSION['quote_items'];
			$subtotal = 0;

			if (is_array($quote_items) && !empty($quote_items)) {
				foreach ($quote_items as $quote_item) {
					$product_id = $quote_item['product_id'];
					$_product = wc_get_product($product_id);
					$subtotal += $_product->get_price() * $quote_item['quantity'];
				}
			}

			$total_cost = $subtotal + $shipping_cost;

			wp_send_json_success(array(
				'total_cost' => wc_price($total_cost)
			));
		} else {
			wp_send_json_error(array('message' => 'Método de envio ou custo do frete não especificado.'));
		}
	}

	public function ajax_save_quote()
	{
		if (!is_user_logged_in()) {
			// Adiciona o parâmetro de redirecionamento
			$redirect_url = wc_get_page_permalink('myaccount') . '?redirect_to=' . (home_url() . '/orcamento'); 
			wc_add_notice('Você precisa estar logado para enviar o orçamento.', 'notice');
			wp_send_json_error(array(
				'message' 		=> 'Você precisa estar logado para enviar o orçamento.', 
				'redirect_url' 	=> $redirect_url)
			);

			return;
		}

		$user_id = get_current_user_id();
		$quote_items = isset($_SESSION['quote_items']) ? $_SESSION['quote_items'] : array();

		// Criar um novo post do tipo 'quote'
		$quote_id = wp_insert_post(array(
			'post_title'  => 'Orçamento ' . current_time('Y-m-d H:i:s'),
			'post_status' => 'draft',
			'post_type'   => 'quote',
			'post_author' => $user_id
		));

		// Marcar a cotação como aberta
		update_post_meta($quote_id, 'status', 'open');
		update_post_meta($quote_id, 'items', $quote_items);

		// Limpar os itens do orçamento da sessão
		unset($_SESSION['quote_items']);

		wp_send_json_success(array('redirect_url' => get_permalink($quote_id)));
	}

	public function ajax_register_user()
	{
		// Valida e sanitiza os dados do formulário
		$email = sanitize_email($_POST['gcw_contato_email']);
		$nome = sanitize_text_field($_POST['gcw_contato_nome']);
		$telefone = sanitize_text_field($_POST['gcw_contato_telefone']);
		$cargo = sanitize_text_field($_POST['gcw_contato_cargo']);
		$nome_fantasia = sanitize_text_field($_POST['gcw_cliente_nome']);
		$cpf_cnpj = sanitize_text_field($_POST['gcw_cliente_cpf_cnpj']);

		// Verifica se o email já está registrado
		if (email_exists($email)) {
			wp_send_json_error(array('message' => 'Este e-mail já está registrado.'));
		}

		// Cria o usuário
		$user_id = wp_create_user($email, wp_generate_password(), $email);

		if (is_wp_error($user_id)) {
			wp_send_json_error(array('message' => 'Erro ao criar o usuário. Tente novamente.'));
		}

		// Atualiza os dados do usuário
		wp_update_user(array(
			'ID' => $user_id,
			'first_name' => $nome,
			'last_name' => $nome,
		));

		// Atualiza os meta dados do usuário
		update_user_meta($user_id, 'telefone', $telefone);
		update_user_meta($user_id, 'cargo', $cargo);
		update_user_meta($user_id, 'nome_fantasia', $nome_fantasia);
		update_user_meta($user_id, 'cpf_cnpj', $cpf_cnpj);

		// Conecta o usuário
		wp_set_auth_cookie($user_id);

		wp_send_json_success(array('redirect_url' => home_url('/orcamento')));
	}
}
