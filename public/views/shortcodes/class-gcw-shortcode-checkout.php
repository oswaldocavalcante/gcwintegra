<?php

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-orcamento.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-cliente.php';

class GCW_Shortcode_Checkout
{
    private $session_shipping_rate  = '';
    private $session_shipping_cost  = '';
    private $session_subtotal       = '';
    private $session_total          = '';
    private $session_postcode       = '';
    private $session_address_html   = '';
    private $session_address_1      = '';
    private $session_address_2      = '';
    private $session_neighborhood   = '';
    private $session_city           = '';
    private $session_state          = '';
    private $session_quote_items    = array();

    private $input_first_name   = '';
    private $input_last_name    = '';
    private $input_company      = '';
    private $input_cnpj         = '';
    private $input_number       = '';
    private $input_phone        = '';
    private $input_email        = '';

    public function __construct()
    {
        add_action('wp_ajax_gcw_finish_quote',         array($this, 'ajax_finish_quote'));
        add_action('wp_ajax_nopriv_gcw_finish_quote',  array($this, 'ajax_finish_quote'));
    }

    public function set_session_attributes()
    {
        $this->session_quote_items      = WC()->session->get('quote_items');
        $this->session_shipping_rate    = WC()->session->get('quote_shipping_rate'); // AKA: Shipping method
        $this->session_shipping_cost    = WC()->session->get('quote_shipping_cost');
        $this->session_subtotal         = WC()->session->get('quote_subtotal_price');
        $this->session_total            = WC()->session->get('quote_total_price');

        $this->session_postcode         = WC()->session->get('shipping_postcode');
        $this->session_address_html     = WC()->session->get('shipping_address_html');
        $this->session_address_1        = WC()->session->get('shipping_address_1');
        $this->session_address_2        = WC()->session->get('shipping_address_2');
        $this->session_neighborhood     = WC()->session->get('shipping_neighborhood');
        $this->session_city             = WC()->session->get('shipping_city');
        $this->session_state            = WC()->session->get('shipping_state');
    }

