<?php

require_once 'class-gcw-gc-api.php';

class GCW_GC_Orcamento extends GCW_GC_Api {
    
    private $api_headers;
    private $api_endpoint;
    
    private $id = null;
    private $data = array();
    private $produtos = array();

    public function __construct($data = null, $cliente_id = null, $context = null | 'form') {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_orcamentos();

        if($context == 'form') {
            $this->produtos = $this->get_form_items($data);
            $this->data = array(
                "tipo"              => "produto",
                "cliente_id"        => $cliente_id,
                "situacao_id"       => get_option("gcw-settings-export-situacao"),
                "nome_canal_venda"  => "Internet",
                "produtos"          => $this->produtos,
            );
        }
    }

    public function get_id() {
        return $this->id;
    }

    public function set_props($props) {
        $this->data = $props;
    }

    public function export(){

        $response = wp_remote_post(
            $this->api_endpoint,
            array_merge($this->api_headers, ["body" => json_encode($this->data)])
        );

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (is_array($response) && $response["code"] == 200) {
            $this->id = $response["data"]["id"];
            return $this->id;
        } else {
            return new WP_Error("failed", __("GestãoClick: Error on export to GestãoClick.", "gestaoclick"));
        }
    }

    private function get_form_items($orcamento){
        $items = [];
        $item_id = 1;
        for ($i = 6; $i < count($orcamento); $i = $i+4) {
            $items = array_merge($items, [
                "produto" => [
                    "nome_produto"  =>  
                        sanitize_text_field($orcamento["gcw_item_nome-{$item_id}"]) . " - " .
                        sanitize_text_field($orcamento["gcw_item_descricao-{$item_id}"]),
                    "detalhes"      =>  sanitize_text_field($orcamento["gcw_item_tamanho-{$item_id}"]),
                    "quantidade"    =>  sanitize_text_field($orcamento["gcw_item_quantidade-{$item_id}"]),
                ],
            ]);
            ++$item_id;
        }

        return $items;
    }
}
