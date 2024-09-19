<?php

require_once 'class-gcw-gc-api.php';

class GCW_GC_Orcamento extends GCW_GC_Api {
    
    private $api_headers;
    private $api_endpoint;
    
    private $id = null;
    private $cliente_id = null;
    private $codigo = null;
    private $observacoes = 'As formas de pagamento são boleto ou PIX:'.PHP_EOL.'• 50% para início da produção.'.PHP_EOL.'• 50% para entrega do pedido.'.PHP_EOL.PHP_EOL.'O sistema confirma o pagamento em até 1 dia útil.';
    private $hash = '';
    private $url = '';
    private $data = array();

    /**
     * @param int $gc_cliente_id
     * @param array $wc_items
     * @param WC_Shipping_Rate $wc_shipping_rate
     */
    public function __construct($gc_cliente_id, $wc_items, $wc_shipping_rate) {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_orcamentos();
        $this->cliente_id   = $gc_cliente_id;
        // $rate_data = $wc_shipping_rate->get_meta_data(); // TODO: Obter a transportadora do GC a partir do CNPJ

        $this->data = array(
            'tipo'                  => 'produto',
            'cliente_id'            => $this->cliente_id,
            'situacao_id'           => '64800',
            'nome_canal_vendas'     => 'Website',
            'valor_frete'           => $wc_shipping_rate->get_cost(),
            'nome_transportadora'   => $wc_shipping_rate->get_meta_data()['company'],
            'produtos'              => $this->get_gc_produtos($wc_items),
            'condicao_pagamento'    => 'parcelado',
            'tipo_desconto'         => '%',
            'forma_pagamento_id'    => '331151',
            'numero_parcelas'       => '2',
            'observacoes'           => $this->observacoes . PHP_EOL . PHP_EOL . 'Método de Envio: ' . $wc_shipping_rate->get_label(),
        );
    }

    private function get_gc_produtos($wc_items)
    {
        $products = array();

        foreach ($wc_items as $wc_item) 
        {
            $wc_product     = wc_get_product($wc_item['product_id']);
            $quantity       = $wc_item['quantity'];
            $customizations_cost = $wc_item['customizations']['cost'] ? $wc_item['customizations']['cost'] : 0;

            if ($wc_product->get_parent_id())
            {
                $gc_product_id   = wc_get_product($wc_product->get_parent_id())->get_meta('gestaoclick_gc_product_id');
                $gc_variation_id = $wc_product->get_meta('gestaoclick_gc_variation_id');

                $products[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'variacao_id'   => $gc_variation_id,
                        'quantidade'    => $quantity,
                        'valor_venda'   => $wc_product->get_price() + $customizations_cost,
                    )
                );
            } 
            else 
            {
                $gc_product_id   = $wc_product->get_meta('gestaoclick_gc_product_id');
                $products[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'quantidade'    => $quantity,
                        'valor_venda'   => $wc_product->get_price() + $customizations_cost,
                    )
                );
            }
        }

        return $products;
    }

    public function export()
    {
        $response = wp_remote_post
        (
            $this->api_endpoint,
            array_merge($this->api_headers, ["body" => wp_json_encode($this->data)])
        );

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (is_array($response) && $response["code"] == 200)
        {
            $this->id           = $response["data"]["id"];
            $this->codigo       = $response["data"]["codigo"];
            $this->hash         = $response["data"]["hash"];
            $this->url          = 'https://gestaoclick.com/proposta/' . $this->hash;

            return $this->codigo;
        } 
        else {
            return false;
        }
    }

    public function update($field, $value)
    {
        $response = wp_remote_request(
            $this->api_endpoint . '/' . $this->id,
            array_merge(
                $this->api_headers,
                [
                    "method" => "PUT",
                    "body" => wp_json_encode(array_merge($this->data, array(
                        $field => $value,
                    )))
                ]
            )
        );

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (is_array($response) && $response["code"] == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function get_hash()
    {
        return $this->hash;
    }

    public function get_url()
    {        
        return $this->url;
    }
}
