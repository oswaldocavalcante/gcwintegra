<?php

class GCW_Shortcode_Quote
{
    public function __construct()
    {
        add_action('wp_ajax_gcw_add_to_quote_simple',               array($this, 'ajax_add_to_quote_simple'));
        add_action('wp_ajax_nopriv_gcw_add_to_quote_simple',        array($this, 'ajax_add_to_quote_simple'));

        add_action('wp_ajax_gcw_add_to_quote_variation',            array($this, 'ajax_add_to_quote_variation'));
        add_action('wp_ajax_nopriv_gcw_add_to_quote_variation',     array($this, 'ajax_add_to_quote_variation'));

        add_action('wp_ajax_gcw_remove_quote_item',                 array($this, 'ajax_gcw_remove_quote_item'));
        add_action('wp_ajax_nopriv_gcw_remove_quote_item',          array($this, 'ajax_gcw_remove_quote_item'));

        add_action('wp_ajax_gcw_update_shipping',                   array($this, 'ajax_update_shipping'));
        add_action('wp_ajax_nopriv_gcw_update_shipping',            array($this, 'ajax_update_shipping'));

        add_action('wp_ajax_gcw_selected_shipping_method',          array($this, 'ajax_selected_shipping_method'));
        add_action('wp_ajax_nopriv_gcw_selected_shipping_method',   array($this, 'ajax_selected_shipping_method'));

        add_action('wp_ajax_gcw_save_quote',                        array($this, 'ajax_proceed_to_checkout'));
        add_action('wp_ajax_nopriv_gcw_save_quote',                 array($this, 'ajax_proceed_to_checkout'));
    }

