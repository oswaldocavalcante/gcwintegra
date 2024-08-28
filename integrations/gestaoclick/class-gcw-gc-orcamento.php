<?php

require_once 'class-gcw-gc-api.php';

class GCW_GC_Orcamento extends GCW_GC_Api {
    
    private $api_headers;
    private $api_endpoint;
    
    private $codigo = null;
    private $data = array();
    private $observacoes = 'As formas de pagamento são boleto ou PIX:'.PHP_EOL.'• 50% para início da produção.'.PHP_EOL.'• 50% para entrega do pedido.'.PHP_EOL.PHP_EOL.'O sistema confirma o pagamento em até 1 dia útil.';

    /**
     * @param int $gc_cliente_id
     * @param array $wc_items
     * @param WC_Shipping_Rate $wc_shipping_rate
     */
    public function __construct($gc_cliente_id, $wc_items, $wc_shipping_rate) {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_orcamentos();
        
        // $rate_data = $wc_shipping_rate->get_meta_data(); // TODO: Obter a transportadora do GC a partir do CNPJ

        $this->data = array(
            'tipo'                  => 'produto',
            'cliente_id'            => $gc_cliente_id,
            'situacao_id'           => get_option('gcw-settings-export-situacao'),
            'nome_canal_vendas'     => 'Website',
            'valor_frete'           => $wc_shipping_rate->get_cost(),
            'nome_transportadora'   => $wc_shipping_rate->get_meta_data()['company'],
            'produtos'              => $this->get_gc_produtos($wc_items),
            'observacoes'           => $this->observacoes . PHP_EOL . PHP_EOL . 'Método de Envio: ' . $wc_shipping_rate->get_label(),
        );
    }

    private function get_gc_produtos($wc_items)
    {
        $products = array();

        foreach ($wc_items as $wc_item) {
            $wc_product     = wc_get_product($wc_item['product_id']);
            $customizations = $wc_item['customizations'];
            $quantity       = $wc_item['quantity'];

            if ($wc_product->get_parent_id()) {
                $gc_product_id   = wc_get_product($wc_product->get_parent_id())->get_meta('gestaoclick_gc_product_id');
                $gc_variation_id = $wc_product->get_meta('gestaoclick_gc_variation_id');

                $products[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'variacao_id'   => $gc_variation_id,
                        'quantidade'    => $quantity,
                        'valor_venda'   => $wc_product->get_price() + $customizations['cost'],
                    )
                );
            } else {
                $gc_product_id   = $wc_product->get_meta('gestaoclick_gc_product_id');
                $products[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'quantidade'    => $quantity,
                        'valor_venda'   => $wc_product->get_price() + $customizations['cost'],
                    )
                );
            }
        }

        return $products;
    }

    public function export()
    {
        $response = wp_remote_post(
            $this->api_endpoint,
            array_merge($this->api_headers, ["body" => wp_json_encode($this->data)])
        );

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (is_array($response) && $response["code"] == 200) {
            $this->codigo = $response["data"]["codigo"];
            return $this->codigo;
        } else {
            return false;
        }
    }
}
