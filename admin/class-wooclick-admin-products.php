<?php

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
            $this->save( $product );
        }

        $import_notice = sprintf('%d produtos importados com sucesso.', count($selectedProducts));
        set_transient('wooclick_import_notice', $import_notice, 30); // Ajuste o tempo conforme necessário
    }

    private function save( $product ) {

        $category_ids = []; //Needed for WooCommerce product data model
        $category_object = get_term_by( 'slug', sanitize_title($product['nome_grupo']), 'product_cat' );

        // Check if the category of the product exists, and add to $category_ids if is true
        if($category_object != false){
            array_push($category_ids, $category_object->term_id);
        }

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
}
