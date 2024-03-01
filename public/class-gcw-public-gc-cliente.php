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
        $clientes = $this->fetch_api();

        foreach( $clientes as $cliente ) {
            if( ($body['cnpj'] != '') && ($body['cnpj'] == $cliente['cnpj']) ) {
                return $cliente['id'];
            }
            elseif( ($body['cpf'] != '') && ($body['cpf'] == $cliente['cpf']) ) {
                return $cliente['id'];
            }
        }

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

    public function fetch_api() {
        $clientes = [];
        $proxima_pagina = 1;

        do {
            $body = wp_remote_retrieve_body( 
                wp_remote_get( $this->api_endpoint . '?pagina=' . $proxima_pagina, $this->api_headers )
            );

            $body_array = json_decode($body, true);
            $proxima_pagina = $body_array['meta']['proxima_pagina'];

            $clientes = array_merge( $clientes, $body_array['data'] );

        } while ( $proxima_pagina != null );

        return $clientes;
    }
}