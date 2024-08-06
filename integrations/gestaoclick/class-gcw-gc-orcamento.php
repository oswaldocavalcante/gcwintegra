<?php

require_once 'class-gcw-gc-api.php';

class GCW_GC_Orcamento extends GCW_GC_Api {
    
    private $api_headers;
    private $api_endpoint;
    
    private $id = null;
    private $data = array();

    /**
     * @param WC_Package $package
     */
    public function __construct($data, $cliente_id, $context = 'form' | 'quote') {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_orcamentos();

        if($context == 'form') {
            $products[] = $this->get_form_items($data);
            $this->data = array(
                "tipo"              => "produto",
                "cliente_id"        => $cliente_id,
                "situacao_id"       => get_option("gcw-settings-export-situacao"),
                "nome_canal_venda"  => "Internet",
                "produtos"          => $products,
            );
        } elseif($context == 'quote')
        {
            $products = $this->get_quote_items($data);
            $this->data = array(
                'tipo'              => 'produto',
                'cliente_id'        => $cliente_id,
                'situacao_id'       => get_option('gcw-settings-export-situacao'),
                'nome_canal_vendas' => 'Internet',
                'produtos'          => $products,
            );
        }
    }

    public function export(){

        $response = wp_remote_post(
            $this->api_endpoint,
            array_merge($this->api_headers, ["body" => wp_json_encode($this->data)])
        );

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (is_array($response) && $response["code"] == 200) {
            $this->id = $response["data"]["id"];
            return $this->id;
        } else {
            return false;
        }
    }

    private function get_quote_items($quote_items)
    {
        $products = array();

        foreach ($quote_items as $product) {
            $wc_product = wc_get_product($product['product_id']);
            $quantity   = $product['quantity'];

            // $gc_variation_id = $product_data['variation_id'];
            if ($wc_product->get_parent_id()) {
                $gc_product_id   = wc_get_product($wc_product->get_parent_id())->get_meta('gestaoclick_gc_product_id');
                $gc_variation_id = $wc_product->get_meta('gestaoclick_gc_variation_id');

                $products[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'variacao_id'   => $gc_variation_id,
                        'quantidade'    => $quantity,
                        'valor_venda'   => $wc_product->get_price(),
                    )
                );
            } else {
                $gc_product_id   = $wc_product->get_meta('gestaoclick_gc_product_id');
                $products[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'quantidade'    => $quantity,
                        'valor_venda'   => $wc_product->get_price(),
                    )
                );
            }
        }

        return $products;
    }

    private function get_form_items($orcamento){
        $items = [];
        $item_id = 1;
        for ($i = 6; $i < count($orcamento); $i = $i+4) {
            if(array_keys($orcamento)[$i] == "gcw_item_nome-{$item_id}") {
                array_push($items, array(
                    "produto" => [
                        "nome_produto"  =>  sanitize_text_field($orcamento["gcw_item_nome-{$item_id}"]) . " - " .
                                            sanitize_text_field($orcamento["gcw_item_descricao-{$item_id}"]),
                        "detalhes"      =>  sanitize_text_field($orcamento["gcw_item_tamanho-{$item_id}"]),
                        "quantidade"    =>  sanitize_text_field($orcamento["gcw_item_quantidade-{$item_id}"]),
                    ],
                ));

                ++$item_id;
            }
        }

        return $items;
    }
}