    public function render()
    {
        $this->set_session_attributes();

        ob_start();
        
        if(empty($this->session_quote_items)) {
            $message = 'Sua lista de produtos está vazia. <a href="' . esc_url(home_url('orcamento')) . '" class="button">Ver orçamento</a>';
            wc_add_notice($message, 'error');
        }
        wc_print_notices();

        if (is_user_logged_in()) {
            $customer = new WC_Customer(wp_get_current_user()->ID);

            $this->input_first_name = $customer->get_first_name();
            $this->input_last_name  = $customer->get_last_name();
            $this->input_company    = $customer->get_billing_company();
            $this->input_cnpj       = $customer->get_meta('billing_cnpj');
            $this->input_number     = $customer->get_meta('billing_number');
            $this->input_phone      = $customer->get_billing_phone();
            $this->input_email      = wp_get_current_user()->user_email;
        }

        ?>
        <form id="gcw-quote-container">

            <div id="gcw_quote_forms_container">

                <h2><?php echo esc_html(__("Empresa", "gestaoclick")); ?></h2>
                <section id="gcw-section-institution" class="gcw-quote-section">
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("Nome da empresa", "gestaoclick"); ?></label>
                        <input type="text" class="gcw-quote-input" name="gcw_company" required value="<?php esc_attr_e($this->input_company); ?>" />
                    </div>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("CNPJ", "gestaoclick"); ?></label>
                        <input type="text" name="gcw_cnpj" class="gcw-quote-input" id="gcw-cliente-cpf-cnpj" required value="<?php esc_attr_e($this->input_cnpj); ?>" />
                    </div>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("CEP", "gestaoclick"); ?></label>
                        <input type="text" name="gcw_postcode" class="gcw-quote-input" required disabled value="<?php esc_attr_e($this->session_postcode); ?>" />
                    </div>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("Nº do endereço", "gestaoclick"); ?></label>
                        <input type="text" name="gcw_number" class="gcw-quote-input" required value="<?php esc_attr_e($this->input_number); ?>" />
                    </div>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("Endereço", "gestaoclick"); ?></label>
                        <input type="text" name="gcw_address" class="gcw-quote-input" required disabled value="<?php esc_attr_e($this->session_address_html); ?>" />
                    </div>
                </section>

                <h2><?php echo esc_html(__("Responsável", "gestaoclick")); ?></h2>
                <section id="gcw-section-responsable" class="gcw-quote-section">
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("Primeiro nome", "gestaoclick"); ?></label>
                        <input type="text" name="gcw_first_name" class="gcw-quote-input" required value="<?php esc_attr_e($this->input_first_name); ?>" />
                    </div>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("Sobrenome", "gestaoclick"); ?></label>
                        <input type="text" name="gcw_last_name" class="gcw-quote-input" required value="<?php esc_attr_e($this->input_last_name); ?>" />
                    </div>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("Email", "gestaoclick"); ?></label>
                        <input type="email" name="gcw_email" class="gcw-quote-input" required value="<?php esc_attr_e($this->input_email); ?>" />
                    </div>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("Telefone", "gestaoclick"); ?></label>
                        <input type="text" name="gcw_phone" class="gcw-quote-input" required value="<?php esc_attr_e($this->input_phone); ?>" />
                    </div>
                </section>

            </div>

            <div id="gcw-quote-totals">

                <h2>Total do orçamento</h2>

                <section id="gcw-quote-checkout-package" class="gcw_quote_totals_section">
                    <span>Produtos</span>
                    <div id="gcw-quote-checkout-items-list">
                        <?php
                        if ($this->session_quote_items) {
                            foreach ($this->session_quote_items as $product) {
                                $product_name = wc_get_product($product['product_id'])->get_name();
                                $quantity = $product['quantity'];
                                echo '<p>' . esc_html($product_name . ' &times; ' . $quantity) . '</p>';
                            }
                        } else {
                            echo '<p>' . esc_html__('Sua lista de produtos está vazia.', 'gestaoclick') . '</p>';
                        }

                        ?>
                    </div>
                </section>

                <section id="gcw_quote_totals_subtotal" class="gcw_quote_totals_section gcw_quote_space_between">
                    <span>Subtotal</span>
                    <?php echo wc_price($this->session_subtotal); ?>
                </section>

                <section id="gcw_quote_shipping_address" class="gcw_quote_totals_section">
                    <div class="gcw_quote_space_between">
                        <span>Entrega</span>
                        <?php echo wc_price($this->session_shipping_cost); ?>
                    </div>
                    <p><?php echo $this->session_address_html; ?></p>
                </section>

                <section id="gcw_quote_totals_total" class="gcw_quote_totals_section gcw_quote_space_between">
                    <span>Total</span>
                    <?php echo wc_price($this->session_total); ?>
                </section>
                
                <section class="gcw_button_wrapper">
                    <button type="submit" class="gcw_button" name="gcw_finish_quote"><?php esc_html_e('Finalizar orçamento', 'gestaoclick'); ?></button>
                </section>

            </div>

        </form>
        <?php

        return ob_get_clean();
    }

