<?php

class Wooclick_Admin_Settings {

	private $api_access_token;
	private $api_secret_access_token;
    private $api_headers;

	private $api_endpoint_products;
    private $api_endpoint_categories;

    public function __construct($api_access_token = '', $api_secret_access_token = '') {
        
        $this->api_access_token = $api_access_token;
        $this->api_secret_access_token = $api_secret_access_token;
        $this->api_headers = array(
            'headers' => array (
                'Content-Type' =>           'application/json',
                'access-token' =>           $api_access_token,
                'secret-access-token' =>    $api_secret_access_token,
            ),
        );

        $this->api_endpoint_products =      'https://api.gestaoclick.com/api/produtos';
        $this->api_endpoint_categories =    'https://api.gestaoclick.com/api/grupos_produtos';
    }

    public function get_api_access_token() {
        return $this->api_access_token;
    }

    public function get_api_secret_access_token() {
        return $this->api_secret_access_token;
    }

    public function get_api_headers() {
        return $this->api_headers;
    }

    public function get_api_endpoint_products() {
        return $this->api_endpoint_products;
    }

    public function get_api_endpoint_categories() {
        return $this->api_endpoint_categories;
    }
}