<?php

require_once plugin_dir_path(dirname(__FILE__)) . 'gestaoclick/class-gcw-gc-api.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'gestaoclick/class-gcw-gc-cliente.php';

class GCW_GC_Venda extends GCW_GC_Api {

    private $api_headers;
    private $api_endpoint;

    public function __construct() {
        parent::__construct();
        $this->api_headers =    parent::get_headers();
        $this->api_endpoint =   parent::get_endpoint_sales();
    }

    public function export( $order_id ) { 
        $order = wc_get_order( $order_id );
        $order_items = $order->get_items();
        $gc_products = [];

        foreach ($order_items as $order_item) {
            $wc_product_id = $order_item->get_changes()['product_id'];
            $wc_product = wc_get_product($wc_product_id);
            $gc_product_id = $wc_product->get_meta('gestaoclick_gc_product_id');
            
            $wc_variation_id = $order_item->get_changes()['variation_id'];
            if($wc_variation_id) {
                $wc_variation = wc_get_product($wc_variation_id);
                $gc_variation_id = $wc_variation->get_meta('gestaoclick_gc_variation_id');

                $gc_products[] = array(
                    'produto' => array(
                        'produto_id'    => $gc_product_id,
                        'variacao_id'   => $gc_variation_id,
                        'quantidade'    => $order_item->get_quantity(),
                        'valor_venda'   => $wc_product->get_price(),
                    )
                );
            } else {
                $gc_products[] = array(
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
        $wc_customer = new WC_Customer( $wc_customer_id );
        $gc_cliente_id = null;
        if( $wc_customer->get_meta('gestaoclick_gc_cliente_id') ) {
            $gc_cliente_id = $wc_customer->get_meta('gestaoclick_gc_cliente_id');
        } else {
            $gc_cliente = new GCW_GC_Cliente($wc_customer, 'woocommerce');
            $wc_customer->add_meta_data('gestaoclick_gc_cliente_id', $gc_cliente->get_id(), true);
        }

        $body = array(
            'tipo'              => 'produto',
            'cliente_id'        => $gc_cliente_id,
            'data'              => $order->get_date_created()->date('Y-m-d'),
            'situacao_id'       => get_option('gcw-settings-export-situacao'),
            'transportadora_id' => get_option('gcw-settings-export-trasportadora'),
            'valor_frete'       => $order->get_shipping_total(),
            'nome_canal_venda'  => 'Internet',
            'produtos'          => $gc_products,
        );

        wp_remote_post( 
            $this->api_endpoint, 
            array_merge(
                $this->api_headers,
                array( 'body' => json_encode($body) ),
            ) 
        );
    }
}