    public function render()
    {
        $this->update_quote_quantities();
        wc_print_notices();

        ob_start();
        $quote_items = WC()->session->get('quote_items');

        if (is_array($quote_items) && !empty($quote_items)) :
            $quote_subtotal = $this->get_quote_subtotal($quote_items);
        ?>
            <div id="gcw-quote-container">

                <div id="gcw-quote-forms-container">
                    <form id="gcw-quote-form" class="woocommerce-cart-form" method="post">
                        <table id="gcw-quote-woocommerce-table" class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">

                            <thead>
                                <tr>
                                    <th class="product-remove">     <span class="screen-reader-text"><?php esc_html_e('Remove item', 'woocommerce'); ?></span></th>
                                    <th class="product-thumbnail">  <span class="screen-reader-text"><?php esc_html_e('Thumbnail image', 'woocommerce'); ?></span></th>
                                    <th class="product-name">       <?php esc_html_e('Product', 'woocommerce'); ?></th>
                                    <th class="product-price">      <?php esc_html_e('Price', 'woocommerce'); ?></th>
                                    <th class="product-quantity">   <?php esc_html_e('Quantity', 'woocommerce'); ?></th>
                                    <th class="product-subtotal">   <?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
                                </tr>
                            </thead>

                            <tbody <?php echo esc_html('id=gcw-quote-tbody'); ?>>
                                <?php
                                foreach ($quote_items as $quote_item_key => $quote_item) {

                                    $product_id         = $quote_item['product_id'];
                                    $_product           = wc_get_product($product_id);
                                    $product_name       = get_the_title($product_id);
                                    $product_permalink  = $_product->get_permalink($quote_item);
                                    $customizations     = WC()->session->get("pcw_customizations_{$product_id}");
                                    $customizations_cost = $customizations['cost'] ?? 0;

                                ?>
                                    <tr <?php echo esc_html(sprintf('id=gcw-quote-row-item-%s', $product_id)); ?>>

                                        <td class="product-remove">
                                            <?php
                                            echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                'quote_item_remove_link',
                                                sprintf(
                                                    '<a class="gcw-button-remove" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"></a>',
                                                    // esc_url(wc_get_cart_remove_url($quote_item_key)),
                                                    /* translators: %s is the product name */
                                                    esc_attr(sprintf(__('Remover %s do orçamento', 'gestaoclick'), wp_strip_all_tags($product_name))),
                                                    esc_attr($product_id),
                                                    esc_attr($_product->get_sku())
                                                ),
                                                $quote_item_key
                                            );
                                            ?>
                                        </td>

                                        <td class="product-thumbnail">
                                            <?php
                                            

                                            if (is_array($customizations) && isset($customizations['images']))
                                            {
                                                $front_image = $customizations['images']['front'] ?? null;
                                                $back_image = $customizations['images']['back'] ?? null;

                                                echo '<img src="' . $front_image . '" alt="Front Image">';
                                                echo '<img src="' . $back_image . '" alt="Back Image">';
                                            } 
                                            else
                                            {
                                                $thumbnail = apply_filters('quote_item_thumbnail', $_product->get_image(), $quote_item, $quote_item_key);
                                                if (!$product_permalink) {
                                                    echo $thumbnail; // PHPCS: XSS ok.
                                                } else {
                                                    printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
                                                }
                                            }

                                            ?>
                                        </td>

                                        <td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
                                            <?php

                                            if (!$product_permalink) {
                                                echo wp_kses_post($product_name . '&nbsp;');
                                            } else {
                                                echo wp_kses_post(apply_filters('quote_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $quote_item, $quote_item_key));
                                            }

                                            do_action('quote_item_name', $quote_item, $quote_item_key);

                                            ?>
                                        </td>

                                        <td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
                                            <?php

                                            echo wc_price($_product->get_price() + $customizations_cost); // PHPCS: XSS ok.

                                            ?>
                                        </td>

                                        <td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
                                            <?php
                                            if ($_product->is_sold_individually()) {
                                                $min_quantity = 1;
                                                $max_quantity = 1;
                                            } else {
                                                $min_quantity = 0;
                                                $max_quantity = $_product->get_max_purchase_quantity();
                                            }

                                            $product_quantity = woocommerce_quantity_input(
                                                array(
                                                    'input_name'   => "gcw_quote_item_quantity[$product_id]",
                                                    'input_value'  => $quote_item['quantity'],
                                                    'max_value'    => $max_quantity,
                                                    'min_value'    => $min_quantity,
                                                    'product_name' => $product_name,
                                                ),
                                                $_product,
                                                false
                                            );

                                            echo apply_filters('cart_item_quantity', $product_quantity, $quote_item_key, $quote_item); // PHPCS: XSS ok.
                                            ?>
                                            <input type="hidden" name="gcw_quote_item_id[]" value="<?php echo esc_attr($product_id); ?>" />
                                        </td>

                                        <td class="product-subtotal" data-title="<?php esc_attr_e('Subtotal', 'woocommerce'); ?>">
                                            <?php
                                            $subtotal = ($_product->get_price() + $customizations_cost) * $quote_item['quantity']; // PHPCS: XSS ok.
                                            echo wc_price($subtotal);
                                            ?>
                                        </td>

                                    </tr>
                                <?php
                                }
                                ?>
                                <tr>
                                    <td class="actions" colspan="6">
                                        <button id="gcw-quote-update-button" type="submit" class="button" name="update_quote" value="<?php esc_attr_e('Atualizar orçamento', 'gestaoclick'); ?>"><?php esc_html_e('Atualizar orçamento', 'gestaoclick'); ?></button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </form>
                </div>

                <div id="gcw-quote-totals-container" class="woocommerce">

                    <h2>Total do orçamento</h2>
                    <div id="gcw_quote_totals_subtotal" class="gcw_quote_totals_section gcw_quote_space_between">
                        <span><?php echo esc_html_e('Subtotal', 'woocommerce'); ?></span>
                        <span><?php echo wc_price($quote_subtotal); ?></span>
                    </div>
                    <div id="gcw_quote_totals_shipping" class="gcw_quote_totals_section">
                        <p><?php echo esc_html_e('Shipping', 'woocommerce'); ?></p>
                        <div id="gcw_quote_shipping_options">
                            <?php echo WC()->session->get('shipping_options'); ?>
                        </div>
                        <div id="gcw_quote_shipping_address">
                            <?php
                            if (WC()->session->get('shipping_address_html')) {
                                echo '<p>' . esc_html(WC()->session->get('shipping_address_html')) . '</p>';
                            }
                            ?>
                        </div>
                        <form method="POST" id="gcw_quote_shipping_form">
                            <input type="text" id="shipping_postcode" name="shipping_postcode" placeholder="Digite seu CEP"
                                <?php
                                if (WC()->session->get('shipping_postcode')) {
                                    esc_attr_e(sprintf("value=%s", WC()->session->get('shipping_postcode')));
                                }
                                ?>
                            />
                            <button id="gcw-update-shipping-button" type="button" class="button">Calcular</button>
                        </form>
                    </div>
                    <div id="gcw_quote_totals_total" class="gcw_quote_totals_section gcw_quote_space_between">
                        <span><?php esc_html_e('Total', 'woocommerce'); ?></span>
                        <div id="gcw_quote_total_display"></div>
                    </div>
                    <div class="gcw_button_wrapper">
                        <button id="gcw_save_quote_button" class="gcw_button">Ir à finalização</button>
                    </div>

                </div>

            </div>
        <?php
        else : echo '<p>Nenhum item encontrado neste orçamento.</p>';
        endif;

        return ob_get_clean();
    }

    public function update_quote_quantities()
    {
        if (isset($_POST['update_quote']) && isset($_POST['gcw_quote_item_id']) && isset($_POST['gcw_quote_item_quantity'])) {
            $item_ids = $_POST['gcw_quote_item_id'];
            $quantities = $_POST['gcw_quote_item_quantity'];

            $quote_items = array();
            $quote_items_quantity = 0;

            foreach ($item_ids as $product_id) {
                if (isset($quantities[$product_id])) 
                {
                    $product_quantity = intval($quantities[$product_id]);
                    $quote_items[] = array
                    (
                        'product_id' => intval($product_id),
                        'quantity' => $product_quantity
                    );

                    $quote_items_quantity = $quote_items_quantity + $product_quantity;
                }
            }

            WC()->session->set('quote_items',           $quote_items);
            WC()->session->set('quote_items_quantity',  $quote_items_quantity);

            wc_add_notice(__('Orçamento atualizado com sucesso.', 'gestaoclick'), 'success');
        }
    }

    public function get_quote_subtotal($quote_items)
    {
        $subtotal = 0;
        foreach ($quote_items as $item) {
            $price = (int) wc_get_product($item['product_id'])->get_price();
            $customizations = WC()->session->get("pcw_customizations_{$item['product_id']}");
            $customizations_cost = $customizations['cost'] ?? 0;
            $subtotal += ($price + $customizations_cost) * $item['quantity'];
        }

        return $subtotal;
    }

    public function ajax_add_to_quote_variation()
    {
        if (isset($_POST['variation_id'])) {
            $parent_id      = sanitize_text_field($_POST['parent_id']);
            $variation_id   = sanitize_text_field($_POST['variation_id']);
            $quantity       = sanitize_text_field($_POST['quantity']);

            $this->add_item_to_quote($variation_id, $quantity, $parent_id);
        } else {
            wp_send_json_error('Não foi possível adicionar o item ao orçamento.');
        }
    }

    public function ajax_add_to_quote_simple()
    {
        if (isset($_POST['product_id'])) {
            $product_id = sanitize_text_field($_POST['product_id']);
            $quantity   = sanitize_text_field($_POST['quantity']);

            $this->add_item_to_quote($product_id, $quantity);
        } else {
            wp_send_json_error('Não foi possível adicionar o item ao orçamento.');
        }
    }

    function add_item_to_quote($product_id, $quantity, $parent_id = null)
    {
        // Obter os itens do orçamento da sessão
        $quote_items = WC()->session->get('quote_items') ? WC()->session->get('quote_items') : array();

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
        WC()->session->set('quote_items', $quote_items);

        $message = 'Produto adicionado ao orçamento com sucesso! <a href="' . esc_url(home_url('orcamento')) . '" class="button">Ver orçamento</a>';
        wc_add_notice($message, 'success');
        
        // Redireciona para a página do produto com uma mensagem de sucesso
        $redirect_url = get_permalink($parent_id ? $parent_id : $product_id);
        wp_send_json_success(array('redirect_url' => $redirect_url));
    }

    public function ajax_gcw_remove_quote_item()
    {
        if (isset($_POST['item_id'])) {
            $item_id = sanitize_text_field($_POST['item_id']);
            $items = WC()->session->get('quote_items');

            if (is_array($items)) {
                foreach ($items as $key => $item) {
                    if ($item['product_id'] == $item_id) {
                        unset($items[$key]);
                    }
                }
            }

            WC()->session->set('quote_items', $items);
        }
    }

    public function ajax_update_shipping()
    {
        if (isset($_POST['shipping_postcode'])) {
            // Desfaz a seleção do método de envio
            WC()->session->set('has_selected_shipping_method', null);

            $shipping_postcode = sanitize_text_field($_POST['shipping_postcode']);
            $quote_items = WC()->session->get('quote_items') ? WC()->session->get('quote_items') : array();

            WC()->session->set('shipping_postcode', $shipping_postcode);
            WC()->session->set('shipping_address_1', sanitize_text_field($_POST['shipping_address_1']));
            WC()->session->set('shipping_neighborhood', sanitize_text_field($_POST['shipping_neighborhood']));
            WC()->session->set('shipping_city', sanitize_text_field($_POST['shipping_city']));
            WC()->session->set('shipping_state', sanitize_text_field($_POST['shipping_state']));
            WC()->session->set('shipping_address_html', sanitize_text_field($_POST['shipping_address_html']));

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
                WC()->session->set('shipping_rates', $this->calculate_shipping_for_package($package));

                if (WC()->session->get('shipping_rates')) {
                    $html = '<form><ul>';
                    foreach (WC()->session->get('shipping_rates') as $rate) {
                        $html .= '<li><input class="gcw_shipping_method_radio" name="shipping_method" type="radio" data-method-id="' . esc_attr($rate->id) . '" ><label>'
                            . esc_html($rate->label) . ': ' . wc_price($rate->cost) .
                            '</label></li>';
                    }
                    $html .= '</ul></form>';
                    WC()->session->set('shipping_options', $html);
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
        if (isset($_POST['shipping_method_id'])) {
            WC()->session->set('has_selected_shipping_method', true);

            $shipping_method_id = sanitize_text_field($_POST['shipping_method_id']);
            $shipping_rates = WC()->session->get('shipping_rates');

            foreach ($shipping_rates as $rate) {
                if ($shipping_method_id == $rate->id) {
                    $shipping_cost = $rate->cost;
                    $shipping_rate = $rate;
                    break;
                }
            }

            // Calcula o custo total do orçamento (frete + subtotal)
            $quote_items = WC()->session->get('quote_items');
            $subtotal_price = 0;

            if (is_array($quote_items) && !empty($quote_items)) {
                foreach ($quote_items as $quote_item) {
                    $product_id = $quote_item['product_id'];
                    $_product = wc_get_product($product_id);
                    $customizations = WC()->session->get("pcw_customizations_{$product_id}");
                    $subtotal_price += ($_product->get_price() + $customizations['cost']) * $quote_item['quantity'];
                }
            }

            $total_price = $subtotal_price + $shipping_cost;
            WC()->session->set('quote_total_price', $total_price);
            WC()->session->set('quote_subtotal_price', $subtotal_price);
            WC()->session->set('quote_shipping_cost', $shipping_cost);
            WC()->session->set('quote_shipping_rate', $shipping_rate);

            wp_send_json_success(array('total_price_html'  => wc_price($total_price)));
        } else {
            wp_send_json_error(array('message' => 'Método de envio ou custo do frete não especificado.'));
        }
    }

    public function ajax_proceed_to_checkout()
    {
        if (!WC()->session->get('has_selected_shipping_method'))
        {
            wp_send_json_error(array('message' => 'Você precisa selecionar um método de envio.'));

            return;
        }

        if(WC()->session->get('quote_items_quantity') < get_option('gcw-settings-quote-minimum'))
        {
            wp_send_json_error(array('message' => sprintf('A quantidade mínima é de %d itens. Adicione mais itens ao seu orçamento.', get_option('gcw-settings-quote-minimum'))));

            return;
        }

        wp_send_json_success(array('redirect_url' => home_url('finalizar-orcamento')));
    }
}
