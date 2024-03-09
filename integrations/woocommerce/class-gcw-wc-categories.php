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

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-api.php';

class GCW_WC_Categories extends GCW_GC_Api {

    private $api_endpoint;
    private $api_headers;

    public function __construct() {
        parent::__construct();
        $this->api_endpoint = parent::get_endpoint_categories();
        $this->api_headers =  parent::get_headers();
        
        add_filter( 'gestaoclick_import_categories', array( $this, 'import' ) );
    }

    public function fetch_api() {
        $categories = [];
        $proxima_pagina = 1;

        do {
            $body = wp_remote_retrieve_body( 
                wp_remote_get( $this->api_endpoint . '?pagina=' . $proxima_pagina, $this->api_headers )
            );

            $body_array = json_decode($body, true);
            $proxima_pagina = $body_array['meta']['proxima_pagina'];

            $categories = array_merge( $categories, $body_array['data'] );

        } while ( $proxima_pagina != null );

        update_option( 'gestaoclick-categories', $categories );
    }

    public function import( $categories_ids ) {
        $categories =           get_option( 'gestaoclick-categories' );
        $categories_selection = get_option( 'gcw-settings-categories-selection' );
        $selected_categories = array();

        if( $categories_selection ) {
            $filtered_categories = array_filter($categories, function ($item) use ($categories_selection) {
                return (in_array($item['nome'], $categories_selection));
            });
            $categories = $filtered_categories;
        }

        // Filtering selected categories
        if (is_array($categories_ids)){
            $selected_categories = array_filter($categories, function ($item) use ($categories_ids) {
                return in_array($item['id'], $categories_ids);
            });
        } elseif ($categories_ids == 'all') {
            $selected_categories = $categories;
        }

        foreach ($selected_categories as $category ) {
            $this->save( $category );
        }

        wp_admin_notice(sprintf('GestãoClick: %d categorias importadas com sucesso.', count($selected_categories)), array('type' => 'success', 'dismissible' => true));
    }

    private function save( $category ) {
        $taxonomy = 'product_cat';
        $category_object = get_term_by( 'slug', sanitize_title($category['nome'] ), $taxonomy );

        if($category_object != false) { //If category already exists, update it
            wp_update_term(
                $category_object->term_id, 
                $taxonomy, 
                array(
                    'description' => $category['meta_descricao'],
                    'parent' => get_term_by( 'grupo_pai_id', $category['grupo_pai_id'], true ),
                )
            );
            update_term_meta(
                $category_object->term_id,
                'grupo_pai_id',
                $category['grupo_pai_id']
            );
        } else { //If category doesn't exist, create it
            $new_category = wp_insert_term( 
                $category['nome'], 
                $taxonomy,
                array(
                    'slug' => sanitize_title($category['nome']),
                )
            );
            add_term_meta(
                $new_category['term_id'],
                'grupo_pai_id',
                $category['grupo_pai_id'],
                true
            );
        }
    }
}