<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once GCWC_ABSPATH . 'integrations/gestaoclick/class-gcwc-gc-api.php';
require_once GCWC_ABSPATH . 'integrations/gestaoclick/class-gcwc-gc-transportadoras.php';
require_once GCWC_ABSPATH . 'integrations/gestaoclick/class-gcwc-gc-situacoes.php';
require_once GCWC_ABSPATH . 'integrations/woocommerce/class-gcwc-wc-categories.php';

class GCWC_WC_Integration extends WC_Integration 
{
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
        $this->id = 'gcwc';
        $this->method_title = __('GestãoClick', 'gcwc');
        $this->method_description = __('Integre o GestãoClick ao Woocommerce.', 'gcwc');

        $this->init_form_fields();
        $this->init_settings();
        $this->define_woocommerce_hooks();
    }

    private function define_woocommerce_hooks()
    {
        add_action('woocommerce_update_options_integration_' . $this->id,   array($this, 'process_admin_options'));
        add_filter('manage_edit-shop_order_columns',                        array($this, 'add_order_list_column'), 20);
        add_action('manage_shop_order_posts_custom_column',                 array($this, 'add_order_list_column_actions_legacy'), 20, 2);
        add_filter('woocommerce_shop_order_list_table_columns',             array($this, 'add_order_list_column'));                            // HPOS orders page.
        add_action('woocommerce_shop_order_list_table_custom_column',       array($this, 'add_order_list_column_actions_hpos'),  10, 2);    // HPOS orders page.
        add_action('wp_ajax_gcwc_nfe',                                      array($this, 'ajax_gcwc_nfe'));
    }

    public function init_form_fields() 
    {
        $button_import_html = '';

        if(GCWC_GC_Api::test_connection()) 
        {
            $gc_transportadoras = new GCWC_GC_Transportadoras();
            $this->gc_transportadoras_options = $gc_transportadoras->get_options_for_settings() ?? [];

            $gc_situacoes = new GCWC_GC_Situacoes();
            $this->gc_situacoes_options = $gc_situacoes->get_options_for_settings() ?? [];

            $gc_categorias = new GCWC_WC_Categories();
            $this->gc_categorias_options = $gc_categorias->get_options_for_settings() ?? [];

            $button_import_html = 
            '<div id="gcwc-import-area">
                <a id="gcwc-btn-import" class="button gcwc-btn-settings">Importar agora</a>
                <span id="gcwc-last-import" style="color: #888">(Última importação: ' . get_option('gcwc_last_import') . ')</span>
            </div>';
        }

        $this->form_fields = array
        (
            'gcwc-api-credentials-section' => array
            (
                'type'          => 'title',
                'title'         => __('Credenciais de acesso da API', 'gcwc'),
                'description'   => __('Veja como obter suas credenciais em <a href="https://gestaoclick.com/integracao_api/configuracoes/gerar_token" target="blank">https://gestaoclick.com/integracao_api/configuracoes/gerar_token</a>', 'gcwc'),
            ),
            'gcwc-api-access-token' => array
            (
                'type'        	=> 'text',
                'default'     	=> '',
                'title'       	=> __('Access Token', 'gcwc'),
                'description' 	=> __('Seu Access Token das configurações de API do GestãoClick.', 'gcwc'),
            ),
            'gcwc-api-secret-access-token' => array
            (
                'type'        	=> 'text',
                'default'     	=> '',
                'title'       	=> __('Secret Access Token', 'gcwc'),
                'description' 	=> __('Seu Secret Access Token das configurações de API do GestãoClick.', 'gcwc'),
            ),

            'gcwc-settings-imports-section' => array
            (
                'type'          => 'title',
                'title'         => __('Importações', 'gcwc' ),
                'description'   => __('Configure como realizar importações para o WooCommerce.', 'gcwc') . $button_import_html,
            ),
            'gcwc-settings-auto-imports' => array
            (
                'type'          => 'checkbox',
                'default'       => 'no',
                'title'         => __( 'Auto-importar', 'gcwc' ),
                'label'         => __( 'Habilitar auto-importação', 'gcwc' ),
                'description'   => __( 'Habilite para sincronizar periodicamente (a cada 15 minutos) o WooCommerce com o GestãoClick.', 'gcwc'),
            ),
            'gcwc-settings-categories-selection' => array
            (
                'type'          => 'multiselect',
                'title'         => __('Seleção de Categorias', 'gcwc'),
                'description'   => __('Selecione as categorias para importar seus produtos do GestãoClick.', 'gcwc'),
                'css'           => 'height: 300px;',
                'options'       => $this->gc_categorias_options,
            ),
            'gcwc-settings-products-blacklist' => array
            (
                'type'          => 'textarea',
                'placeholder'   => '2012254018005...',
                'title'         => __('Produtos proibidos', 'gcwc'),
                'description'   => __('Lista de códigos de produtos para não importar do GestãoClick (um código de produto por linha).', 'gcwc'),
            ),

            'gcwc-settings-exports-section' => array
            (
                'type'          => 'title',
                'title'         => __('Exportações', 'gcwc'),
                'description'   => __('Configure como realizar exportações para o GestãoClick.', 'gcwc'),
            ),
            'gcwc-settings-export-orders' => array
            (
                'type'          => 'checkbox',
                'default'       => 'no',
                'title'         => __('Auto-exportar vendas', 'gcwc'),
                'label'         => __('Habilitar auto-exportar vendas', 'gcwc'),
                'description'   => __('Sempre exportar novas vendas pagas e seus respectivos clientes ao GestãoClick.', 'gcwc'),
            ),
            'gcwc-settings-export-situacao' => array
            (
                'type'          => 'select',
                'title'         => __('Situação padrão ao exportar vendas ao GestãoClick', 'gcwc'),
                'label'         => __('Selecione a situação padrão para novas vendas exportadas', 'gcwc'),
                'description'   => __('A situação padrão para ser usada em novas vendas pagas exportadas para o GestaoClick.', 'gcwc'),
                'options'       => $this->gc_situacoes_options,
            ),
            'gcwc-settings-export-trasportadora' => array
            (
                'type'          => 'select',
                'title'         => __('Transportadora padrão ao exportar vendas ao GestãoClick', 'gcwc'),
                'label'         => __('Selecione a transportadora padrão para novas vendas exportadas', 'gcwc'),
                'description'   => __('A transportadora padrão para ser usada em novas vendas pagas exportadas ao GestaoClick.', 'gcwc'),
                'options'       => $this->gc_transportadoras_options,
            ),
            'gcwc-settings-shipping-calculator' => array
            (
                'type'          => 'checkbox',
                'default'       => 'no',
                'title'         => __('Calculadora de frete', 'gcwc'),
                'label'         => __('Habilitar calculadora de frete', 'gcwc'),
                'description'   => __('A calculadora de frete aparece na página individual para produtos e orçamento.', 'gcwc'),
            )
        );
    }

    public function admin_options() 
    {
        update_option('gcwc-api-access-token',               $this->settings['gcwc-api-access-token']);
        update_option('gcwc-api-secret-access-token',        $this->settings['gcwc-api-secret-access-token']);

        update_option('gcwc-settings-auto-imports',          $this->settings['gcwc-settings-auto-imports']);
        update_option('gcwc-settings-categories-selection',  $this->settings['gcwc-settings-categories-selection']);
        update_option('gcwc-settings-products-blacklist',    explode(PHP_EOL, $this->settings['gcwc-settings-products-blacklist']));

        update_option('gcwc-settings-export-orders',         $this->settings['gcwc-settings-export-orders']);
        update_option('gcwc-settings-export-trasportadora',  $this->settings['gcwc-settings-export-trasportadora']);
        update_option('gcwc-settings-export-situacao',       $this->settings['gcwc-settings-export-situacao']);
        update_option('gcwc-settings-shipping-calculator',   $this->settings['gcwc-settings-shipping-calculator']);


        if(GCWC_GC_Api::test_connection()) 
        {
            echo '<span id="gcwc-integration-connection" class="dashicons-before dashicons-yes-alt">' . esc_html( __('Conectado', 'gcwc') ) . '</span>';
        } 
        else
        {
            wp_admin_notice(__( 'GestaoClick: Preencha corretamente suas credenciais de acesso.', 'gcwc' ), array( 'error' ) );
        }

        $this->set_auto_imports(get_option('gcwc-settings-auto-imports'));

        parent::admin_options();
    }

	public function set_auto_imports($auto_updates = 'no') 
    {
		if( $auto_updates == 'yes' )
        {
			if (!wp_next_scheduled('gcwc_update')) 
            {
				wp_schedule_event(time(), 'fifteen_minutes', 'gcwc_update');
			}
		} 
        elseif ( wp_next_scheduled( 'gcwc_update' ) ) 
        {
			$timestamp = wp_next_scheduled( 'gcwc_update' );
			wp_unschedule_event( $timestamp, 'gcwc_update' );
		}
	}

    function add_order_list_column($columns)
    {
        if (!GCWC_GC_Api::test_connection())
        {
            wp_admin_notice(__('GestãoClick: Configura suas credenciais de acesso da API.', 'gcwc'), array('type' => 'error'));
        }

        $reordered_columns = array();

        foreach ($columns as $key => $column)
        {
            $reordered_columns[$key] = $column;
            if ($key == 'order_status')
            {
                $reordered_columns['gcwc-actions'] = __('GestãoClick', 'gcwc'); // Inserting after "Status" column
            }
        }

        return $reordered_columns;
    }

    function add_order_list_column_actions_legacy($column, $order_id)
    {
        if($column !== 'gcwc-actions') return;
        
        $order = wc_get_order($order_id);

        if(!$order || !$order->meta_exists('gcwc_gc_venda_id')) return;

        echo wp_kses_post($this->generate_nfe_button($order));
    }

    function add_order_list_column_actions_hpos($column, $post_or_order_object)
    {
        if($column !== 'gcwc-actions') return;
        
        /**  @var WC_Order $order  */
        $order = ($post_or_order_object instanceof WP_Post) 
            ? wc_get_order($post_or_order_object->ID) 
            : $post_or_order_object; // Note: $post_or_order_object should not be used directly below this point.

        if(!$order || !$order->meta_exists('gcwc_gc_venda_id')) return;

        echo wp_kses_post($this->generate_nfe_button($order));
    }

    private function generate_nfe_button(WC_Order $order)
    {
        $button_label = '';
        $css_classes  = ['button', 'button-large', 'dashicons-before', 'dashicons-external'];
        if(!$order->is_paid()) $css_classes[] = 'disabled';

        if($order->meta_exists('gcwc_gc_venda_nfe_id'))
        {
            $button_label = __('Ver NFe', 'gcwc');
        }
        else
        {
            $button_label = __('Emitir NFe', 'gcwc');
            $css_classes[] = 'button-primary';
        }

        return sprintf
        (
            '<a id="%s" data-order-id="%s" class="%s"> %s </a>',
            esc_attr('gcwc-button-nfe'),
            esc_attr($order->get_id()),
            esc_attr(implode(' ', $css_classes)),
            esc_html($button_label)
        );
    }

    public function ajax_gcwc_nfe($order_id)
    {
        if(!isset($_POST['order_id']) && !isset($_POST['security']) && !check_ajax_referer('gcwc_nonce', 'security')) return;

        $order_id = absint(wp_unslash($_POST['order_id']));
        $order = wc_get_order($order_id);
        $redirect_url = 'https://gestaoclick.com/notas_fiscais/';

        if($order->meta_exists('gcwc_gc_venda_nfe_id'))
        {
            $nota_fiscal_id = $order->get_meta('gcwc_gc_venda_nfe_id');
            $redirect_url .= 'index?id=' . $nota_fiscal_id;
        }
        else
        {
            $gc_venda = new GCWC_GC_Venda($order_id);
            $gc_venda_data = $gc_venda->get();

            if (is_wp_error($gc_venda_data))
            {
                wp_send_json( array
                (
                    'success' => false,
                    'data' => $gc_venda_data,
                    'message' => 'Nenhuma venda encontrada para este pedido.'
                ));
            }

            $nota_fiscal_id = $gc_venda_data['nota_fiscal_id'];

            if($nota_fiscal_id)
            {
                $order->add_meta_data('gcwc_gc_venda_nfe_id', $nota_fiscal_id);
                $redirect_url .= 'index?id=' . $nota_fiscal_id;
                $order->save();
            }
            else
            {
                $gc_venda_hash = $order->get_meta('gcwc_gc_venda_hash');
                $redirect_url .= 'adicionar/venda:' . $gc_venda_hash;
            }
        }

        wp_send_json_success($redirect_url);
    }
}