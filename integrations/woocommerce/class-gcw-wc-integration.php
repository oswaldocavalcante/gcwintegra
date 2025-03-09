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

    public function __construct() 
    {
        $this->id = 'gestaoclick';
        $this->method_title = __('GestãoClick');
        $this->method_description = __('Integre o GestãoClick ao Woocommerce.', 'gestaoclick');

        $this->init_form_fields();
        $this->init_settings();
        $this->define_woocommerce_hooks();
    }

    private function define_woocommerce_hooks()
    {
        add_action('woocommerce_update_options_integration_' . $this->id,   array($this, 'process_admin_options'));
        add_filter('cron_schedules',                                        array($this, 'add_cron_interval'));
        add_filter('manage_edit-shop_order_columns',                        array($this, 'add_order_list_column'), 20);
        add_action('manage_shop_order_posts_custom_column',                 array($this, 'add_order_list_column_actions_legacy'), 20, 2);
        add_filter('woocommerce_shop_order_list_table_columns',             array($this, 'add_order_list_column'));                            // HPOS orders page.
        add_action('woocommerce_shop_order_list_table_custom_column',       array($this, 'add_order_list_column_actions_hpos'),  10, 2);    // HPOS orders page.

    }

    public function init_form_fields() 
    {
        $button_import_html = '';

        if(GCW_GC_Api::test_connection()) 
        {
            $gc_transportadoras = new GCW_GC_Transportadoras();
            $this->gc_transportadoras_options = $gc_transportadoras->get_options_for_settings();

            $gc_situacoes = new GCW_GC_Situacoes();
            $this->gc_situacoes_options = $gc_situacoes->get_options_for_settings();

            $gc_categorias = new GCW_WC_Categories();
            $this->gc_categorias_options = $gc_categorias->get_options_for_settings();

            $button_import_html = '
                <br>
                <div id="gcw-import-area">
                    <a id="gcw-btn-import" class="button gcw-btn-settings">Importar agora</a>
                    <span id="gcw-last-import" style="color: #888">(Última importação: ' . get_option('gcw_gestaoclick_last_import') . ')</span>
                </div>
            ';
        }

        $this->form_fields = array
        (
            'gcw-api-credentials-section' => array
            (
                'type'          => 'title',
                'title'         => __( 'Credenciais de acesso da API', 'gestaoclick' ),
                'description'   => sprintf(__('Veja como obter suas credenciais em <a href="https://gestaoclick.com/integracao_api/configuracoes/gerar_token" target="blank">%s</a>', 'gestaoclick'), 'https://gestaoclick.com/integracao_api/configuracoes/gerar_token'),
            ),
            'gcw-api-access-token' => array
            (
                'type'        	=> 'text',
                'default'     	=> '',
                'title'       	=> __( 'Access Token', 'gestaoclick' ),
                'description' 	=> __( 'Seu Access Token das configurações de API do GestãoClick.', 'gestaoclick' ),
            ),
            'gcw-api-secret-access-token' => array
            (
                'type'        	=> 'text',
                'default'     	=> '',
                'title'       	=> __( 'Secret Access Token', 'gestaoclick' ),
                'description' 	=> __('Seu Secret Access Token das configurações de API do GestãoClick.', 'gestaoclick' ),
            ),

            'gcw-settings-imports-section' => array
            (
                'type'          => 'title',
                'title'         => __( 'Importações', 'gestaoclick' ),
                'description'   => __( 'Configure como realizar importações para o WooCommerce.' ) . $button_import_html,
            ),
            'gcw-settings-auto-imports' => array
            (
                'type'          => 'checkbox',
                'default'       => 'no',
                'title'         => __( 'Auto-importar', 'gestaoclick' ),
                'label'         => __( 'Habilitar auto-importação', 'gestaoclick' ),
                'description'   => __( 'Habilite para sincronizar periodicamente (a cada 15 minutos) o WooCommerce com o GestãoClick.', 'gestaoclick' ),
            ),
            'gcw-settings-categories-selection' => array
            (
                'type'          => 'multiselect',
                'title'         => __( 'Seleção de Categorias', 'gestaoclick' ),
                'description'   => __( 'Selecione as categorias para importar seus produtos do GestãoClick.', 'gestaoclick' ),
                'css'           => 'height: 300px;',
                'options'       => $this->gc_categorias_options,
            ),
            'gcw-settings-products-blacklist' => array
            (
                'type'          => 'textarea',
                'placeholder'   => '2012254018005...',
                'title'         => __( 'Produtos proibidos', 'gestaoclick' ),
                'description'   => __( 'Lista de códigos de produtos para não importar do GestãoClick (um código de produto por linha).', 'gestaoclick' ),
            ),

            'gcw-settings-exports-section' => array
            (
                'type'          => 'title',
                'title'         => __( 'Exportações', 'gestaoclick' ),
                'description'   => __('Configure como realizar exportações para o GestãoClick.' ),
            ),
            'gcw-settings-export-orders' => array
            (
                'type'          => 'checkbox',
                'default'       => 'no',
                'title'         => __( 'Auto-exportar vendas', 'gestaoclick' ),
                'label'         => __( 'Habilitar auto-exportar vendas', 'gestaoclick' ),
                'description'   => __( 'Sempre exportar novas vendas pagas e seus respectivos clientes ao GestãoClick.', 'gestaoclick' ),
            ),
            'gcw-settings-export-situacao' => array
            (
                'type'          => 'select',
                'title'         => __('Situação padrão ao exportar vendas ao GestãoClick', 'gestaoclick'),
                'label'         => __('Selecione a situação padrão para novas vendas exportadas', 'gestaoclick'),
                'description'   => __('A situação padrão para ser usada em novas vendas pagas exportadas para o GestaoClick.', 'gestaoclick'),
                'options'       => $this->gc_situacoes_options,
            ),
            'gcw-settings-export-trasportadora' => array
            (
                'type'          => 'select',
                'title'         => __( 'Transportadora padrão ao exportar vendas ao GestãoClick', 'gestaoclick' ),
                'label'         => __( 'Selecione a transportadora padrão para novas vendas exportadas', 'gestaoclick' ),
                'description'   => __( 'A transportadora padrão para ser usada em novas vendas pagas exportadas ao GestaoClick.', 'gestaoclick' ),
                'options'       => $this->gc_transportadoras_options,
            ),
            'gcw-settings-shipping-calculator' => array
            (
                'type'          => 'checkbox',
                'default'       => 'no',
                'title'         => __('Calculadora de frete', 'gestaoclick'),
                'label'         => __('Habilitar calculadora de frete', 'gestaoclick'),
                'description'   => __('A calculadora de frete aparece na página individual para produtos e orçamento.', 'gestaoclick'),
            ),

            'gcw-settings-quote-section' => array
            (
                'type'          => 'title',
                'title'         => __('Orçamentos', 'gestaoclick'),
                'description'   => __('Configure o funcionamento de orçamentos que serão enviados para o GestãoClick.'),
            ),
            'gcw-settings-quote-enabler' => array
            (
                'type'          => 'checkbox',
                'default'       => 'no',
                'title'         => __('Habilitar orçamentos', 'gestaoclick'),
                'label'         => __('Habilitar o módulo de orçamentos', 'gestaoclick'),
                'description'   => __('Produtos configurados para não ter controle de estoque no GestãoClick e importados para o WooCommerce, serão tratados como produtos para orçamentos.', 'gestaoclick'),
            ),
            'gcw-settings-quote-minimum' => array
            (
                'type'          => 'number',
                'default'       => '0',
                'placeholder'   => '0',
                'title'         => __('Quantidade mínima', 'gestaoclick'),
                'label'         => __('Configura uma quantidade mínima de todos os itens para um orçamento', 'gestaoclick'),
                'description'   => __('Se um orçamento não atingir a quantidade mínima de produtos, ele não será enviado ao GestãoClick e o cliente será notificado. Insira 0 para não impor uma quantidade mínima.', 'gestaoclick'),
            ),
        );
    }

    public function admin_options() 
    {
        update_option('gcw-api-access-token',               $this->settings['gcw-api-access-token']);
        update_option('gcw-api-secret-access-token',        $this->settings['gcw-api-secret-access-token']);

        update_option('gcw-settings-auto-imports',          $this->settings['gcw-settings-auto-imports']);
        update_option('gcw-settings-categories-selection',  $this->settings['gcw-settings-categories-selection']);
        update_option('gcw-settings-products-blacklist',    explode(PHP_EOL, $this->settings['gcw-settings-products-blacklist']));

        update_option('gcw-settings-export-orders',         $this->settings['gcw-settings-export-orders']);
        update_option('gcw-settings-export-trasportadora',  $this->settings['gcw-settings-export-trasportadora']);
        update_option('gcw-settings-export-situacao',       $this->settings['gcw-settings-export-situacao']);
        update_option('gcw-settings-shipping-calculator',   $this->settings['gcw-settings-shipping-calculator']);

        update_option('gcw-settings-quote-enabler',         $this->settings['gcw-settings-quote-enabler']);
        update_option('gcw-settings-quote-minimum',         $this->settings['gcw-settings-quote-minimum']);

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

    public function add_cron_interval( $schedules ) 
    { 
        $schedules['fifteen_minutes'] = array
        (
            'interval' => 900,
            'display'  => __( 'Every Fifteen Minutes', 'gestaoclick' ), 
        );

        return $schedules;
    }

	public function set_auto_imports( $auto_updates = 'no' ) 
    {
		if( $auto_updates == 'yes' )
        {
			if ( ! wp_next_scheduled( 'gestaoclick_update' ) ) {
				wp_schedule_event( time(), 'fifteen_minutes', 'gestaoclick_update' );
			}
		} 
        elseif ( wp_next_scheduled( 'gestaoclick_update' ) ) 
        {
			$timestamp = wp_next_scheduled( 'gestaoclick_update' );
			wp_unschedule_event( $timestamp, 'gestaoclick_update' );
		}
	}

    function add_order_list_column($columns)
    {
        if (!GCW_GC_Api::test_connection())
        {
            wp_admin_notice(__('GestãoClick: Configura suas credenciais de acesso da API.', 'gestaoclick'), array('type' => 'error'));
        }

        $reordered_columns = array();

        foreach ($columns as $key => $column)
        {
            $reordered_columns[$key] = $column;
            if ($key == 'order_status')
            {
                // Inserting after "Status" column
                $reordered_columns['gcw-actions'] = __('GestãoClick', 'gestaoclick');
            }
        }

        return $reordered_columns;
    }

    function add_order_list_column_actions_legacy($column, $order_id)
    {
        if ($column === 'gcw-actions')
        {
            $order = wc_get_order($order_id);

            if (!$order) return;

            if($order->meta_exists('gcw_gc_venda_id')) // Checks if the order has been exported
            {
                $button_label = __('NFe', 'gestaoclick');
                $button_props = '';
                $css_classes = 'button button-large dashicons-before dashicons-external ';

                if ($order->meta_exists('gcw_gc_venda_nfe_id'))
                { 
                    $button_label = __('Ver NFe', 'gestaoclick');
                }
                else
                {
                    $button_label = __('Emitir NFe', 'gestaoclick');
                    $css_classes .= 'button-primary ';
                }

                if (!$order->is_paid()) {
                    $css_classes .= 'disabled ';
                } 

                $button_props .= sprintf
                (
                    '
                        id="gcw-button-nfe"
                        data-order-id="%s"
                        class="%s"
                    ',
                    esc_attr($order_id),
                    esc_attr($css_classes)
                );

                echo sprintf('<a %s> %s </a>', $button_props, $button_label);
            }
        }
    }

    function add_order_list_column_actions_hpos($column, $post_or_order_object)
    {
        if ($column === 'gcw-actions')
        {
            $order = ($post_or_order_object instanceof WP_Post) ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;
            // Note: $post_or_order_object should not be used directly below this point.

            if (!$order) return;

            if ($order->meta_exists('gcw_gc_venda_id')) // Checks if the order has been exported
            {
                $button_label = __('NFe', 'gestaoclick');
                $button_props = '';
                $css_classes = 'button button-large dashicons-before dashicons-external ';

                if ($order->meta_exists('gcw_gc_venda_nfe_id'))
                {
                    $button_label = __('Ver NFe', 'gestaoclick');
                }
                else
                {
                    $button_label = __('Emitir NFe', 'gestaoclick');
                    $css_classes .= 'button-primary ';
                }

                if (!$order->is_paid())
                {
                    $css_classes .= 'disabled ';
                }

                $button_props .= sprintf(
                    '
                        id="gcw-button-nfe"
                        data-order-id="%s"
                        class="%s"
                    ',
                    esc_attr($order->get_id()),
                    esc_attr($css_classes)
                );

                echo sprintf('<a %s> %s </a>', $button_props, $button_label);
            }
        }
    }
}