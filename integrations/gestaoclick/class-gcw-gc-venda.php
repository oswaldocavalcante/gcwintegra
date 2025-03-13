<?php

require_once 'class-gcw-gc-api.php';
require_once 'class-gcw-gc-cliente.php';

class GCW_GC_Venda extends GCW_GC_Api 
{
    private $api_headers;
    private $api_endpoint;

    private $wc_order_id;
    private $cliente_id;
    private $data;
    private $valor_frete;
    private $transportadora;
    private $desconto_valor;
    private $produtos = array();

    public function __construct($wc_order_id)
    {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_vendas();

        $this->wc_order_id = $wc_order_id;
        $order = wc_get_order($this->wc_order_id);
        $this->desconto_valor = $order->get_total_fees() < 0 ? abs($order->get_total_fees()) : '0';
        $this->data           = $order->get_date_created()->date('Y-m-d');
        $this->valor_frete    = $order->get_shipping_total();
        $this->transportadora = $this->valor_frete == 0 ? '' : get_option('gcw-settings-export-trasportadora');

        foreach ($order->get_items() as $order_item) 
        {
            $order_item_data    = $order_item->get_data();
            $wc_product_id      = $order_item_data['product_id'];
            $wc_product         = wc_get_product($wc_product_id);
            $gc_product_id      = $wc_product->get_meta('gestaoclick_gc_product_id');

            $wc_variation_id = $order_item_data['variation_id'];
            if ($wc_variation_id) 
            {
                $wc_variation       = wc_get_product($wc_variation_id);
                $gc_variation_id    = $wc_variation->get_meta('gestaoclick_gc_variation_id');

                $this->produtos[] = array
                (
                    'produto' => array
                    (
                        'produto_id'            => $gc_product_id,
                        'variacao_id'           => $gc_variation_id,
                        'quantidade'            => $order_item->get_quantity(),
                        'valor_venda'           => $wc_product->get_price(),
                    )
                );
            } 
            else 
            {
                $this->produtos[] = array
                (
                    'produto' => array
                    (
                        'produto_id'            => $gc_product_id,
                        'quantidade'            => $order_item->get_quantity(),
                        'valor_venda'           => $wc_product->get_price(),
                    )
                );
            }
        }

        $wc_customer_id     = $order->get_customer_id();
        $wc_customer        = new WC_Customer($wc_customer_id);
        $this->cliente_id   = null;
        
        // If a GestaoClick cliente_id exists, get it. Otherwise, export the new client and return his id from GestaoClick.
        if ($wc_customer->get_meta('gestaoclick_gc_cliente_id'))
        {
            $this->cliente_id = $wc_customer->get_meta('gestaoclick_gc_cliente_id');
        } 
        else
        {
            $gc_cliente = new GCW_GC_Cliente($wc_customer);
            $this->cliente_id = $gc_cliente->export();
            $wc_customer->add_meta_data('gestaoclick_gc_cliente_id', $this->cliente_id, true);
        }
    }

    public function get()
    {
        $order = wc_get_order($this->wc_order_id);
        $id = $order->get_meta('gcw_gc_venda_id');

        $body = wp_remote_retrieve_body
        (
            wp_remote_get($this->api_endpoint . '?id=' . $id, $this->api_headers)
        );

        $body = json_decode($body, true);

        if ($body['code'] == 200) 
        {
            if (is_array($body['data'])) 
            {
                return $body['data'][0];
            }
            else 
            {
                return new WP_Error($body['code'], $body['data']);
            }
        }
        else 
        {
            return new WP_Error($body['code'], $body['data']);
        }
    }

    public function export()
    {
        $body = array
        (
            'tipo'              => 'produto',
            'cliente_id'        => $this->cliente_id,
            'data'              => $this->data,
            'situacao_id'       => get_option('gcw-settings-export-situacao'),
            'transportadora_id' => $this->transportadora,
            'valor_frete'       => $this->valor_frete,
            'nome_canal_venda'  => 'Internet',
            'produtos'          => $this->produtos,
            'desconto_valor'    => $this->desconto_valor,
        );

        $response = wp_remote_post
        ( 
            $this->api_endpoint, 
            array_merge
            (
                $this->api_headers,
                array('body' => wp_json_encode($body)),
            ) 
        );

        // Associates WC Order with GC Order through metadata
        $this->add_wc_order_metadata(json_decode($response['body']));
    }

    public function add_wc_order_metadata($gc_data)
    {
        $wc_order = wc_get_order($this->wc_order_id);
        $wc_order->add_meta_data('gcw_gc_venda_id',             $gc_data->data->id, true);
        $wc_order->add_meta_data('gcw_gc_venda_codigo',         $gc_data->data->codigo, true);
        $wc_order->add_meta_data('gcw_gc_venda_hash',           $gc_data->data->hash, true);
        $wc_order->save();
    }
}