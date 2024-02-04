<?php

require_once WP_PLUGIN_DIR . '/wooclick/admin/class-wck-gc-api.php';
require_once WP_PLUGIN_DIR . '/wooclick/admin/gestaoclick/class-wck-gc-transportadoras.php';
require_once WP_PLUGIN_DIR . '/wooclick/admin/gestaoclick/class-wck-gc-situacoes.php';

class WCK_WC_Integration extends WC_Integration {

    private $gc_transportadoras_options = null;
    private $gc_situacoes_options = null;

    public function __construct() {
        
        $this->id = 'wooclick';
        $this->method_title = __('WooClick');
        $this->method_description = __('Integrates GestãoClick to Woocommerce.', 'wooclick');

        $this->init_form_fields();
        $this->init_settings();

        add_action(	'woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
        add_filter( 'cron_schedules', array($this, 'add_cron_interval') );
    }

    public function init_form_fields() {

        if( WCK_GC_Api::test_connection() ) {
            $gc_transportadoras =   new WCK_GC_Transportadoras();
            $gc_transportadoras_options = $gc_transportadoras->get_select_options();

            $gc_situacoes =         new WCK_GC_Situacoes();
            $gc_situacoes_options = $gc_situacoes->get_select_options();
        } else {
            $gc_transportadoras_options = null;
            $gc_situacoes_options = null;
        }

        $this->form_fields = array(
            'wck-api-credentials-section' => array(
                'title'         => __( 'API Access Credentials', 'wooclick' ),
                'type'          => 'title',
                'description'   => sprintf(__('See how to get your credentials in <a href="https://gestaoclick.com/integracao_api/configuracoes/gerar_token" target="blank">%s</a>', 'wooclick'), 'https://gestaoclick.com/integracao_api/configuracoes/gerar_token'),
            ),
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
            'wck-settings-imports-section' => array(
                'title'         => __( 'Plugin Imports', 'wooclick' ),
                'type'          => 'title',
                'description'   => __( 'Set how to work on imports to WooCommerce.' ),
            ),
            'wck-settings-auto-imports' => array(
                'title'         => __( 'Auto-imports', 'wooclick' ),
                'type'          => 'checkbox',
                'label'         => __( 'Enable auto-import', 'wooclick' ),
                'default'       => 'no',
                'description'   => __( 'Check to periodically (each 15 minutes) synchronize WooCommerce with GestãoClick.', 'wooclick' ),
            ),
            'wck-settings-blacklist-categories' => array(
                'title'         => __( 'Categories Blacklist', 'wooclick' ),
                'type'          => 'textarea',
                'placeholder'   => __('Companies,Universities,Schools,...', 'wooclick' ),
                'description'   => __( 'List of categories to not import products from GestãoClick (categories names separated by commas, without spaces).', 'wooclick' ),
            ),
            'wck-settings-blacklist-products' => array(
                'title'         => __( 'Products Blacklist', 'wooclick' ),
                'type'          => 'textarea',
                'placeholder'   => '2012254018005,2090661972561,...',
                'description'   => __( 'List of products to not import from GestãoClick (product codes separated by commas, without spaces).', 'wooclick' ),
            ),

            'wck-settings-exports-section' => array(
                'title'         => __( 'Plugin Exports', 'wooclick' ),
                'type'          => 'title',
                'description'   => __( 'Set how to work on exports to GestãoClick.' ),
            ),
            'wck-settings-export-orders' => array(
                'title'         => __( 'Auto-export Orders', 'wooclick' ),
                'type'          => 'checkbox',
                'label'         => __( 'Enable auto-export orders', 'wooclick' ),
                'default'       => 'no',
                'description'   => __( 'Allways export new paid orders and its customers to GestãoClick.', 'wooclick' ),
            ),
            'wck-settings-export-trasportadora' => array(
                'title'         => __( 'Default shipping company to export orders to GestãoClick', 'wooclick' ),
                'type'          => 'select',
                'label'         => __( 'Select the default shipping company for new orders exported', 'wooclick' ),
                'description'   => __( 'The default shipping company to be used on new paid orders exported to GestaoClick.', 'wooclick' ),
                'options'       => $gc_transportadoras_options,
            ),
            'wck-settings-export-situacao' => array(
                'title'         => __( 'Default situation to export orders to GestãoClick', 'wooclick' ),
                'type'          => 'select',
                'label'         => __( 'Select the default situation for new orders exported', 'wooclick' ),
                'description'   => __( 'The default situation to be used on new paid orders exported to GestaoClick.', 'wooclick' ),
                'options'       => $gc_situacoes_options,
            ),
        );
    }

    public function admin_options() {

        update_option( 'wck-api-access-token', 	            $this->get_option('wck-api-access-token') );
        update_option( 'wck-api-secret-access-token', 	    $this->get_option('wck-api-secret-access-token') );

        update_option( 'wck-settings-auto-imports',         $this->get_option('wck-settings-auto-imports') );
        update_option( 'wck-settings-blacklist-categories', explode( ',', $this->get_option('wck-settings-blacklist-categories') ) );
        update_option( 'wck-settings-blacklist-products',   explode( ',', $this->get_option('wck-settings-blacklist-products') ) );

        update_option( 'wck-settings-export-orders',        $this->get_option('wck-settings-export-orders') );
        update_option( 'wck-settings-export-trasportadora', $this->get_option('wck-settings-export-trasportadora') );
        update_option( 'wck-settings-export-situacao',      $this->get_option('wck-settings-export-situacao') );

        echo '<div id="wck_settings">';
        echo '<h2 class="wck-integration-title">' . esc_html( $this->get_method_title() ) . '</h2>';

        if( WCK_GC_Api::test_connection() ) {
            echo '<span class="wck-integration-connection dashicons-before dashicons-yes-alt">' . __('Connected', 'wooclick') . '</span>';
        } else {
            wp_admin_notice( __( 'WooClick: Preencha corretamente suas credenciais de acesso.', 'wooclick' ), array( 'error' ) );
        }

        $this->set_auto_imports( get_option( 'wck-settings-auto-imports') );

        echo wp_kses_post( wpautop( $this->get_method_description() ) );
        echo '<div><input type="hidden" name="section" value="' . esc_attr( $this->id ) . '" /></div>';
        echo '<table class="form-table">' . $this->generate_settings_html( $this->get_form_fields(), false ) . '</table>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';
    }

    public function add_cron_interval( $schedules ) { 
        $schedules['fifteen_minutes'] = array(
            'interval' => 900,
            'display'  => __( 'Every Fifteen Minutes', 'wooclick' ), 
        );
        return $schedules;
    }

	public function set_auto_imports( $auto_updates = 'no' ) {
		if( $auto_updates == 'yes' ){
			if ( ! wp_next_scheduled( 'wooclick_update' ) ) {
				wp_schedule_event( time(), 'fifteen_minutes', 'wooclick_update' );
			}
		} elseif ( wp_next_scheduled( 'wooclick_update' ) ) {
			$timestamp = wp_next_scheduled( 'wooclick_update' );
			wp_unschedule_event( $timestamp, 'wooclick_update' );
		}
	}
}