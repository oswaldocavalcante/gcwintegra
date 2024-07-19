<?php

/**
 * Reference for Products Variables: https://stackoverflow.com/questions/47518280/create-programmatically-a-woocommerce-product-variation-with-new-attribute-value
 *
 * @package    Gestaoclick
 * @subpackage Gestaoclick/integrations
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-api.php';

class GCW_WC_Products extends GCW_GC_Api {

    private $api_endpoint;
    private $api_headers;
    public $attributes_preset;

    public function __construct()
    {
        parent::__construct();
        $this->api_endpoint = parent::get_endpoint_items();
        $this->api_headers =  parent::get_headers();
        $this->attributes_preset = $this->get_attributes_preset();

        add_filter( 'gestaoclick_import_products', array( $this, 'import' ) );
    }

    public function fetch_api() 
    {
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

        update_option( 'gestaoclick-products', $products );
    }

    public function import( $products_codes ) 
    {
        if (!class_exists('WC_Product')) {
            include_once WC_ABSPATH . 'includes/abstracts/abstract-wc-product.php';
        }

        $products               = get_option( 'gestaoclick-products' );
        $products_blacklist     = get_option( 'gcw-settings-products-blacklist' );
        $categories_selection   = get_option( 'gcw-settings-categories-selection' );
        $products_selection     = array();

        if( $categories_selection ) {
            $filtered_categories = array_filter($products, function ($item) use ($categories_selection) {
                return (in_array($item['nome_grupo'], $categories_selection));
            });
            $products = $filtered_categories;
        }

        if( $products_blacklist ) {
            $filtered_products = array_filter($products, function ($item) use ($products_blacklist) {
                return (!in_array($item['codigo_barra'], $products_blacklist));
            });
            $products = $filtered_products;
        }

        if( is_array($products_codes) ) {
            $products_selection = array_filter($products, function ($item) use ($products_codes) {
                return (in_array($item['codigo_barra'], $products_codes));
            });
        } elseif( $products_codes == 'all' ) {
            $products_selection = $products;
        }

        foreach ($products_selection as $product_data) {

            // Save the product as simple or variable
            if ($product_data['possui_variacao'] == '1') {
                $product = $this->save($product_data);

                $attributes = [];
                $attributes[] = $this->get_product_variable_attributes($product_data['variacoes']);

                $product->set_attributes($attributes);
                $product->save();

                $this->save_product_variable_variations($product->get_id(), $product_data['variacoes']);
            } else {
                $product = $this->save($product_data);
            }

            $filters = $this->get_filters_attributes($product->get_name());
            $product->set_attributes(array_merge($product->get_attributes(), $filters));
            $product->save();
        }

        wp_admin_notice(sprintf('GestãoClick: %d produtos importados com sucesso.', count($products_selection)), array('type' => 'success', 'dismissible' => true));
    }

    private function get_category_id( $category_name ) 
    {
        $category_object = get_term_by('slug', sanitize_title($category_name), 'product_cat');

        if ($category_object != false) {
            $category_id = $category_object->term_id;
            return $category_id;
        } else {
            return false;
        }
    }

    private function save( $product_data )
    {
        $category_ids[] = $this->get_category_id($product_data['nome_grupo']);

        $product_props = array(
            'sku'           => $product_data['codigo_barra'],
            'name'          => $product_data['nome'],
            'regular_price' => $product_data['valor_venda'],
            'sale_price'    => $product_data['valor_venda'],
            'description'   => $product_data['descricao'],
            'stock_quantity'=> $product_data['estoque'],
            'date_created'  => $product_data['cadastrado_em'],
            'date_modified' => $product_data['modificado_em'],
            'description'   => $product_data['descricao'],
            'weight'        => $product_data['peso'],
            'length'        => $product_data['comprimento'],
            'width'         => $product_data['largura'],
            'height'        => $product_data['altura'],
            'manage_stock'  => (int) $product_data['movimenta_estoque'],
            'category_ids'  => $category_ids,
        );

        $product_exists = wc_get_product_id_by_sku($product_props['sku']);
        $product = null;

        if ($product_exists) {
            $product = wc_get_product($product_exists);
        } else {
            if( (int) $product_data['possui_variacao'] ) {
                $product = new WC_Product_Variable();
            } else{
                $product = new WC_Product_Simple();
            }
            $product->add_meta_data('gestaoclick_gc_product_id', (int) $product_data['id'], true);
        }

        $product->set_props($product_props);
        $product->save();

        return $product;
    }

    private function get_product_variable_attributes( $variations )
    {
        $attribute = new WC_Product_Attribute();
        $attribute->set_id(0);
        $attribute->set_name('Modelo');
        $attribute->set_visible(true);
        $attribute->set_variation(true);

        $options = array();
        foreach( $variations as $variation ) {
            array_push( $options, $variation['variacao']['nome'] );
        }
        $attribute->set_options($options);

        return $attribute;
    }

    private function save_product_variable_variations( $parent_product_id, $variations )
    {
        $parent_product = wc_get_product($parent_product_id);

        foreach ($variations as $variation_data) {

            $sku = $variation_data['variacao']['codigo'];
            $variation_id_exists = wc_get_product_id_by_sku($sku);
            $variation = null;

            if ($variation_id_exists) {
                $variation = wc_get_product($variation_id_exists);
            } else {
                $variation = new WC_Product_Variation();
                $variation->set_sku($variation_data['variacao']['codigo']);
                $variation->add_meta_data( 'gestaoclick_gc_variation_id', (int) $variation_data['variacao']['id'], true );
            }
            
            $variation->set_parent_id($parent_product_id);
            $variation->set_manage_stock($parent_product->get_manage_stock());
            $variation->set_stock_status($parent_product->get_manage_stock() ? '' : 'onbackorder');
            $variation->set_status('publish');
            $variation->set_price($variation_data['variacao']['valores'][0]['valor_venda']);
            $variation->set_regular_price($variation_data['variacao']['valores'][0]['valor_venda']);
            $variation->set_stock_quantity($variation_data['variacao']['estoque']);
            $variation->set_attributes(array('modelo' => $variation_data['variacao']['nome']));
            $variation->save();

            $parent_product->save();
        }
    }

    private function save_tags($product_id, $product_name)
    {
        $tags_selection = get_option('gcw-settings-subcategories-selection');
        $tags = array();
        $taxonomy = 'product_tag';

        // Get tags in product name parts
        $product_name_parts = explode(' - ', $product_name);
        foreach ($product_name_parts as $name_part) {
            $tag_candidates = explode('/', $name_part);
            foreach ($tag_candidates as $tag_candidate){
                if (in_array($tag_candidate, $tags_selection)) {
                    $tags[] = $tag_candidate;
                }
            }
        }

        foreach ($tags as $tag_name) {
            if(in_array($tag_name, $tags_selection)) {

                $tag = term_exists($tag_name, $taxonomy);
                if (!$tag) {
                    wp_insert_term($tag_name, $taxonomy);
                }

                wp_set_object_terms($product_id, $tag_name, $taxonomy, true);
            }
        }
    }

    // Get all preset of attributes in WooCommerce
    private function get_attributes_preset()
    {
        $taxonomies = wc_get_attribute_taxonomies();
        $attributes_selection = array();

        if ($taxonomies) {
            foreach ($taxonomies as $taxonomy) {
                if (taxonomy_exists(wc_attribute_taxonomy_name($taxonomy->attribute_name))) {
                    $terms = get_terms(array(
                        'taxonomy' => wc_attribute_taxonomy_name($taxonomy->attribute_name),
                        'hide_empty' => false,
                    ));
                    foreach ($terms as $term) {
                        $attributes_selection[] = $term->name;
                    }
                }
            }
        }

        return $attributes_selection;
    }

    private function get_filters_attributes($product_name)
    {
        // Get all preset of attributes in WooCommerce
        $attributes_selection = $this->attributes_preset;
        $attributes_names = array();
        $attributes = array();

        // Get filters attributes names in product name parts
        $product_name_parts = explode(' - ', $product_name);
        foreach ($product_name_parts as $name_part) {
            $attributes_candidates = explode('/', $name_part);
            foreach ($attributes_candidates as $attribute_candidate) {
                if (in_array($attribute_candidate, $attributes_selection)) {
                    $attributes_names[] = $attribute_candidate;
                }
            }
        }

        foreach ($attributes_names as $attribute_name) {
            if (in_array($attribute_name, $attributes_selection)) {

                $taxonomies = wc_get_attribute_taxonomies();
                $terms = array();

                if ($taxonomies) {
                    foreach ($taxonomies as $taxonomy) {
                        if (taxonomy_exists(wc_attribute_taxonomy_name($taxonomy->attribute_name))) {
                            $attribute = new WC_Product_Attribute();
                            $attribute->set_id(sizeof($attributes) + 1);
                            $attribute->set_name('pa_' . $taxonomy->attribute_name);
                            $attribute->set_visible(true);
                            $attribute->set_variation(false);

                            $options = array();
                            $terms = get_terms(array(
                                'taxonomy' => wc_attribute_taxonomy_name($taxonomy->attribute_name),
                                'hide_empty' => false,
                            ));
                            foreach ($terms as $term) {
                                if (in_array($term->name, $attributes_names)) {
                                    array_push($options, $term->term_id);
                                }
                            }
                            if ($options) {
                                $attribute->set_options($options);
                                $attributes[] = $attribute;
                            }
                        }
                    }
                }
            }
        }

        return $attributes;
    }
}