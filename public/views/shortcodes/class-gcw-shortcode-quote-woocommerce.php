<?php

class GCW_Shortcode_Quote
{
    public function render_quote()
    {
        ob_start();

        $quote_id = $this->get_quote_by_user_id(get_current_user_id())->ID;
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

        return ob_get_clean();
    }

    function get_quote_by_user_id($user_id)
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
