<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gcw-public-gc-orcamento.php';

class GCW_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'assets/css/gestaoclick-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'assets/js/gestaoclick-public.js', array('jquery'), $this->version, false);
	}

    public function shortcode_orcamento() {

		if($_POST){
			$gc_orcamento = new GCW_Public_GC_Orcamento();
			$gc_orcamento->export($_POST);
		}

        return '
			<form method="post">

				<h2>' . __('Instituição', 'gestaoclick') . '</h2>
				<section id="gcw-section-institution" class="gcw-quote-section">
					<div class="gcw-field-wrap">
						<label>' . __('Nome da instituição', 'gestaoclick') . '</label>
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
						<label>' . __('Nome', 'gestaoclick') . '</label>
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