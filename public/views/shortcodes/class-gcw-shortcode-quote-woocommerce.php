<?php

class GCW_Shortcode_Quote_WooCommmerce
{
    public function render()
    {
        ob_start();
        $quote_id    = $this->get_quote_by_user_id(get_current_user_id())->ID;
        $quote_items = get_post_meta($quote_id, 'items', true);

        if (is_array($quote_items) && !empty($quote_items)) :
?>
            <div id="gcw-quote-woocommerce-container">

                <form id="gcw-quote-woocommerce-form" class="woocommerce-cart-form" action="<?php echo esc_url(home_url()); ?>" method="post">

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

                        <tbody>
                            <?php
                            foreach ($quote_items as $quote_item_key => $quote_item) {

                                $product_id         = $quote_item['product_id'];
                                $_product           = wc_get_product($product_id);
                                $product_name       = get_the_title($product_id);
                                $product_permalink  = $_product->get_permalink($quote_item);
                            ?>
                                <tr class="woocommerce-cart-form__cart-item">

                                    <td class="product-remove">
                                        <?php
                                        echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            'quote_item_remove_link',
                                            sprintf(
                                                '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"></a>',
                                                esc_url(wc_get_cart_remove_url($quote_item_key)),
                                                /* translators: %s is the product name */
                                                esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
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
                                                'input_name'   => "quote[{$quote_item_key}][qty]",
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
                                <td colspan="6" class="actions">
                                    <button id="gcw-quote-woocommerce-form-update-button" type="submit" class="button" name="update_quote" value="<?php esc_attr_e('Atualizar orçamento', 'gestaoclick'); ?>"><?php esc_html_e('Atualizar orçamento', 'gestaoclick'); ?></button>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </form>

                <div id="gcw-quote-woocommerce-totals">
                    <h2>Total no orçamento</h2>
                    <p>Subtotal</p>
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
}
