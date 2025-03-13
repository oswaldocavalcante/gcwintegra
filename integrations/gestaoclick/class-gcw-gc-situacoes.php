<?php

require_once 'class-gcw-gc-api.php';

class GCW_GC_Situacoes extends GCW_GC_Api {

    private $api_headers;
    private $api_endpoint;

    public function __construct() {
        parent::__construct();
        $this->api_headers =    parent::get_headers();
        $this->api_endpoint =   parent::get_endpoint_situacoes();
    }

    public function fetch_api() 
    {
        $situacoes = [];
        $proxima_pagina = 1;

        do 
        {
            $body = wp_remote_retrieve_body
            ( 
                wp_remote_get( $this->api_endpoint . '?pagina=' . $proxima_pagina, $this->api_headers )
            );

            $body_array = json_decode($body, true);
            if (!is_array($body_array)) return false;

            $proxima_pagina = $body_array['meta']['proxima_pagina'];
            $situacoes = array_merge( $situacoes, $body_array['data'] );

        } 
        while ($proxima_pagina != null);

        return $situacoes;
    }

    public function get_options_for_settings() 
    {
        $situacoes = $this->fetch_api();
        if (!$situacoes) return false;

        $array_options = [];

        foreach ($situacoes as $situacao) 
        {
            $array_options[$situacao['id']] = $situacao['nome'];
        }

        return $array_options;
    }
}