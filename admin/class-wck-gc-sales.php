<?php

class WCK_GC_Sales extends WCK_GC_Api {

    public function __construct() {
        parent::__construct();

        $this->api_headers =            parent::get_headers();
        $this->api_endpoint_sales =     parent::get_endpoint_sales();
        $this->api_endpoint_clients =   parent::get_endpoint_clients();

        add_action( 'woocommerce_new_order', array( $this, 'new_sell' ), 10, 2 );
    }

    public function new_sell( $order_id, $order ) {

        $client_id = $this->get_client_id( $order->get_customer_id() );
        $wc_date = $order->get_date_created();
        $wc_order_items = $order->get_items();
        $gc_produtos = [];

        foreach ($wc_order_items as $order_item) {
            $wc_product_id = $order_item->get_changes()['product_id'];
            $wc_product = wc_get_product($wc_product_id);
            $gc_product_id = $wc_product->get_meta('wooclick_gc_product_id');
            
            $wc_variation_id = $order_item->get_changes()['variation_id'];

            if($wc_variation_id) {
                $wc_variation = wc_get_product($wc_variation_id);
                $gc_variation_id = $wc_variation->get_meta('wooclick_gc_variation_id');

                $gc_produtos[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'variacao_id'   => $gc_variation_id,
                        'quantidade'    => $order_item->get_quantity(),
                        'valor_venda'   => $wc_product->get_price(),
                    )
                );
            } else {
                $gc_produtos[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'quantidade'    => $order_item->get_quantity(),
                        'valor_venda'   => $wc_product->get_price(),
                    )
                );
            }
        }

        $body = array(
            'tipo'              => 'produto',
            'cliente_id'        => $client_id,
            'data'              => $order->get_date_created()->date('Y-m-d'),
            'situacao_id'       => 809158, // TODO: change to get it dinamically
            'transportadora_id' => 154839, // TODO: change to get it dinamically
            'valor_frete'       => $order->get_shipping_total(),
            'nome_canal_venda'  => 'Internet',
            'produtos'          => $gc_produtos,
        );

        $response = wp_remote_post( 
            $this->api_endpoint_sales, 
            array_merge(
                $this->api_headers,
                array( 'body' => json_encode($body) ),
            ) 
        );
    }

    public function fetch_clients_api() {
        $clients = [];
        $proxima_pagina = 1;

        do {
            $body = wp_remote_retrieve_body( 
                wp_remote_get( $this->api_endpoint_clients . '?pagina=' . $proxima_pagina, $this->api_headers )
            );

            $body_array = json_decode($body, true);
            $proxima_pagina = $body_array['meta']['proxima_pagina'];

            $clients = array_merge( $clients, $body_array['data'] );

        } while ( $proxima_pagina != null );

        return $clients;
    }

    public function get_client_id( $wc_customer_id ) {

        $gc_all_clients = $this->fetch_clients_api();

        // Get an instance of the WC_Customer Object from the user ID
        $wc_customer = new WC_Customer( $wc_customer_id );
        $cpf = $wc_customer->get_meta('billing_cpf');
        $gc_client = null;

        foreach( $gc_all_clients as $client ) {
            if( $client['cpf'] == $cpf ) {
                $gc_client = $client;
            }
        }

        // If client existis, return its id; otherwise, create a new client in GestãoClick and return its id
        if( $gc_client ) {
            return $gc_client['id'];
        } else {

            $post_body = array(
                'tipo_pessoa'   => 'PF',
                'nome'          => $wc_customer->get_first_name() . ' ' . $wc_customer->get_last_name(),
                'cpf'           => $cpf,
                'email'         => $wc_customer->get_email(),
                'telefone'      => $wc_customer->get_billing_phone(),
                'enderecos'     => array(
                    'endereco'  => array(
                        'cep'           => $wc_customer->get_billing_postcode(),
                        'logradouro'    => $wc_customer->get_billing_address_1(),
                        'complemento'   => $wc_customer->get_billing_address_2(),
                        'pais'          => $wc_customer->get_billing_country(),
                        'nome_cidade'   => $wc_customer->get_billing_city(),
                        'estado'        => $wc_customer->get_billing_state(),
                    )
                ),
            );

            $response = wp_remote_post(
                $this->api_endpoint_clients,
                array_merge(
                    $this->api_headers,
                    array( 'body' => json_encode( $post_body ) )
                )
            );

            $gc_client_data = json_decode(wp_remote_retrieve_body( $response ), true);

            return $gc_client_data['data']['id'];
        }
    }
}