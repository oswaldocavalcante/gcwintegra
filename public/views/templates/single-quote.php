<?php
/*
Template Name: Full-width page layout
Template Post Type: post, page
*/

function enqueue_single_quote_styles()
{
    wp_enqueue_style('gcw-shortcode-checkout', GCW_URL . 'public/assets/css/gcw-shortcode-checkout.css', array(), GCW_VERSION, 'all');
    wp_enqueue_style('gcw-shortcode-quote', GCW_URL . 'public/assets/css/gcw-shortcode-quote.css', array(), GCW_VERSION, 'all');
    wp_enqueue_style('gcw-single-quote', GCW_URL . 'public/assets/css/gcw-single-quote.css', array(), GCW_VERSION, 'all');
}
add_action('wp_enqueue_scripts', 'enqueue_single_quote_styles');

// Impede o acesso se não for o autor ou não possua permissão
if (get_post_field('post_author', get_the_ID()) != get_current_user_id() && !current_user_can('manage_options'))
{
    wp_redirect(home_url()); // Página de erro ou redirecionamento
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();

        $quote_id = get_the_ID();
        $quote_items = get_post_meta($quote_id, 'items', true);

        if (is_array($quote_items) && !empty($quote_items)) :

            $total          = get_post_meta($quote_id, 'total', true);
            $quote_subtotal = get_post_meta($quote_id, 'subtotal', true);
            $shipping       = get_post_meta($quote_id, 'shipping', true);
            $status         = esc_attr(get_post_meta($quote_id, 'status', true));
            $tracking       = esc_attr(get_post_meta($quote_id, 'tracking', true));
            $gc_codigo      = get_post_meta($quote_id, 'gc_codigo', true);

?>

            <div id="gcw_quote_forms_container">

                <table id="gcw-quote-woocommerce-table" class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">

                    <thead>
                        <tr>
                            <th class="product-thumbnail"> <span class="screen-reader-text"><?php esc_html_e('Thumbnail image', 'woocommerce'); ?></span></th>
                            <th class="product-name"> <?php esc_html_e('Product', 'woocommerce'); ?></th>
                            <th class="product-quantity"> <?php esc_html_e('Quantity', 'woocommerce'); ?></th>
                        </tr>
                    </thead>

                    <tbody <?php echo esc_html('id=gcw-quote-tbody'); ?>>
                        <?php
                        foreach ($quote_items as $quote_item_key => $quote_item) :

                            $product_id         = $quote_item['product_id'];
                            $_product           = wc_get_product($product_id);
                            $product_name       = get_the_title($product_id);
                            $product_permalink  = $_product->get_permalink($quote_item);
                            $customizations     = $quote_item['customizations'];

                        ?>
                            <tr <?php echo esc_html(sprintf('id=gcw-quote-row-item-%s', $product_id)); ?>>

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
                                        if (!$product_permalink)
                                        {
                                            echo $thumbnail; // PHPCS: XSS ok.
                                        }
                                        else
                                        {
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
                                        }
                                    }

                                    ?>
                                </td>

                                <td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
                                    <?php

                                    if (!$product_permalink)
                                    {
                                        echo wp_kses_post($product_name . '&nbsp;');
                                    }
                                    else
                                    {
                                        echo wp_kses_post(sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()));
                                    }

                                    ?>
                                </td>

                                <td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
                                    <?php echo $quote_item['quantity'] ?>
                                </td>

                            </tr>
                        <?php

                        endforeach;
                        ?>

                    </tbody>
                </table>

            </div>

            <div id="gcw-quote-totals" style="width: fit-content;">

                <h2>Orçamento <?php echo esc_attr($gc_codigo); ?></h2>

                <section id="gcw_quote_totals_subtotal" class="gcw_quote_totals_section gcw_quote_space_between">
                    <span><?php echo esc_html('Data', 'gestaoclick'); ?></span>
                    <?php echo get_the_date(); ?>
                </section>

                <section id="gcw_quote_shipping_address" class="gcw_quote_totals_section">
                    <div class="gcw_quote_space_between">
                        <span><?php echo esc_html('Envio', 'gestaoclick'); ?></span>
                        <?php echo get_post_meta(get_the_ID(), 'tracking', true); ?>
                    </div>
                    <p><?php echo 'Endereço de envio'; ?></p>
                </section>

                <section id="gcw_quote_totals_total" class="gcw_quote_totals_section gcw_quote_space_between">
                    <span><?php echo esc_html('Situação', 'gestaoclick'); ?></span>
                    <?php echo get_post_meta(get_the_ID(), 'status', true); ?>
                </section>

                <section id="gcw_quote_totals_finish">
                    <a id="gcw_save_quote_button">Ver envio</a>
                </section>

            </div>

<?php

        else :
            echo '<p>Nenhum item encontrado neste orçamento.</p>';
        endif;

    endwhile;
else :
    echo '<p>Orçamento não encontrado.</p>';
endif;

get_footer();
