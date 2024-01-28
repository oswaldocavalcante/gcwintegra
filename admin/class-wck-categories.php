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
class WCK_Categories extends WCK_GC_Api {

    private $api_endpoint;
    private $api_headers;

    public function __construct() {
        parent::__construct();

        $this->api_endpoint = parent::get_endpoint_categories();
        $this->api_headers =  parent::get_headers();
        
        add_filter( 'wooclick_import_categories', array( $this, 'import' ) );
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

        update_option( 'wooclick-categories', $categories );
    }

    public function import( $categories_ids ) {
        $categories =           get_option('wooclick-categories');
        $categories_blacklist = get_option( 'wck-settings-blacklist-categories' );
        $selectedCategories = array();

        if( $categories_blacklist ) {
            $filteredCategories = array_filter($categories, function ($item) use ($categories_blacklist) {
                return (!in_array($item['nome'], $categories_blacklist));
            });
            $categories = $filteredCategories;
        }

        // Filtering selected categories
        if (is_array($categories_ids)){
            $selectedCategories = array_filter($categories, function ($item) use ($categories_ids) {
                return in_array($item['id'], $categories_ids);
            });
        } elseif ($categories_ids == 'all') {
            $selectedCategories = $categories;
        }

        foreach ($selectedCategories as $category ) {
            $this->save( $category );
        }

        $import_notice = sprintf('%d categorias importadas com sucesso.', count($selectedCategories));
        set_transient('wooclick_import_notice', $import_notice, 30); 
    }

    private function save( $category ) {
        
        $taxonomy = 'product_cat';

        $category_object = get_term_by( 'slug', sanitize_title($category['nome'] ), 'product_cat' );

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

    public function display(){
        $this->fetch_api();
        require_once 'partials/wooclick-admin-display-categories.php';
    }
}