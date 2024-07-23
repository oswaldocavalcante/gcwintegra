<?php
/*
Template Name: Full-width page layout
Template Post Type: post, page, product
*/
get_header();

// Inicia o loop do WordPress
if (have_posts()) :
    while (have_posts()) : the_post();
        // Obtém o ID do post atual
        $quote_id = get_the_ID();

        // Recupera os metadados do orçamento
        $items = get_post_meta($quote_id, 'items', true);

        // Verifica se há itens e os exibe
        if (is_array($items) && !empty($items)) :
            echo '<h1>Itens do Orçamento</h1>';
            echo '<ul>';
            foreach ($items as $item) {
                // Recupera o ID do produto e a quantidade
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];

                // Recupera o título do produto
                $product_title = get_the_title($product_id);

                // Exibe os detalhes do item
                echo '<li>' . esc_html($product_title) . ' - Quantidade: ' . esc_html($quantity) . '</li>';
            }
            echo '</ul>';
        else :
            echo '<p>Nenhum item encontrado neste orçamento.</p>';
        endif;

    endwhile;
else :
    echo '<p>Orçamento não encontrado.</p>';
endif;

get_footer();
