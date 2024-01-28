<?php

require_once 'class-wck-gc-api.php';

class WCK_WC_Integration extends WC_Integration {
    
    private $wck_gc_api;

    public function __construct() {
        
        $this->id = 'wooclick';
        $this->method_title = __('WooClick');
        $this->method_description = __('Integrates GestãoClick for Woocommerce.', 'wooclick');

        $this->init_form_fields();
        $this->init_settings();

        $this->wck_gc_api = new WCK_GC_Api();
        $this->define_woocommerce_hooks();
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'wck-api-access-token' => array(
                'title'       	=> __( 'Access Token', 'wooclick' ),
                'type'        	=> 'text',
                'description' 	=> __( 'Your Access Token in GestãoClick API settings.', 'wooclick' ),
                'default'     	=> '',
            ),
            'wck-api-secret-access-token' => array(
                'title'       	=> __( 'Secret Access Token', 'wooclick' ),
                'type'        	=> 'text',
                'description' 	=> __( 'Your Secret Access Token in GestãoClick API settings.', 'wooclick' ),
                'default'     	=> '',
            ),
        );
    }

    public function admin_options() {

        update_option( 'wck-api-access-token', 	        $this->get_option('wck-api-access-token') );
        update_option( 'wck-api-secret-access-token', 	$this->get_option('wck-api-secret-access-token') );

        echo '<h2>' . esc_html( $this->get_method_title() ) . '</h2>';
        echo wp_kses_post( wpautop( $this->get_method_description() ) );
        echo sprintf(__('See how to get your credentials in <a href="https://gestaoclick.com/integracao_api/configuracoes/gerar_token" target="blank">%s</a>', 'wooclick'), 'https://gestaoclick.com/integracao_api/configuracoes/gerar_token');
        echo '<div id="wck_settings">';
        echo '<div><input type="hidden" name="section" value="' . esc_attr( $this->id ) . '" /></div>';
        echo '<table class="form-table">' . $this->generate_settings_html( $this->get_form_fields(), false ) . '</table>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';

        if( $this->wck_gc_api->is_connected() ) {
            echo __( 'Successfully connected!', 'wooclick' );
        } else {
            wp_admin_notice( __( 'WooClick: Preencha corretamente suas credenciais de acesso.', 'wooclick' ), array( 'warning', false ) );
        }
    }

    private function define_woocommerce_hooks() {
        add_action(	'woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
    }
}