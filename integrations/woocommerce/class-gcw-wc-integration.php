<?php

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-api.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-transportadoras.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-situacoes.php';
require_once GCW_ABSPATH . 'integrations/woocommerce/class-gcw-wc-categories.php';

class GCW_WC_Integration extends WC_Integration {

    public $id;
    public $method_title;
    public $method_description;
    public $form_fields;
    public $settings;

    private $gc_transportadoras_options = null;
    private $gc_situacoes_options = null;
    private $gc_categorias_options = null;

    public function __construct() {
        $this->id = 'gestaoclick';
        $this->method_title = __('GestãoClick');
        $this->method_description = __('Integre o GestãoClick ao Woocommerce.', 'gestaoclick');

        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
        add_filter('cron_schedules', array($this, 'add_cron_interval'));
    }

    public function init_form_fields() {
        if( GCW_GC_Api::test_connection() ) {
            $gc_transportadoras = new GCW_GC_Transportadoras();
            $this->gc_transportadoras_options = $gc_transportadoras->get_options_for_settings();

            $gc_situacoes = new GCW_GC_Situacoes();
            $this->gc_situacoes_options = $gc_situacoes->get_options_for_settings();

            $gc_categorias = new GCW_WC_Categories();
            $this->gc_categorias_options = $gc_categorias->get_options_for_settings();
        }

        $this->form_fields = array(
            'gcw-api-credentials-section' => array(
                'title'         => __( 'Credenciais de acesso da API', 'gestaoclick' ),
                'type'          => 'title',
                'description'   => sprintf(__('Veja como obter suas credenciais em <a href="https://gestaoclick.com/integracao_api/configuracoes/gerar_token" target="blank">%s</a>', 'gestaoclick'), 'https://gestaoclick.com/integracao_api/configuracoes/gerar_token'),
            ),
            'gcw-api-access-token' => array(
                'title'       	=> __( 'Access Token', 'gestaoclick' ),
                'type'        	=> 'text',
                'description' 	=> __( 'Seu Access Token das configurações de API do GestãoClick.', 'gestaoclick' ),
                'default'     	=> '',
            ),
            'gcw-api-secret-access-token' => array(
                'title'       	=> __( 'Secret Access Token', 'gestaoclick' ),
                'type'        	=> 'text',
                'description' 	=> __('Seu Secret Access Token das configurações de API do GestãoClick.', 'gestaoclick' ),
                'default'     	=> '',
            ),
            'gcw-settings-imports-section' => array(
                'title'         => __( 'Importações', 'gestaoclick' ),
                'type'          => 'title',
                'description'   => __( 'Configure como realizar importações para o WooCommerce.' ),
            ),
            'gcw-settings-auto-imports' => array(
                'title'         => __( 'Auto-importar', 'gestaoclick' ),
                'type'          => 'checkbox',
                'label'         => __( 'Habilitar auto-importação', 'gestaoclick' ),
                'default'       => 'no',
                'description'   => __( 'Habilite para sincronizar periodicamente (a cada 15 minutos) o WooCommerce com o GestãoClick.', 'gestaoclick' ),
            ),
            'gcw-settings-categories-selection' => array(
                'title'         => __( 'Seleção de Categorias', 'gestaoclick' ),
                'type'          => 'multiselect',
                'description'   => __( 'Selecione as categorias para importar seus produtos do GestãoClick.', 'gestaoclick' ),
                'options'       => $this->gc_categorias_options,
                'css' => 'height: 300px;',
            ),
            'gcw-settings-products-blacklist' => array(
                'title'         => __( 'Produtos proibidos', 'gestaoclick' ),
                'type'          => 'textarea',
                'placeholder'   => '2012254018005...',
                'description'   => __( 'Lista de códigos de produtos para não importar do GestãoClick (um código de produto por linha).', 'gestaoclick' ),
            ),
            'gcw-settings-exports-section' => array(
                'title'         => __( 'Exportações', 'gestaoclick' ),
                'type'          => 'title',
                'description'   => __('Configure como realizar exportações para o GestãoClick.' ),
            ),
            'gcw-settings-export-orders' => array(
                'title'         => __( 'Auto-exportar vendas', 'gestaoclick' ),
                'type'          => 'checkbox',
                'label'         => __( 'Habilitar auto-exportar vendas', 'gestaoclick' ),
                'default'       => 'no',
                'description'   => __( 'Sempre exportar novas vendas pagas e seus respectivos clientes ao GestãoClick.', 'gestaoclick' ),
            ),
            'gcw-settings-export-trasportadora' => array(
                'title'         => __( 'Transportadora padrão ao exportar vendas ao GestãoClick', 'gestaoclick' ),
                'type'          => 'select',
                'label'         => __( 'Selecione a transportadora padrão para novas vendas exportadas', 'gestaoclick' ),
                'description'   => __( 'A transportadora padrão para ser usada em novas vendas pagas exportadas ao GestaoClick.', 'gestaoclick' ),
                'options'       => $this->gc_transportadoras_options,
            ),
            'gcw-settings-export-situacao' => array(
                'title'         => __( 'Situação padrão ao exportar vendas ao GestãoClick', 'gestaoclick' ),
                'type'          => 'select',
                'label'         => __( 'Selecione a situação padrão para novas vendas exportadas', 'gestaoclick' ),
                'description'   => __( 'A situação padrão para ser usada em novas vendas pagas exportadas para o GestaoClick.', 'gestaoclick' ),
                'options'       => $this->gc_situacoes_options,
            ),
        );
    }

