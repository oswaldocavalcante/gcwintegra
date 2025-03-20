<?php

class GCW_Public
{

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

	/**
	 * 
	 */
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
			$package = array
			(
				'contents' => array(),
				'contents_cost' => 0,
				'destination' => array
				(
					'country' => 'BR',
					'postcode' => $shipping_postcode,
				)
			);

			$_product = wc_get_product($product_id);
			$package['contents'][$product_id] = array
			(
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
					if ($dropoff_deadline)
					{
						if ($dropoff_deadline->format('Y-m-d') == current_datetime()->format('Y-m-d'))
						{
							$dropoff_html = 'hoje';
						}
						else if ($dropoff_deadline->format('Y-m-d') == current_datetime()->modify('+1 day')->format('Y-m-d'))
						{
							$dropoff_html = 'amanhã';
						}
						else
						{
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

	/**
	 * @param $package
	 * @return array
	 */
	private function calculate_shipping_for_package($package)
	{
		$shipping_zone = WC_Shipping_Zones::get_zone_matching_package($package); // Obter a zona de envio correspondente ao pacote
		$shipping_methods = $shipping_zone->get_shipping_methods(true);

		$available_rates = array();
		foreach ($shipping_methods as $method)
		{
			$method->calculate_shipping($package);
			$available_rates = array_merge($available_rates, $method->rates);
		}

		return $available_rates;
	}
}
