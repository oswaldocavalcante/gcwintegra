<?php

// Reference for Products Variables: https://stackoverflow.com/questions/47518280/create-programmatically-a-woocommerce-product-variation-with-new-attribute-value

/**
 * The admin-specific functionality of the plugin.
 *
 * Sync products to WooCommerce from GestãoClick API
 *
 * @package    Wooclick
 * @subpackage Wooclick/admin
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */
class Wooclick_Admin_Products {

    private $api_endpoint;
    private $api_headers;

    public function __construct( $api_endpoint, $api_headers ) {

        $this->api_endpoint = $api_endpoint;
        $this->api_headers = $api_headers;

        add_filter( 'wooclick_import_products', array( $this, 'import' ) );
    }

    public function fetch_api() {
        $products = [];
        $proxima_pagina = 1;

        do {
            $body = wp_remote_retrieve_body( 
                wp_remote_get( $this->api_endpoint . '?pagina=' . $proxima_pagina, $this->api_headers )
            );

            $body_array = json_decode($body, true);
            $proxima_pagina = $body_array['meta']['proxima_pagina'];

            $products = array_merge( $products, $body_array['data'] );

        } while ( $proxima_pagina != null );

        update_option( 'wooclick-products', $products );
    }

    public function import( $products_codes ) {
        if (!class_exists('WC_Product')) {
            include_once WC_ABSPATH . 'includes/abstracts/abstract-wc-product.php';
        }

        $products = get_option('wooclick-products');
        $selectedProducts = array();

        if(is_array($products_codes)){
            $selectedProducts = array_filter($products, function ($item) use ($products_codes) {
                return in_array($item['codigo_barra'], $products_codes);
            });
        } elseif($products_codes == 'all') {
            $selectedProducts = $products;
        }

        foreach ($selectedProducts as $product) {
            // Check if the product has variations
            if ($product['possui_variacao'] == '1') {
                $this->save_product_variable($product);
            } else {
                $this->save_product_simple($product);
            }
        }

        $import_notice = sprintf('%d produtos importados com sucesso.', count($selectedProducts));
        set_transient('wooclick_import_notice', $import_notice, 30); // Ajuste o tempo conforme necessário
    }

    private function save_product_simple($product) {
        $category_ids = $this->get_category_ids($product['nome_grupo']);

        $woocommerce_product_data = array(
            'sku' =>            $product['codigo_barra'],
            'name' =>           $product['nome'],
            'regular_price' =>  $product['valor_venda'],
            'sale_price' =>     $product['valor_venda'],
            'description' =>    $product['descricao'],
            'stock_quantity' => $product['estoque'],
            'date_created' =>   $product['cadastrado_em'],
            'date_modified' =>  $product['modificado_em'],
            'description' =>    $product['descricao'],
            'weight' =>         $product['peso'],
            'length' =>         $product['comprimento'],
            'width' =>          $product['largura'],
            'height' =>         $product['altura'],
            'category_ids' =>   $category_ids,
            'manage_stock' =>   'true',
            'backorders' =>     'notify',
        );

        $product_exists = wc_get_product_id_by_sku($woocommerce_product_data['sku']);

        if ($product_exists) {
            $product = wc_get_product($product_exists);
            $product->set_props($woocommerce_product_data);
            $product->save();
        } else {
            $product = new WC_Product_Simple();
            $product->set_props($woocommerce_product_data);
            $product->save();
        }
    }

    private function save_product_variable($product) {

        $category_ids = $this->get_category_ids($product['nome_grupo']);

        $woocommerce_product_data = array(
            'sku' =>            $product['codigo_barra'],
            'name' =>           $product['nome'],
            'regular_price' =>  $product['valor_venda'],
            'sale_price' =>     $product['valor_venda'],
            'price' =>          $product['valor_venda'],
            'description' =>    $product['descricao'],
            'stock_quantity' => $product['estoque'],
            'date_created' =>   $product['cadastrado_em'],
            'date_modified' =>  $product['modificado_em'],
            'description' =>    $product['descricao'],
            'weight' =>         $product['peso'],
            'length' =>         $product['comprimento'],
            'width' =>          $product['largura'],
            'height' =>         $product['altura'],
            'category_ids' =>   $category_ids,
            'manage_stock' =>   'true',
            'backorders' =>     'notify',
        );

        $product_exists = wc_get_product_id_by_sku($product['codigo_barra']);
        $product_variable = null;

        if($product_exists) {
            $product_variable = wc_get_product($product_exists);
            $product_variable->set_props($woocommerce_product_data);
            
        } else {
            $product_variable = new WC_Product_Variable();
            $product_variable->set_props($woocommerce_product_data);
        }
        
        $product_variable->save();

        $this->add_product_variations( $product_variable, $product['variacoes'] );
    }

