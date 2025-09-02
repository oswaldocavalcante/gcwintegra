<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once 'class-gcwi-gc-api.php';

class GCWI_GC_Notas_Fiscais extends GCWI_GC_API 
{
    private $api_endpoint;

    public function __construct()
    {
        parent::__construct();
        $this->api_endpoint = parent::get_endpoint_notas_fiscais();
    }

    public function fetch($id)
    {
        return parent::get($id, $this->api_endpoint);
    }

    public function create_nota_fiscal($order_id)
    {
        $wc_order = wc_get_order($order_id);
        $gc_produtos = $this->get_produtos($wc_order);

        $wc_customer = new WC_Customer($wc_order->get_customer_id());
        $gc_cliente_id = $wc_customer->get_meta('gcwi_gc_cliente_id');

        $body = array
        (
            'loja_id'           => 5865,
            'tipo_nf'           => 1,
            'id_destinatario'   => $gc_cliente_id,
            'produtos'          => $gc_produtos
        );

        $response = wp_remote_post($this->api_endpoint, array_merge
        (
            parent::get_headers(),
            array('body' => json_encode($body))
        ));

        if(is_wp_error($response)) return $response;

        $gc_nfe_id = json_decode($response['body'])->data->dados;
        $this->add_wc_order_metadata($wc_order, $gc_nfe_id);

        return $gc_nfe_id;
    }

    private function get_produtos(WC_Order $order)
    {
        $produtos = array();

        foreach ($order->get_items() as $order_item)
        {
            $wc_product = wc_get_product($order_item->get_data()['product_id']);
            $gc_produto = array
            (
                'produto_id'        => $wc_product->get_meta('gcwi_gc_product_id'),
                'codigo_produto'    => $wc_product->get_sku(),
                'nome_produto'      => $wc_product->get_name(),
                'unidade'           => 'UN',
                'quantidade'        => $order_item->get_quantity(),
                'valor_venda'       => $wc_product->get_price(),
                'valor_custo'       => 0,
                'ncm'               => $wc_product->get_meta('gcwi_gc_ncm'),
            );

            // Optional
            // $wc_variation_id = $order_item_data['variation_id'];
            // if ($wc_variation_id)
            // {
            //     $wc_variation       = wc_get_product($wc_variation_id);
            //     $gc_variation_id    = $wc_variation->get_meta('gcwi_gc_variation_id');
            //     $gc_produto['variacao_id'] = $gc_variation_id;

            //     $wc_parent = wc_get_product($wc_variation->get_parent_id());
            //     $gc_product_id = $wc_parent->get_meta('gcwi_gc_product_id');
            //     $gc_produto['produto_id'] = $gc_product_id;
            // }

            $produtos[] = $gc_produto;
        }

        return $produtos;
    }

    public function add_wc_order_metadata(WC_Order $wc_order, $gc_data)
    {
        $order = wc_get_order($wc_order);
        $order->add_meta_data('gcwi_gc_nfe_id', $gc_data->data->dados, true);
        $order->save();
    }
}