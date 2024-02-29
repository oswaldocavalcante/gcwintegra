<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gcw-gc-api.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gcw-public-gc-cliente.php';

class GCW_Public_GC_Orcamento extends GCW_GC_Api {

    public function __construct($orcamento) {
        parent::__construct();
        $this->api_headers  = parent::get_headers();
        $this->api_endpoint = parent::get_endpoint_orcamentos();

        $this->cliente_nome     = sanitize_text_field( $orcamento['gcw_cliente_nome'] );
        $this->cliente_cpf_cnpj = sanitize_text_field( $orcamento['gcw_cliente_cpf_cnpj'] );

        $this->contato_nome     = sanitize_text_field( $orcamento['gcw_contato_nome'] );
        $this->contato_email    = sanitize_text_field( $orcamento['gcw_contato_email'] );
        $this->contato_telefone = sanitize_text_field( $orcamento['gcw_contato_telefone'] );
        $this->contato_cargo    = sanitize_text_field( $orcamento['gcw_contato_cargo'] );

        $this->gc_produtos = $this->get_gc_items($orcamento);
    }

    public function export() {

        $gc_cliente_id  = $this->get_gc_cliente_id($this->cliente_cpf_cnpj);

        $body = array(
            'tipo'              => 'produto',
            'cliente_id'        => $gc_cliente_id,
            'situacao_id'       => get_option('gcw-settings-export-situacao'),
            'transportadora_id' => get_option('gcw-settings-export-trasportadora'),
            'nome_canal_venda'  => 'Internet',
            'produtos'          => $this->gc_produtos,
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
    public function get_gc_cliente_id($cpf_cnpj) {

        $orcamento_cliente = array(
            'tipo_pessoa'   => strlen($cpf_cnpj) == 18 ? 'cnpj' : 'cpf',
            'cnpj'          => strlen($cpf_cnpj) == 18 ? $cpf_cnpj : '',
            'cpf'           => strlen($cpf_cnpj) == 14 ? $cpf_cnpj : '',
            'nome'          => $this->cliente_nome,
            'contatos' => array(
                'contato' => array(
                    'nome'      => $this->contato_nome,
                    'contato'   => $this->contato_email . ' / ' . $this->contato_telefone,
                    'cargo'     => $this->contato_cargo,
                ),
            ),
        );

        $gc_cliente = new GCW_Public_GC_Cliente();
        return $gc_cliente->export( $orcamento_cliente );
    }

    public function get_gc_items($orcamento) {

        $items = array();
        $item_id = 1;
        for($i = 6; $i < count($orcamento) ; $i=$i+4) {
            $items = array_merge($items, 
                array(
                    'produto' => array(
                        'nome_produto'  => sanitize_text_field( $orcamento["gcw_item_nome-{$item_id}"] ) . ' - ' . sanitize_text_field( $orcamento["gcw_item_descricao-{$item_id}"] ),
                        'detalhes'      => sanitize_text_field( $orcamento["gcw_item_tamanho-{$item_id}"] ),
                        'quantidade'    => sanitize_text_field( $orcamento["gcw_item_quantidade-{$item_id}"] )
                    )
                )
            );
            ++$item_id;
        }

        return $items;
    }

    public static function render_form() {
        return '
			<form method="post">

				<h2>' . __('Instituição', 'gestaoclick') . '</h2>
				<section id="gcw-section-institution" class="gcw-quote-section">
					<div class="gcw-field-wrap">
						<label>' . __('Nome fantasia', 'gestaoclick') . '</label>
						<input type="text" class="gcw-quote-input" name="gcw_cliente_nome" required />
					</div>
					<div class="gcw-field-wrap">
						<label>' . __('CNPJ/CPF', 'gestaoclick') . '</label>
						<input type="text" class="gcw-quote-input" name="gcw_cliente_cpf_cnpj" id="gcw-cliente-cpf-cnpj" required />
					</div>
				</section>

				<h2>' . __('Responsável', 'gestaoclick') . '</h2>
				<section id="gcw-section-responsable" class="gcw-quote-section">
					<div class="gcw-field-wrap">
						<label>' . __('Nome e sobrenome', 'gestaoclick') . '</label>
						<input type="text" name="gcw_contato_nome" class="gcw-quote-input" required />
					</div>
					<div class="gcw-field-wrap">
						<label>' . __('Email', 'gestaoclick') . '</label>
						<input type="email" name="gcw_contato_email" class="gcw-quote-input" required />
					</div>
					<div class="gcw-field-wrap">
						<label>' . __('Telefone', 'gestaoclick') . '</label>
						<input type="text" name="gcw_contato_telefone" class="gcw-quote-input" required />
					</div>
					<div class="gcw-field-wrap">
						<label>' . __('Cargo', 'gestaoclick') . '</label>
						<input type="text" name="gcw_contato_cargo" class="gcw-quote-input" required />
					</div>
				</section>

				<h2>' . __('Orçamento', 'gestaoclick') . '</h2>
				<section id="gcw-quote-section-items">
				</section>
				<a id="gcw-quote-add-item">' . __('Adicionar item', 'gestaoclick') . '</a>
				
				<button type="submit" id="gcw-quote-send">Solicitar orçamento</button>

			</form>
		';
    }
}