    private function get_category_ids($category_name) {
        $category_ids = array();
        $category_object = get_term_by('slug', sanitize_title($category_name), 'product_cat');

        if ($category_object != false) {
            $category_ids[] = $category_object->term_id;
        }

        return $category_ids;
    }

    private function add_product_variations( $product, $variations ) {
        foreach ($variations as $variation_data) {

            $variation_post = array(
                'post_title'  => $product->get_name(),
                'post_name'   => 'product-'. $product->get_id() .'-variation',
                'post_status' => 'publish',
                'post_parent' => $product->get_id(),
                'post_type'   => 'product_variation',
                'guid'        => $product->get_permalink()
            );

            // Creating the product variation
            $variation_id = wp_insert_post( $variation_post );

            // Create product variation attribute
            $attribute_name = 'variation';
            $term_name = $variation_data['variacao']['nome'];
            $this->add_product_variation_attribute( $product->get_id(), $variation_id, $attribute_name, $term_name );

            $sku = $variation_data['variacao']['codigo'];
            $exists_variation_id = wc_get_product_id_by_sku($sku);
            $variation = null;

            if ($exists_variation_id) {
                $variation = wc_get_product($exists_variation_id);
            } else {
                $variation = new WC_Product_Variation( $variation_id );
                $variation->set_parent_id($product->get_id());
                $variation->set_sku($variation_data['variacao']['codigo']);
            }

            $variation->set_price($variation_data['variacao']['valores'][0]['valor_venda']);
            $variation->set_regular_price($variation_data['variacao']['valores'][0]['valor_venda']);
            $variation->set_sale_price($variation_data['variacao']['valores'][0]['valor_venda']);
            $variation->set_stock_quantity($variation_data['variacao']['estoque']);
            $variation->set_manage_stock(true);

            $variation->save();
        }
    }

    private function add_product_variation_attribute( $product_id, $variation_id, $attribute_name, $term_name) {
            $taxonomy = 'pa_'.$attribute_name; // The attribute taxonomy

            // If attribute doesn't exists we create it 
            $attribute_id = wc_attribute_taxonomy_id_by_name($attribute_name);
            if (!$attribute_id) {
                $attribute_args = array(
                    'name' => $attribute_name,
                    'slug' => sanitize_title($attribute_name),
                );
                $attribute_id = wc_create_attribute($attribute_args);
            }

            // If the value ($term_name) of attribute doesn't exist we create it
            if( ! term_exists( $term_name, $taxonomy ) )
                wp_insert_term( $term_name, $taxonomy ); // Create the term

            // $term = get_term_by('name', $term_name, $taxonomy ); // Get the term slug

            // // Get the post Terms names from the parent variable product.
            // $post_term_names = wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

            // // Check if the post term exist and if not we set it in the parent variable product.
            // if( ! in_array( $term_name, $post_term_names ) )
            //     wp_set_post_terms( $product_id, $term_name, $taxonomy, true );

            // // Set/save the attribute data in the product variation
            // update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term->slug );
    }

    // private function add_product_variation_attribute( $product_id, $variation_id, $attribute_name, $term_name) {
    //         $taxonomy = 'pa_'.$attribute_name; // The attribute taxonomy

    //         // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
    //         if( ! taxonomy_exists( $taxonomy ) ){
    //             register_taxonomy(
    //                 $taxonomy,
    //                 'product_variation',
    //                     array(
    //                         'hierarchical' => false,
    //                         'label' => ucfirst( $attribute_name ),
    //                         'query_var' => true,
    //                         'rewrite' => array( 'slug' => sanitize_title($attribute_name) ), // The base slug
    //                     ),
    //             );
    //         }

    //         if( ! term_exists( $term_name, $taxonomy ) )
    //             wp_insert_term( $term_name, $taxonomy ); // Create the term

    //         $term = get_term_by('name', $term_name, $taxonomy ); // Get the term slug

    //         // Get the post Terms names from the parent variable product.
    //         $post_term_names = wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

    //         // Check if the post term exist and if not we set it in the parent variable product.
    //         if( ! in_array( $term_name, $post_term_names ) )
    //             wp_set_post_terms( $product_id, $term_name, $taxonomy, true );

    //         // Set/save the attribute data in the product variation
    //         update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term->slug );
    // }

}