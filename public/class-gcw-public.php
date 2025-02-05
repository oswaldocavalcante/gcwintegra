<?php

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-orcamento.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-cliente.php';

require_once GCW_ABSPATH . 'public/views/shortcodes/class-gcw-shortcode-quote.php';
require_once GCW_ABSPATH . 'public/views/shortcodes/class-gcw-shortcode-checkout.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class GCW_Public
{
	private $quote;
	private $checkout;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct()
	{
		$this->quote 	= new GCW_Shortcode_Quote();
		$this->checkout = new GCW_Shortcode_Checkout();
	}

	public function session_start()
	{
		if (is_user_logged_in() || is_admin())
		{
			return;
		}
		if (isset(WC()->session))
		{
			if (!WC()->session->has_session())
			{
				WC()->session->set_customer_session_cookie(true);
			}
		}
	}

	// Adicionar a query var para o endpoint
	function add_quote_query_vars($vars)
	{
		$vars[] = 'orcamento';

		return $vars;
	}

	public function include_template_quote($template)
	{
		if (is_singular('orcamento'))
		{
			// Caminho para o template no diretório do plugin
			$template = GCW_ABSPATH . 'public/views/templates/single-quote.php';
			if (file_exists($template))
			{
				return $template;
			}
		}

		return $template;
	}

	// Função para adicionar 'Orçamentos' ao menu de conta
	function add_orcamentos_to_account_menu($items)
	{
		$items = array_slice($items, 0, 2, true) + // Mantém o primeiro item
			array('orcamentos' => 'Orçamentos') + // Adiciona 'Orçamentos'
			array_slice($items, 2, NULL, true); // Mantém o resto

		return $items;
	}

	// Exibe o conteúdo do endpoint 'orcamentos' em WC myaccount
	function orcamentos_endpoint_content()
	{
		wc_get_template('wc-myaccount-quotes.php', array(), 'quotes', GCW_ABSPATH . 'public/views/templates/');
	}

	public function shipping_calculator()
	{
		wp_enqueue_style('gcw-shortcode-quote', GCW_URL . 'public/assets/css/gcw-public.css', array(), GCW_VERSION, 'all');
		wp_enqueue_script('gcw-shipping-calculator', GCW_URL . 'public/assets/js/gcw-shipping-calculator.js', array('jquery'), GCW_VERSION, false);
		wp_localize_script('gcw-shipping-calculator', 'gcw_quote_ajax_object', array
		(
			'url'   => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('gcw_quote_nonce'),
		));

		?>
		<div id="gcw_quote_totals_shipping" class="gcw_quote_totals_section">
			<p><?php echo esc_html_e('Cálculo da entrega', 'gestaoclick'); ?></p>
			<div id="gcw_quote_shipping_address"></div>
			<form method="POST" id="gcw_quote_shipping_form">
				<input type="text" id="shipping_postcode" name="shipping_postcode" placeholder="Digite seu CEP" />
				<button id="gcw-update-shipping-button" type="button" class="button">Calcular</button>
			</form>
			<div id="gcw_quote_shipping_options"></div>
		</div>
		<?php
	}

	public function ajax_calculate_shipping()
	{
		if (isset($_POST['shipping_postcode']) && isset($_POST['product_id']) && isset($_POST['quantity']))
		{
			// Desfaz a seleção do método de envio
			WC()->session->set('has_selected_shipping_method', null);

			$shipping_postcode = sanitize_text_field($_POST['shipping_postcode']);
			$product_id = sanitize_text_field($_POST['product_id']);
			$quantity = sanitize_text_field($_POST['quantity']);

			// Criar um pacote para o cálculo do frete

			$package = array(
				'contents' => array(),
				'contents_cost' => 0,
				'destination' => array(
					'country' => 'BR',
					'postcode' => $shipping_postcode,
				)
			);

			$_product = wc_get_product($product_id);
			$package['contents'][$product_id] = array(
				'data' => $_product,
				'quantity' => $quantity,
				'line_total' => $_product->get_price() * $quantity,
				'line_tax' => 0,
				'line_subtotal' => $_product->get_price() * $quantity,
				'line_subtotal_tax' => 0,
			);
			$package['contents_cost'] += $_product->get_price() * $quantity;

			// Calcular frete para o pacote
			$rates = $this->calculate_shipping_for_package($package);

			if ($rates)
			{
				$html = '<form><ul>';
				foreach ($rates as $rate)
				{
					$dropoff_html = '';

					$dropoff_deadline = $rate->meta_data['dropoff_deadline'];
					if($dropoff_deadline)
					{
						if($dropoff_deadline->format('Y-m-d') == current_datetime()->format('Y-m-d')) {
							$dropoff_html = 'hoje';
						}
						else if($dropoff_deadline->format('Y-m-d') == current_datetime()->modify('+1 day')->format('Y-m-d')) {
							$dropoff_html = 'amanhã';
						}
						else {
							$formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, wp_timezone(), IntlDateFormatter::GREGORIAN, 'EEEE');
							$dropoff_html = $formatter->format($dropoff_deadline);
						}

						$dropoff_html = sprintf('(chegará %s)', $dropoff_html);
					}

					$html .= sprintf('<li> %s: %s %s </li>', esc_html($rate->label), wc_price($rate->cost), $dropoff_html);
				}
				$html .= '</ul></form>';

				wp_send_json_success(array('html' => $html));
			}
			else
			{
				wp_send_json_error('Nenhuma opção de frete disponível.');
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
		foreach ($shipping_methods as $method)
		{
			$method->calculate_shipping($package);
			$available_rates = array_merge($available_rates, $method->rates);
		}

		return $available_rates;
	}

	public function shortcode_quote()
	{
		wp_enqueue_style('gcw-shortcode-quote', GCW_URL . 'public/assets/css/gcw-public.css', array(), GCW_VERSION, 'all');
		wp_enqueue_script('gcw-shortcode-quote', GCW_URL . 'public/assets/js/gcw-shortcode-quote.js', array('jquery'), GCW_VERSION, false);
		wp_localize_script('gcw-shortcode-quote', 'gcw_quote_ajax_object', array(
			'url'   => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('gcw_quote_nonce'),
		));

		return $this->quote->render();
	}

	public function shortcode_checkout()
	{
		wp_enqueue_style('gcw-shortcode-quote', GCW_URL . 'public/assets/css/gcw-public.css', array(), GCW_VERSION, 'all');
		wp_enqueue_style('gcw-shortcode-checkout', GCW_URL . 'public/assets/css/gcw-shortcode-checkout.css', array(), GCW_VERSION, 'all');
		wp_enqueue_script('gcw-shortcode-checkout', GCW_URL . 'public/assets/js/gcw-shortcode-checkout.js', array('jquery'), GCW_VERSION, false);
		wp_localize_script('gcw-shortcode-checkout', 'gcw_quote_ajax_object', array(
			'url'   => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('gcw_quote_nonce'),
		));

		return $this->checkout->render();
	}

	public function add_to_quote_button()
	{
		$product = wc_get_product(get_the_ID());

		if ($product)
		{
			if ($product->get_stock_status() == 'onbackorder')
			{
				wp_enqueue_style('gcw-add-to-quote-button', plugin_dir_url(__FILE__) . 'assets/css/gcw-public.css', array(), GCW_VERSION, 'all');
				wp_enqueue_script('gcw-add-to-quote-button', plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-button.js', array('jquery'), GCW_VERSION, false);
				echo '<a id="gcw_add_to_quote_button" class="disabled" product_id="' . get_the_ID() . '">Adicionar ao orçamento</a>';

				// Diferencia o script para produtos variáveis e simples
				if ($product->has_child())
				{
					wp_enqueue_script('gcw-add-to-quote-variation', plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-variation.js', array('jquery'), GCW_VERSION, true);
					wp_localize_script('gcw-add-to-quote-variation', 'gcw_add_to_quote_variation', array(
						'url' 	=> admin_url('admin-ajax.php'),
						'nonce' => wp_create_nonce('gcw_add_to_quote_variation')
					));
				}
				else
				{
					wp_enqueue_script('gcw-add-to-quote-simple', plugin_dir_url(__FILE__) . 'assets/js/gcw-add-to-quote-simple.js', array('jquery'), GCW_VERSION, true);
					wp_localize_script('gcw-add-to-quote-simple', 'gcw_add_to_quote_simple', array(
						'url' 	=> admin_url('admin-ajax.php'),
						'nonce' => wp_create_nonce('gcw_add_to_quote_simple')
					));
				}
			}
		}
	}

	public function ajax_create_spec_sheet()
	{
		// Verificar nonce
		if (!check_ajax_referer('gcw_spec_sheet_nonce', 'nonce', false))
		{
			wp_die('Erro de segurança');
		}

		$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
		$quote_id 	= isset($_GET['quote_id']) ? intval($_GET['quote_id']) : 0;

		$product 	= wc_get_product($product_id);
		$parent_id 	= $product->is_type('variation') ? $product->get_parent_id() : null;

		if (!$product_id || !$quote_id)
		{
			wp_die('Parâmetros inválidos');
		}

		// Configurar cabeçalhos para exibir o PDF no navegador
		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="ficha_tecnica.pdf"');
		header('Cache-Control: private, max-age=0, must-revalidate');
		header('Pragma: public');

		require_once(GCW_ABSPATH . 'vendor/autoload.php');

		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isPhpEnabled', true);
		$options->set('isRemoteEnabled', true);
		$options->set('chroot', wp_upload_dir()); // Permissão para acessar imagens da pasta uploads

		$dompdf = new Dompdf($options);

		ob_start();
		include(GCW_ABSPATH . 'public/views/templates/spec-sheet.php');
		$html = ob_get_clean();

		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream("ficha-tecnica.pdf", array("Attachment" => false));

		exit;
	}
}
