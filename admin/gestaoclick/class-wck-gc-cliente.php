<?php

include_once WP_PLUGIN_DIR . '/wooclick/admin/class-wck-gc-api.php';

class WCK_GC_Cliente extends WCK_GC_Api {

    private $id = null;

    public function __construct( $wc_customer ) {
        parent::__construct();
        $this->api_headers =    parent::get_headers();
        $this->api_endpoint =   parent::get_endpoint_clients();

        $this->export( $wc_customer );
    }

    public function export( $wc_customer ) {
        $body = array(
            'tipo_pessoa'   => 'PF',
            'nome'          => $wc_customer->get_first_name() . ' ' . $wc_customer->get_last_name(),
            'cpf'           => $wc_customer->get_meta('billing_cpf'),
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
            $this->api_endpoint, 
            array_merge(
                $this->api_headers,
                array( 'body' => json_encode($body) ),
            ) 
        );

        $response_body = json_decode(wp_remote_retrieve_body( $response ), true);

        if( $response_body['code'] == 200 ) {
            $this->id = $response_body['data']['id'];
            $wc_customer->add_meta_data( 'wooclick_gc_cliente_id', $this->id, true );
        } else {
            return new WP_Error( 'failed', __( 'WooClick: Error on export client to GestãoClick.', 'wooclick' ) );
        }
    }

    public function get_id() {
        if( $this->id == null ) {
            return false;
        }
        return $this->id;
    }
}