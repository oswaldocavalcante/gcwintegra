<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'gestaoclick/class-gcw-gc-api.php';

class GCW_GC_Transportadoras extends GCW_GC_Api {

    private $default = null;

    public function __construct() {
        parent::__construct();
        $this->api_headers =    parent::get_headers();
        $this->api_endpoint =   parent::get_endpoint_transportadoras();
    }

    public function fetch_api() {
        $transportadoras = [];
        $proxima_pagina = 1;

        do {
            $body = wp_remote_retrieve_body( 
                wp_remote_get( $this->api_endpoint . '?pagina=' . $proxima_pagina, $this->api_headers )
            );

            $body_array = json_decode($body, true);
            $proxima_pagina = $body_array['meta']['proxima_pagina'];

            $transportadoras = array_merge( $transportadoras, $body_array['data'] );

        } while ( $proxima_pagina != null );

        return $transportadoras;
    }

    public function get_select_options() {
        $transportadoras = $this->fetch_api();
        $array_options = [];

        foreach ($transportadoras as $transportadora) {
            $array_options[$transportadora['id']] = $transportadora['nome'];
        }

        return $array_options;
    }
}