    public function admin_options() {
        update_option( 'gcw-api-access-token',              $this->settings['gcw-api-access-token'] );
        update_option( 'gcw-api-secret-access-token',       $this->settings['gcw-api-secret-access-token'] );

        update_option( 'gcw-settings-auto-imports',         $this->settings['gcw-settings-auto-imports'] );
        update_option( 'gcw-settings-categories-selection', $this->settings['gcw-settings-categories-selection'] );
        update_option( 'gcw-settings-products-blacklist',   explode(PHP_EOL, $this->settings['gcw-settings-products-blacklist'] ) );

        update_option( 'gcw-settings-export-orders',        $this->settings['gcw-settings-export-orders'] );
        update_option( 'gcw-settings-export-trasportadora', $this->settings['gcw-settings-export-trasportadora'] );
        update_option( 'gcw-settings-export-situacao',      $this->settings['gcw-settings-export-situacao'] );


        echo '<div id="gcw_settings">';
        echo '<h2 class="gcw-integration-title">' . esc_html( $this->get_method_title() ) . '</h2>';

        if( GCW_GC_Api::test_connection() ) {
            echo '<span class="gcw-integration-connection dashicons-before dashicons-yes-alt">' . esc_html( __('Conectado', 'gestaoclick') ) . '</span>';
        } else {
            wp_admin_notice(__( 'GestaoClick: Preencha corretamente suas credenciais de acesso.', 'gestaoclick' ), array( 'error' ) );
        }

        $this->set_auto_imports( get_option( 'gcw-settings-auto-imports') );

        echo wp_kses_post( wpautop( $this->get_method_description() ) );
        echo '<div><input type="hidden" name="section" value="' . esc_attr( $this->id ) . '" /></div>';
        echo '<table class="form-table">' . $this->generate_settings_html( $this->get_form_fields(), false ) . '</table>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';
    }

    public function add_cron_interval( $schedules ) { 
        $schedules['fifteen_minutes'] = array(
            'interval' => 900,
            'display'  => __( 'Every Fifteen Minutes', 'gestaoclick' ), 
        );
        return $schedules;
    }

	public function set_auto_imports( $auto_updates = 'no' ) {
		if( $auto_updates == 'yes' ){
			if ( ! wp_next_scheduled( 'gestaoclick_update' ) ) {
				wp_schedule_event( time(), 'fifteen_minutes', 'gestaoclick_update' );
			}
		} elseif ( wp_next_scheduled( 'gestaoclick_update' ) ) {
			$timestamp = wp_next_scheduled( 'gestaoclick_update' );
			wp_unschedule_event( $timestamp, 'gestaoclick_update' );
		}
	}
}