    public function ajax_finish_quote()
    {
        $this->set_session_attributes();

        if(empty($this->session_quote_items) || !$this->session_total || !$this->session_subtotal || !$this->session_shipping_cost) {
            wp_send_json_error(array('message' => 'É preciso preencher todos os campos do orçamento.'));
            return;
        }

        // Valida e sanitiza os dados do formulário
        $this->input_first_name = sanitize_text_field($_POST['gcw_first_name']);
        $this->input_last_name  = sanitize_text_field($_POST['gcw_last_name']);
        $this->input_company    = sanitize_text_field($_POST['gcw_company']);
        $this->input_cnpj       = sanitize_text_field($_POST['gcw_cnpj']); // TODO: Checar CNPJ
        $this->input_phone      = sanitize_text_field($_POST['gcw_phone']);
        $this->input_number     = sanitize_text_field($_POST['gcw_number']);
        $this->input_email      = sanitize_email($_POST['gcw_email']);

        // Atualiza a lista de items com as respectivas customizações
        foreach ($this->session_quote_items as &$item)
        {
            $item['customizations'] = WC()->session->get("pcw_customizations_{$item['product_id']}");
        }

        // Cria ou resgata o usuário logado
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
        } 
        else 
        {
            if (email_exists($this->input_email)) 
            {
                wp_send_json_error(array('message' => 'Este e-mail já está registrado. Faça login para concluir.'));
                return;
            }

            $user_id = wp_create_user($this->input_email, wp_generate_password(), $this->input_email);
            if (is_wp_error($user_id))
            {
                wp_send_json_error(array('message' => 'Erro ao criar o usuário. Tente novamente.'));
                return;
            }

            // Atualiza os dados do usuário para o WordPress
            wp_update_user(array
            (
                'ID'            => $user_id,
                'first_name'    => $this->input_first_name,
                'last_name'     => $this->input_last_name,
            ));
        }

        $customer = new WC_Customer($user_id);
        $customer->set_billing_company($this->input_company);
        $customer->set_billing_phone($this->input_phone);
        $customer->set_billing_email($this->input_email);
        $customer->set_billing_first_name($this->input_first_name);
        $customer->set_billing_last_name($this->input_last_name);
        $customer->set_billing_postcode($this->session_postcode);
        $customer->set_billing_address_1($this->session_address_1);
        $customer->set_billing_city($this->session_city);
        $customer->set_billing_state($this->session_state);

        // Para o plugin: "Extra Checkout Fields for Brazil"
        update_user_meta($user_id, 'billing_number',        $this->input_number);
        update_user_meta($user_id, 'billing_neighborhood',  $this->session_neighborhood);
        update_user_meta($user_id, 'billing_cnpj',          $this->input_cnpj);
        $customer->save();

        // Conecta o usuário
        wp_set_auth_cookie($user_id);

        $gc_cliente = new GCW_GC_Cliente($customer, 'quote');
        $gc_cliente_id = $gc_cliente->export();

        $gc_orcamento = new GCW_GC_Orcamento($gc_cliente_id, $this->session_quote_items, $this->session_shipping_rate);
        $gc_orcamento_codigo = $gc_orcamento->export();
        $gc_orcamento->update('introducao', 'Gerado pelo site: ' . home_url('orcamentos/' . $gc_orcamento_codigo));

        // Criar um novo post do tipo 'quote'
        $quote_id = wp_insert_post(array
        (
            'post_title'  => $gc_orcamento_codigo,
            'post_status' => 'publish',
            'post_type'   => 'orcamento',
            'post_author' => $user_id
        ));

        // Marcar armazena os dados do orçamento
        update_post_meta($quote_id, 'gc_cliente_id', $gc_cliente_id);
        update_post_meta($quote_id, 'gc_codigo', $gc_orcamento_codigo);
        update_post_meta($quote_id, 'gc_url',    $gc_orcamento->get_url());
        update_post_meta($quote_id, 'status',   'Em aberto');
        update_post_meta($quote_id, 'tracking', 'Não enviado');
        update_post_meta($quote_id, 'total',    $this->session_total);
        update_post_meta($quote_id, 'subtotal', $this->session_subtotal);
        update_post_meta($quote_id, 'shipping', $this->session_shipping_rate);
        update_post_meta($quote_id, 'items',    $this->session_quote_items);

        // Limpar os dados do orçamento armazenados na seção.
        WC()->session->delete_session(get_current_user_id());

        wp_send_json_success(array
        (
            'message'       => 'Orçamento salvo e enviado com sucesso.',
            'redirect_url'  => get_permalink($quote_id)
        ));
    }
}
