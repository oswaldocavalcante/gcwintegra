<?php

class GCW_Shortcode_Quote_Checkout
{
    public function __construct()
    {
        add_action('wp_ajax_gcw_register_user',         array($this, 'ajax_register_user'));
        add_action('wp_ajax_nopriv_gcw_register_user',  array($this, 'ajax_register_user'));

        add_action('wp_ajax_gcw_save_quote',        array($this, 'ajax_finish_quote'));
        add_action('wp_ajax_nopriv_gcw_save_quote', array($this, 'ajax_finish_quote'));
    }

    public function render()
    {
        ob_start();
        wc_print_notices();

        if (class_exists('WooCommerce')) {
            // Obtém o objeto de checkout do WooCommerce
            $checkout = WC()->checkout();
            ?>
                <div id="gcw-quote-container">
                    <div id="gcw_quote_forms_container">
                        <div id="gcw_quote_user_registration" class="woocommerce">
                            <form name="checkout" method="post" class="checkout woocommerce-checkout">
                                <div class="woocommerce-billing-fields">
                                    <h3><?php esc_html_e('Billing Details', 'woocommerce'); ?></h3>

                                    <?php do_action('woocommerce_before_checkout_billing_form', $checkout); ?>

                                    <div class="woocommerce-billing-fields__field-wrapper">
                                        <?php
                                        // Exibe os campos de faturamento
                                        foreach ($checkout->get_checkout_fields('billing') as $key => $field) {
                                            woocommerce_form_field($key, $field, $checkout->get_value($key));
                                        }
                                        ?>
                                    </div>

                                    <?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div id="gcw-quote-totals">
                        <button type="submit" class="button" id="place_order"><?php esc_html_e('Submit', 'woocommerce'); ?></button>
                    </div>
                </div>
            <?php
        }

        return ob_get_clean();
    }

    public function ajax_register_user()
    {
        // Valida e sanitiza os dados do formulário
        $email          = sanitize_email($_POST['gcw_contato_email']);
        $nome           = sanitize_text_field($_POST['gcw_contato_nome']);
        $telefone       = sanitize_text_field($_POST['gcw_contato_telefone']);
        $nome_fantasia  = sanitize_text_field($_POST['gcw_cliente_nome']);
        $cpf_cnpj       = sanitize_text_field($_POST['gcw_cliente_cpf_cnpj']);

        // Verifica se o email já está registrado
        if (email_exists($email)) {
            wp_send_json_error(array('message' => 'Este e-mail já está registrado.'));
        }

        // Cria o usuário
        $user_id = wp_create_user($email, wp_generate_password(), $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => 'Erro ao criar o usuário. Tente novamente.'));
        }

        // Atualiza os dados do usuário
        wp_update_user(array(
            'ID'            => $user_id,
            'first_name'    => $nome,
            'last_name'     => $nome,
        ));

        $customer = new WC_Customer($user_id);
        $customer->set_billing_company($nome_fantasia);
        $customer->set_billing_phone($telefone);
        $customer->set_billing_email($email);
        $customer->set_billing_first_name($nome);

        // Atualiza os meta dados do usuário
        update_user_meta($user_id, 'telefone',      $telefone);
        update_user_meta($user_id, 'nome_fantasia', $nome_fantasia);
        update_user_meta($user_id, 'cpf_cnpj',      $cpf_cnpj);

        // Conecta o usuário
        wp_set_auth_cookie($user_id);

        wp_send_json_success(array('redirect_url' => home_url('orcamento')));
    }

    public function ajax_finish_quote() //TODO: Enviar ao GestãoClick
    {
        $user_id = get_current_user_id();
        $customer = new WC_Customer($user_id);

        if (!is_user_logged_in()) {
            // Adiciona o parâmetro de redirecionamento
            // $redirect_url = wc_get_page_permalink('myaccount') . '?redirect_to=' . (home_url('orcamento'));
            wp_send_json_error(array('message' => 'Você precisa estar logado para enviar o orçamento.'));

            return;
        }

        if (!isset($_SESSION['has_selected_shipping_method'])) {
            wp_send_json_error(array('message' => 'Você precisa selecionar um método de envio.'));

            return;
        }

        $user_data = array(
            'tipo_pessoa'   => $customer->get_meta('billing_persontype'),
            'cnpj'          => $customer->get_meta('billing_cnpj') ? $customer->get_meta('billing_cnpj') : "",
            'cpf'           => $customer->get_meta('billing_cpf') ? $customer->get_meta('billing_cpf') : "",
            'nome'          => $customer->get_meta('nome_fantasia'),
            'email'         => '',
            'telefone'      => '',
            'contatos'      => [
                'contato'   => [
                    'nome'          =>  $customer->get_meta('nome_fantasia'),
                    'observacao'    =>  '' .
                        '',
                ],
            ],
        );

        $quote_items = isset($_SESSION['quote_items']) ? $_SESSION['quote_items'] : array();

        // Criar um novo post do tipo 'quote'
        $quote_id = wp_insert_post(array(
            'post_title'  => 'Orçamento ' . current_time('Y-m-d H:i:s'),
            'post_status' => 'draft',
            'post_type'   => 'quote',
            'post_author' => $user_id
        ));

        // Marcar a cotação como aberta
        update_post_meta($quote_id, 'status', 'open');
        update_post_meta($quote_id, 'items', $quote_items);

        // Limpar os itens do orçamento da sessão
        unset($_SESSION['quote_items']);

        wp_send_json_success(array('redirect_url' => get_permalink($quote_id)));
    }
}
