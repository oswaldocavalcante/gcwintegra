<?php

require_once 'class-gcw-gc-api.php';

class GCW_GC_Cliente extends GCW_GC_Api
{
    private $id = null;

    private $api_headers;
    private $api_endpoint;

    /**
     * Stores client data.
     * @var array
     */
    private $data = array
    (
        'tipo_pessoa'   => '',
        'nome'          => '',
        'cpf'           => '',
        'cnpj'          => '',
        'email'         => '',
        'telefone'      => '',
        'contatos'      => array
        (
            'contato'   => array
            (
                'nome'          => '',
                'cargo'         => '',
                'observacao'    => ''
            )
        ),
        'enderecos'     => array
        (
            'endereco'  => array
            (
                'cep'           => '',
                'logradouro'    => '',
                'numero'        => '',
                'complemento'   => '',
                'bairro'        => '',
                'pais'          => '',
                'nome_cidade'   => '',
                'estado'        => '',
            )
        ),
    );

    /** 
     * @param WC_Customer | array  $wc_customer Data core to create a new client and export to GestãoClick
     * @param string $context Context to select which way the client should be created
    */
    public function __construct($wc_customer, $context = 'order' | 'form' | 'quote') 
    {
        parent::__construct();
        $this->api_headers =    parent::get_headers();
        $this->api_endpoint =   parent::get_endpoint_clients();

        if($context == 'order') 
        {
            $this->data = array
            (
                'tipo_pessoa'   => 'PF',
                'nome'          => $wc_customer->get_first_name() . ' ' . $wc_customer->get_last_name(),
                'cpf'           => $wc_customer->get_meta('billing_cpf'),
                'email'         => $wc_customer->get_email(),
                'telefone'      => $wc_customer->get_billing_phone(),
                'enderecos'     => array
                (
                    'endereco'  => array
                    (
                        'cep'           => $wc_customer->get_billing_postcode(),
                        'logradouro'    => $wc_customer->get_billing_address_1(),
                        'numero'        => $wc_customer->get_meta('billing_number'),
                        'complemento'   => $wc_customer->get_billing_address_2(),
                        'bairro'        => $wc_customer->get_meta('billing_neighborhood'),
                        'pais'          => $wc_customer->get_billing_country(),
                        'nome_cidade'   => $wc_customer->get_billing_city(),
                        'estado'        => $wc_customer->get_billing_state(),
                    )
                ),
            );
        } 
        elseif ($context == 'quote') 
        {
            $this->data = array
            (
                'tipo_pessoa'   => 'PJ',
                'nome'          => $wc_customer->get_billing_company(),
                'cnpj'          => $wc_customer->get_meta('billing_cnpj'),
                'email'         => $wc_customer->get_billing_email(),
                'telefone'      => $wc_customer->get_billing_phone(),
                'contatos'      => array
                (
                    'contato'   => array
                    (
                        'nome'          => $wc_customer->get_billing_first_name() . $wc_customer->get_billing_last_name(),
                        'contato'       => $wc_customer->get_billing_phone(),
                        'observacao'    => $wc_customer->get_billing_email(),
                    )
                ),
                'enderecos'     => array
                (
                    'endereco'  => array
                    (
                        'cep'           => $wc_customer->get_billing_postcode(),
                        'logradouro'    => $wc_customer->get_billing_address_1(),
                        'complemento'   => $wc_customer->get_billing_address_2(),
                        'numero'        => $wc_customer->get_meta('billing_number'),
                        'bairro'        => $wc_customer->get_meta('billing_neighborhood'),
                        'pais'          => $wc_customer->get_billing_country(),
                        'nome_cidade'   => $wc_customer->get_billing_city(),
                        'estado'        => $wc_customer->get_billing_state(),
                    )
                ),
            );
        }
    }

    public function export() 
    {
        if ($this->get_cliente_by_cpf_cnpj($this->data['cpf']) || $this->get_cliente_by_cpf_cnpj($this->data['cnpj'])) {
            return $this->id;
        }

        $response = wp_remote_post
        ( 
            $this->api_endpoint, 
            array_merge
            (
                $this->api_headers,
                array( 'body' => wp_json_encode($this->data) ),
            ) 
        );

        $response_body = json_decode(wp_remote_retrieve_body( $response ), true);

        if( $response_body['code'] == 200 ) 
        {
            $this->id = $response_body['data']['id'];
            return $this->id;
        } 
        else {
            return false;
        }
    }

    public function get_cliente_by_id($id) 
    {
        $body = wp_remote_retrieve_body(
            wp_remote_get($this->api_endpoint . '?id=' . $id, $this->api_headers)
        );

        $body = json_decode($body, true);
        
        if($body['code'] == 200) {
            return $body['data'];
        } else {
            return false;
        }
    }

    public function get_cliente_by_cpf_cnpj($cpf_cnpj) 
    {
        if ($cpf_cnpj == '') return false;

        $body = wp_remote_retrieve_body(
            wp_remote_get($this->api_endpoint . '?cpf_cnpj=' . $cpf_cnpj, $this->api_headers)
        );

        $body = json_decode($body, true);

        if (is_array($body['data'])) 
        {
            $this->id = $body['data'][0]['id'];
            return $body['data'][0];
        } 
        else {
            return false;
        }
    }
}