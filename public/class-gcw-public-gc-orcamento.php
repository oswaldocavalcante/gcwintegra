<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gcw-gc-api.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gcw-public-gc-cliente.php';

class GCW_Public_GC_Orcamento extends GCW_GC_Api {

    public function __construct() {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_orcamentos();
    }

    public function export($orcamento) {

        $gc_cliente_id  = $this->get_cliente_id($orcamento);
        $gc_products    = $this->get_items($orcamento);

        $body = array(
            'tipo'              => 'produto',
            'cliente_id'        => $gc_cliente_id,
            'situacao_id'       => get_option('gcw-settings-export-situacao'),
            'transportadora_id' => get_option('gcw-settings-export-trasportadora'),
            'nome_canal_venda'  => 'Internet',
            'produtos'          => $gc_products,
        );

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
            return new WP_Error( 'failed', __( 'GestãoClick: Error on export to GestãoClick.', 'gestaoclick' ) );
        }
    }

    // If a GestaoClick cliente_id exists, get it. Otherwise, export the new client and return his id from GestaoClick.
    public function get_cliente_id($orcamento) {

        $cpf_cnpj = $orcamento['gcw_cliente_cpf_cnpj'];

        $orcamento_cliente = array(
            'tipo_pessoa'   => strlen($cpf_cnpj) == 18 ? 'cnpj' : 'cpf',
            'cnpj'          => strlen($cpf_cnpj) == 18 ? $cpf_cnpj : '',
            'cpf'           => strlen($cpf_cnpj) == 14 ? $cpf_cnpj : '',
            'nome'          => $orcamento['gcw_cliente_nome'],
            'contatos' => array(
                'contato' => array(
                    'nome'      => $orcamento['gcw_contato_nome'],
                    'contato'   => $orcamento['gcw_contato_email'] . ' / ' . $orcamento['gcw_contato_telefone'],
                    'cargo'     => $orcamento['gcw_contato_cargo'],
                ),
            ),
        );

        $gc_cliente = new GCW_Public_GC_Cliente();
        return $gc_cliente->export( $orcamento_cliente );
    }

    public function get_items($orcamento) {

        $items = array();
        $item_id = 1;
        for($i = 6; $i < count($orcamento) ; $i=$i+4) {
            $items = array_merge($items, 
                array(
                    'produto' => array(
                        'nome_produto'  => $orcamento["gcw_item_nome-{$item_id}"] . ' - ' . $orcamento["gcw_item_descricao-{$item_id}"],
                        'detalhes'      => $orcamento["gcw_item_tamanho-{$item_id}"],
                        'quantidade'    => $orcamento["gcw_item_quantidade-{$item_id}"]
                    )
                )
            );
            ++$item_id;
        }

        return $items;
    }
}