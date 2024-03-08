<?php

require_once 'class-gcw-gc-api.php';
require_once 'class-gcw-gc-cliente.php';

class GCW_GC_Venda extends GCW_GC_Api {

    private $api_headers;
    private $api_endpoint;

    private $cliente_id;
    private $data;
    private $valor_frete;
    private $produtos = array();

    public function __construct($wc_order_id) {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_sales();

        $order = wc_get_order($wc_order_id);
        $this->data         = $order->get_date_created()->date('Y-m-d');
        $this->valor_frete  = $order->get_shipping_total();

        $order_items = $order->get_items();
        foreach ($order_items as $order_item) {
            $wc_product_id = $order_item->get_changes()['product_id'];
            $wc_product = wc_get_product($wc_product_id);
            $gc_product_id = $wc_product->get_meta('gestaoclick_gc_product_id');

            $wc_variation_id = $order_item->get_changes()['variation_id'];
            if ($wc_variation_id) {
                $wc_variation = wc_get_product($wc_variation_id);
                $gc_variation_id = $wc_variation->get_meta('gestaoclick_gc_variation_id');

                $this->produtos[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'variacao_id'   => $gc_variation_id,
                        'quantidade'    => $order_item->get_quantity(),
                        'valor_venda'   => $wc_product->get_price(),
                    )
                );
            } else {
                $this->produtos[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'quantidade'    => $order_item->get_quantity(),
                        'valor_venda'   => $wc_product->get_price(),
                    )
                );
            }
        }

        // If a GestaoClick cliente_id exists, get it. Otherwise, export the new client and return his id from GestaoClick.
        $wc_customer_id = $order->get_customer_id();
        $wc_customer = new WC_Customer($wc_customer_id);
        $this->cliente_id = null;
        if ($wc_customer->get_meta('gestaoclick_gc_cliente_id')) {
            $this->cliente_id = $wc_customer->get_meta('gestaoclick_gc_cliente_id');
        } else {
            $gc_cliente = new GCW_GC_Cliente($wc_customer, 'woocommerce');
            $this->cliente_id = $gc_cliente->export();
            $wc_customer->add_meta_data('gestaoclick_gc_cliente_id', $this->cliente_id, true);
        }
    }

    public function export() { 
 
        $body = array(
            'tipo'              => 'produto',
            'cliente_id'        => $this->cliente_id,
            'data'              => $this->data,
            'situacao_id'       => get_option('gcw-settings-export-situacao'),
            'transportadora_id' => get_option('gcw-settings-export-trasportadora'),
            'valor_frete'       => $this->valor_frete,
            'nome_canal_venda'  => 'Internet',
            'produtos'          => $this->produtos,
        );

        wp_remote_post( 
            $this->api_endpoint, 
            array_merge(
                $this->api_headers,
                array( 'body' => wp_json_encode($body) ),
            ) 
        );
    }
}