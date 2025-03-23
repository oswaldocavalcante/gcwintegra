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

if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once GCWI_ABSPATH . 'integrations/gestaoclick/class-gcwi-gc-api.php';
 
class GCWI_WC_Attributes extends GCWI_GC_Api
{

    private $api_endpoint;
    private $api_headers;

    public function __construct() 
    {
        parent::__construct();
        $this->api_endpoint = parent::get_endpoint_attributes();
        $this->api_headers =  parent::get_headers();
        
        add_filter('gcwi_import_attributes', array( $this, 'import'));
    }

    public function fetch_api() 
    {
        $attributes = [];
        $proxima_pagina = 1;

        do
        {
            $response = wp_remote_retrieve_body
            ( 
                wp_remote_get( $this->api_endpoint . '?pagina=' . $proxima_pagina, $this->api_headers )
            );

            $response = json_decode($response, true);

            if(is_array($response) && $response['code'] == 200)
            {
                $proxima_pagina = $response['meta']['proxima_pagina'];
                $attributes = array_merge($attributes, $response['data']);
            }
        } 
        while($proxima_pagina != null);

        return $attributes;
    }

    // public function import( $attributes_ids ) {
    //     $attributes = get_option('gcwi-attributes');
    //     $selected_attributes = array();

    //     // Filtering selected attributes
    //     if (is_array($attributes_ids)){
    //         $selected_attributes = array_filter($attributes, function ($item) use ($attributes_ids) {
    //             return in_array($item['id'], $attributes_ids);
    //         });
    //     } elseif ($attributes_ids == 'all') {
    //         $selected_attributes = $attributes;
    //     }

    //     foreach ($selected_attributes as $attribute ) {
    //         $this->save($attribute);
    //     }

    //     wp_admin_notice(sprintf('GestãoClick: %d atributos importados com sucesso.', count($selected_attributes)), array('type' => 'success', 'dismissible' => true));
    // }

    // private function save( $attribute_data ) {
    //     $attribute_name = $attribute_data['nome'];
    //     $attribute = get_term_by('name', $attribute_name, 'pa');

    //     if (!$attribute) {
    //         $attribute_args = array(
    //             'name' => $attribute_name,
    //             'slug' => sanitize_title($attribute_name),
    //         );
    //         $attribute_id = wc_create_attribute($attribute_args);
    //     } else {
    //         $attribute_id = $attribute->get_id();
    //         $attribute->name = $attribute_data['nome'];
    //         wp_update_term( $attribute_id, 'pa', array( 'name' => $new_name, 'slug' => sanitize_title( $new_name ) ) );
    //     }
    // }
}