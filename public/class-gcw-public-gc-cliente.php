<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gcw-gc-api.php';

class GCW_Public_GC_Cliente extends GCW_GC_Api {

    private $id = null;

    public function __construct() {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_clients();
    }

    public function get_id() {
        return $this->id;
    }

    public function export( $body ) {

        $response = wp_remote_post( 
            $this->api_endpoint, 
            array_merge(
                $this->api_headers,
                array( 'body' => json_encode($body) ),
            ) 
        );

        $response_body = json_decode(wp_remote_retrieve_body( $response ), true);

        if( is_array($response_body) && $response_body['code'] == 200 ) {
            $this->id = $response_body['data']['id'];
            return $this->id;
        } else {
            return new WP_Error( 'failed', __( 'GestãoClick: Error on export client to GestãoClick.', 'gestaoclick' ) );
        }
    }

    public function get_cliente_by_cpf_cnpj( $cpf_cnpj ) {

        $body = wp_remote_retrieve_body( 
            wp_remote_get( $this->api_endpoint . '?cpf_cnpj=' . $cpf_cnpj, $this->api_headers )
        );

        $body = json_decode( $body, true );

        if( is_array($body['data']) ) {
            $this->id = $body['data'][0]['id'];
            return $body['data'][0];
        } else {
            return false;
        }
    }
}