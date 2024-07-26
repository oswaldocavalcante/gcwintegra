<?php

class GCW_Shortcode_Quote_WooCommmerce
{
    public function render()
    {
        $this->update_quote_quantities();
        wc_print_notices();

        ob_start();
        $quote_id    = $this->get_quote_by_user_id(get_current_user_id())->ID;
        $quote_items = get_post_meta($quote_id, 'items', true);
        $quote_subtotal = $this->get_quote_subtotal($quote_items);

        if (is_array($quote_items) && !empty($quote_items)) :
?>
            <div id="gcw-quote-container">

                <form id="gcw-quote-form" class="woocommerce-cart-form" method="post" <?php echo esc_html(sprintf('data-quote_id=%s', $quote_id)) ?>>
                    <input type="hidden" name="gcw_quote_id" value="<?php echo esc_attr($quote_id); ?>" />
                    <table id="gcw-quote-woocommerce-table" class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">

                        <thead>
                            <tr>
                                <th class="product-remove"> <span class="screen-reader-text"><?php esc_html_e('Remove item', 'woocommerce'); ?></span></th>
                                <th class="product-thumbnail"> <span class="screen-reader-text"><?php esc_html_e('Thumbnail image', 'woocommerce'); ?></span></th>
                                <th class="product-name"> <?php esc_html_e('Product', 'woocommerce'); ?></th>
                                <th class="product-price"> <?php esc_html_e('Price', 'woocommerce'); ?></th>
                                <th class="product-quantity"> <?php esc_html_e('Quantity', 'woocommerce'); ?></th>
                                <th class="product-subtotal"> <?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
                            </tr>
                        </thead>

                        <tbody <?php echo esc_html('id=gcw-quote-tbody'); ?>>
                            <?php
                            foreach ($quote_items as $quote_item_key => $quote_item) {

                                $product_id         = $quote_item['product_id'];
                                $_product           = wc_get_product($product_id);
                                $product_name       = get_the_title($product_id);
                                $product_permalink  = $_product->get_permalink($quote_item);
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
                                        $thumbnail = apply_filters('quote_item_thumbnail', $_product->get_image(), $quote_item, $quote_item_key);

                                        if (!$product_permalink) {
                                            echo $thumbnail; // PHPCS: XSS ok.
                                        } else {
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
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

                                        // // Meta data.
                                        // echo quote_item_data($quote_item); // PHPCS: XSS ok.

                                        // Backorder notification.
                                        if ($_product->backorders_require_notification() && $_product->is_on_backorder($quote_item['quantity'])) {
                                            echo wp_kses_post(apply_filters('quote_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
                                        }
                                        ?>
                                    </td>

                                    <td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
                                        <?php
                                        echo $_product->get_price(); // PHPCS: XSS ok.
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
                                        echo apply_filters('cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $quote_item['quantity']), $quote_item, $quote_item_key); // PHPCS: XSS ok.
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

                <div id="gcw-quote-totals">
                    <h2>Total no orçamento</h2>
                    <div id="gcw_quote_totals_subtotal">
                        <?php echo esc_html_e('Subtotal:', 'woocommerce') . wc_price($quote_subtotal); ?>
                    </div>
                    <div id="gcw_quote_totals_shipping">
                        <?php echo esc_html_e('Shipping:', 'woocommerce'); ?>
                        <div id="gcw-quote-shipping-options"></div>
                        <form method="POST" id="gcw_quote_shipping_form">
                            <input type="text" id="shipping_postcode" name="shipping_postcode" placeholder="Digite seu CEP" />
                            <button id="gcw-update-shipping-button" type="button" class="button">Calcular Frete</button>
                        </form>
                    </div>
                    <div id="gcw_quote_totals_total">
                        <?php esc_html_e('Total:', 'woocommerce'); ?>
                        <?php
                            // TODO: Total = $subtotal + shipping_cost (de acordo com o método de envio escolhido pelo usuário)
                        ?>
                    </div>
                </div>

            </div>
<?php
        else :
            echo '<p>Nenhum item encontrado neste orçamento.</p>';
        endif;

        return ob_get_clean();
    }

    public function get_quote_by_user_id($user_id)
    {
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

        if (!empty($quotes)) {
            return $quotes[0]; // Retorna a primeira (e provavelmente única) cotação aberta
        }

        return null; // Nenhuma cotação encontrada
    }

    public function update_quote_quantities()
    {
        if (isset($_POST['update_quote'])) {
            $quote_id = intval($_POST['gcw_quote_id']);
            $item_ids = $_POST['gcw_quote_item_id'];
            $quantities = $_POST['gcw_quote_item_quantity'];

            $quote_items = array();

            foreach ($item_ids as $product_id) {
                if (isset($quantities[$product_id])) {
                    $quote_items[] = array(
                        'product_id' => intval($product_id),
                        'quantity' => intval($quantities[$product_id])
                    );
                }
            }

            update_post_meta($quote_id, 'items', $quote_items);

            // Adicione uma mensagem de sucesso
            wc_clear_notices();
            wc_add_notice(__('Orçamento atualizado com sucesso.', 'gestaoclick'), 'success');
        }
    }

    public function get_quote_subtotal($quote_items)
    {
        $subtotal = 0;
        foreach ($quote_items as $item) {
            $price = (int) wc_get_product($item['product_id'])->get_price();
            $subtotal += $price * $item['quantity'];
        }

        return $subtotal;
    }
}
