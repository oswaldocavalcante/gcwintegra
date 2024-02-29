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
            'tipo_pessoa'   => strlen($cpf_cnpj) == 18 ? 'PJ' : 'PF',
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

				<h2>' . esc_html( __('Instituição', 'gestaoclick') ) . '</h2>
				<section id="gcw-section-institution" class="gcw-quote-section">
					<div class="gcw-field-wrap">
						<label>' . esc_html( __('Nome fantasia', 'gestaoclick') ) . '</label>
						<input type="text" class="gcw-quote-input" name="gcw_cliente_nome" required />
					</div>
					<div class="gcw-field-wrap">
						<label>' . esc_html( __('CNPJ/CPF', 'gestaoclick') ) . '</label>
						<input type="text" class="gcw-quote-input" name="gcw_cliente_cpf_cnpj" id="gcw-cliente-cpf-cnpj" required />
					</div>
				</section>

				<h2>' . esc_html( __('Responsável', 'gestaoclick') ) . '</h2>
				<section id="gcw-section-responsable" class="gcw-quote-section">
					<div class="gcw-field-wrap">
						<label>' . esc_html( __('Nome e sobrenome', 'gestaoclick') ) . '</label>
						<input type="text" name="gcw_contato_nome" class="gcw-quote-input" required />
					</div>
					<div class="gcw-field-wrap">
						<label>' . esc_html( __('Email', 'gestaoclick') ) . '</label>
						<input type="email" name="gcw_contato_email" class="gcw-quote-input" required />
					</div>
					<div class="gcw-field-wrap">
						<label>' . esc_html( __('Telefone', 'gestaoclick') ) . '</label>
						<input type="text" name="gcw_contato_telefone" class="gcw-quote-input" required />
					</div>
					<div class="gcw-field-wrap">
						<label>' . esc_html( __('Cargo', 'gestaoclick') ) . '</label>
						<input type="text" name="gcw_contato_cargo" class="gcw-quote-input" required />
					</div>
				</section>

				<h2>' . esc_html( __('Orçamento', 'gestaoclick') ) . '</h2>
				<section id="gcw-quote-section-items">
                    <fieldset id="gcw-quote-fieldset-1" class="gcw-quote-fieldset">
                        <legend class="gcw-quote-fieldset-legend">' . esc_html( __('Item 1', 'gestaoclick') ) . '</legend>
                        <div class="gcw-field-wrap">
                            <label>' . esc_html( __('Nome', 'gestaoclick') ) . '</label>
                            <input type="text" class="gcw-quote-name gcw-quote-input" name="gcw_item_nome-1" required />
                        </div>
                        <div class="gcw-field-wrap">
                            <label>' . esc_html( __('Descrição', 'gestaoclick') ) . '</label>
                            <input type="text" class="gcw-quote-description gcw-quote-input" name="gcw_item_descricao-1" required />
                        </div>
                        <div class="gcw-field-wrap gcw-field-size">
                        <label>' . esc_html( __('Tamanho', 'gestaoclick') ) . '</label>
                            <select class="gcw-quote-size gcw-quote-input" name="gcw_item_tamanho-1" required>
                                <option value="' . esc_html( __('Selecionar', 'gestaoclick') ) . '" selected="selected">' . esc_html( __('Selecionar', 'gestaoclick') ) . '</option>
                                <option value="PP">PP</option>
                                <option value="P">P</option>
                                <option value="M">M</option>
                                <option value="G">G</option>
                                <option value="GG">GG</option>
                                <option value="XG">XG</option>
                                <option value="XGG">XGG</option>
                                <option value="PS">Plus Size</option>
                            </select>
                        </div>
                        <div class="gcw-field-wrap gcw-field-quantity">
                            <label>' . esc_html( __('Quantidade', 'gestaoclick') ) . '</label>
                            <input type="number" class="gcw-quote-quantity gcw-quote-input" name="gcw_item_quantidade-1" required value="10" min="10" inputmode="numeric" pattern="\d*" />
                        </div>
                        <a class="gcw-quote-button-remove" item_id="1">×</a>
                    </fieldset>
				</section>
				<a id="gcw-quote-add-item">' . esc_html( __('Adicionar item', 'gestaoclick') ) . '</a>
				
				<button type="submit" id="gcw-quote-send">' . esc_html( __('Solicitar orçamento', 'gestaoclick') ) . '</button>

			</form>
		';
    }
}