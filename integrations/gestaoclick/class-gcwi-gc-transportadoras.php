<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once 'class-gcwi-gc-api.php';

class GCWI_GC_Transportadoras extends GCWI_GC_API 
{
    private $api_headers;
    private $api_endpoint;

    public function __construct()
    {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_transportadoras();
    }

    public function fetch_all() 
    {
        $transportadoras = [];
        $proxima_pagina = 1;

        do 
        {
            $body = wp_remote_retrieve_body
            ( 
                wp_remote_get($this->api_endpoint . '?pagina=' . $proxima_pagina, $this->api_headers)
            );

            $body_array = json_decode($body, true);
            if(!is_array($body_array)) return false;

            $proxima_pagina = $body_array['meta']['proxima_pagina'];
            $transportadoras = array_merge($transportadoras, $body_array['data']);
        }
        while ($proxima_pagina != null);

        return $transportadoras;
    }

    public function get_options_for_settings() 
    {
        $transportadoras = $this->fetch_all();
        if(!$transportadoras) return false;
        
        $array_options = [];

        foreach ($transportadoras as $transportadora) 
        {
            $array_options[$transportadora['id']] = $transportadora['nome'];
        }

        return $array_options;
    }
}