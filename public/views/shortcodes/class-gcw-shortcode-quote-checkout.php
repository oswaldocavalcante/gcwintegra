<?php

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-orcamento.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-cliente.php';

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

        $first_name     = '';
        $last_name      = '';
        $company        = '';
        $cnpj           = '';
        $postcode       = '';
        $number         = '';
        $address_1      = '';
        $address_2      = '';
        $neighborhood   = '';
        $city           = '';
        $state          = '';
        $phone          = '';
        $email          = '';

        if (is_user_logged_in()) {
            $customer = new WC_Customer(wp_get_current_user()->ID);
            $first_name     = $customer->get_first_name();
            $last_name      = $customer->get_last_name();
            $company        = $customer->get_meta('billing_company');
            $cnpj           = $customer->get_meta('billing_cnpj');
            $postcode       = $customer->get_billing_postcode();
            $number         = $customer->get_meta('billing_number');
            $address_1      = $customer->get_billing_address_1();
            $address_2      = $customer->get_billing_address_1();
            $neighborhood   = $customer->get_meta('billing_neighborhood');
            $city           = $customer->get_billing_city();
            $state          = $customer->get_billing_state();
            $phone          = $customer->get_billing_phone();
            $email          = wp_get_current_user()->user_email;
        }
        ?>
            <form id="gcw-quote-container">

                <div id="gcw_quote_forms_container">
                    <h2><?php echo esc_html(__("Empresa", "gestaoclick")); ?></h2>
                    <section id="gcw-section-institution" class="gcw-quote-section">
                        <div class="gcw-field-wrap">
                            <label><?php echo esc_html_e("Nome da empresa", "gestaoclick"); ?></label>
                            <input type="text" class="gcw-quote-input" name="gcw_company" required value="<?php esc_attr_e($company); ?>" />
                        </div>
                        <div class="gcw-field-wrap">
                            <label><?php echo esc_html_e("CNPJ", "gestaoclick"); ?></label>
                            <input type="text" name="gcw_cnpj" class="gcw-quote-input" id="gcw-cliente-cpf-cnpj" required value="<?php esc_attr_e($cnpj); ?>" />
                        </div>
                        <div class="gcw-field-wrap">
                            <label><?php echo esc_html_e("CEP", "gestaoclick"); ?></label>
                            <input type="text" name="gcw_postcode" class="gcw-quote-input" required disabled value="<?php esc_attr_e($postcode); ?>" />
                        </div>
                        <div class="gcw-field-wrap">
                            <label><?php echo esc_html_e("Nº do endereço", "gestaoclick"); ?></label>
                            <input type="text" name="gcw_number" class="gcw-quote-input" requiredvalue="<?php esc_attr_e($number); ?>" />
                        </div>
                        <div class="gcw-field-wrap">
                            <label><?php echo esc_html_e("Endereço", "gestaoclick"); ?></label>
                            <input type="text" name="gcw_postcode" class="gcw-quote-input" required disabled value="<?php esc_attr_e($postcode); ?>" />
                        </div>
                    </section>

                    <h2><?php echo esc_html(__("Responsável", "gestaoclick")); ?></h2>
                    <section id="gcw-section-responsable" class="gcw-quote-section">
                        <div class="gcw-field-wrap">
                            <label><?php echo esc_html_e("Primeiro nome", "gestaoclick"); ?></label>
                            <input type="text" name="gcw_first_name" class="gcw-quote-input" required value="<?php esc_attr_e($first_name); ?>" />
                        </div>
                        <div class="gcw-field-wrap">
                            <label><?php echo esc_html_e("Sobrenome", "gestaoclick"); ?></label>
                            <input type="text" name="gcw_last_name" class="gcw-quote-input" required value="<?php esc_attr_e($last_name); ?>" />
                        </div>
                        <div class="gcw-field-wrap">
                            <label><?php echo esc_html_e("Email", "gestaoclick"); ?></label>
                            <input type="email" name="gcw_email" class="gcw-quote-input" required value="<?php esc_attr_e($email); ?>" />
                        </div>
                        <div class="gcw-field-wrap">
                            <label><?php echo esc_html_e("Telefone", "gestaoclick"); ?></label>
                            <input type="text" name="gcw_phone" class="gcw-quote-input" required value="<?php esc_attr_e($phone); ?>" />
                        </div>
                    </section>
                </div>

                <div id="gcw-quote-totals">
                    <button type="submit" class="button" id="gcw_finish_quote_button"><?php esc_html_e('Finalizar orçamento', 'gestaoclick'); ?></button>
                </div>

            </form>
        <?php

        return ob_get_clean();
    }

    public function ajax_finish_quote() //TODO: Enviar ao GestãoClick
    {
        // Valida e sanitiza os dados do formulário
        $first_name     = sanitize_text_field($_POST['gcw_first_name']);
        $last_name      = sanitize_text_field($_POST['gcw_last_name']);
        $company        = sanitize_text_field($_POST['gcw_company']);
        $cnpj           = sanitize_text_field($_POST['gcw_cnpj']);
        $postcode       = sanitize_text_field($_POST['gcw_postcode']);
        $number         = sanitize_text_field($_POST['gcw_number']);
        $address_1      = sanitize_text_field($_POST['gcw_address_1']);
        $address_2      = sanitize_text_field($_POST['gcw_address_2']);
        $neighborhood   = sanitize_text_field($_POST['gcw_neighborhood']);
        $city           = sanitize_text_field($_POST['gcw_city']);
        $state          = sanitize_option('gcw_state', $_POST['gcw_state']);
        $phone          = sanitize_text_field($_POST['gcw_phone']);
        $email          = sanitize_email($_POST['gcw_email']);

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
        }
        else {
            if (email_exists($email)) {
                wp_send_json_error(array('message' => 'Este e-mail já está registrado.'));
                return;
            }

            $user_id = wp_create_user($email, wp_generate_password(), $email);
            if (is_wp_error($user_id)) {
                wp_send_json_error(array('message' => 'Erro ao criar o usuário. Tente novamente.'));
                return;
            }

            // Atualiza os dados do usuário para o WordPress
            wp_update_user(array(
                'ID'            => $user_id,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
            ));
        }

        $customer = new WC_Customer($user_id);
        $customer->set_billing_company($company);
        $customer->set_billing_phone($phone);
        $customer->set_billing_email($email);
        $customer->set_billing_first_name($first_name);
        $customer->set_billing_last_name($last_name);
        $customer->set_billing_postcode($postcode);
        $customer->set_billing_address_1($address_1);
        $customer->set_billing_address_2($address_2);
        $customer->set_billing_city($city);
        $customer->set_billing_state($state);

        // Para o plugin: "Extra Checkout Fields for Brazil"
        update_user_meta($user_id, 'billing_number',        $number);
        update_user_meta($user_id, 'billing_neighborhood',  $neighborhood);
        update_user_meta($user_id, 'billing_company',       $company);
        update_user_meta($user_id, 'billing_cnpj',          $cnpj);
        $customer->save();

        $gc_cliente = new GCW_GC_Cliente($customer, 'quote');
        $gc_cliente->export();

        // Conecta o usuário
        wp_set_auth_cookie($user_id);

        $quote_items = isset($_SESSION['quote_items']) ? $_SESSION['quote_items'] : array();

        $gc_orcamento = new GCW_GC_Orcamento($quote_items, $gc_cliente->get_id(), 'quote');
        $gc_orcamento->export();

        // Criar um novo post do tipo 'quote'
        $quote_id = wp_insert_post(array(
            'post_title'  => 'Orçamento ',
            'post_status' => 'draft',
            'post_type'   => 'quote',
            'post_author' => $user_id
        ));

        wp_update_post(array(
            'ID' => $quote_id,
            'post_title'  => 'Orçamento ' . $quote_id,
        ));

        // Marcar a cotação como aberta
        update_post_meta($quote_id, 'status', 'open');
        update_post_meta($quote_id, 'items', $quote_items);

        // Limpar os itens do orçamento da sessão
        unset($_SESSION);

        wp_send_json_success(array(
            'message' => 'Orçamento salvo e enviado com sucesso.',
            'redirect_url' => get_permalink($quote_id)
        ));
    }
}
            // Adiciona o parâmetro de redirecionamento
            // $redirect_url = wc_get_page_permalink('myaccount') . '?redirect_to=' . (home_url('orcamento'));