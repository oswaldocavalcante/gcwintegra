<?php

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-orcamento.php';
require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-cliente.php';

class GCW_Shortcode_Quote_Checkout
{
    public function __construct()
    {
        add_action('wp_ajax_gcw_finish_quote',         array($this, 'ajax_finish_quote'));
        add_action('wp_ajax_nopriv_gcw_finish_quote',  array($this, 'ajax_finish_quote'));
    }

    public function render()
    {
        ob_start();
        wc_print_notices();

        $subtotal_price = isset($_SESSION['quote_subtotal_price']) ? $_SESSION['quote_subtotal_price'] : '';
        $shipping_cost  = isset($_SESSION['quote_shipping_cost']) ? $_SESSION['quote_shipping_cost'] : '';
        $total_price    = isset($_SESSION['quote_total_price']) ? $_SESSION['quote_total_price'] : '';
        $address_html   = isset($_SESSION['shipping_address_html']) ? $_SESSION['shipping_address_html'] : '';
        $postcode       = isset($_SESSION['shipping_postcode']) ? $_SESSION['shipping_postcode'] : '';
        $quote_items    = isset($_SESSION['quote_items']) ? $_SESSION['quote_items'] : '';

        $first_name     = '';
        $last_name      = '';
        $company        = '';
        $cnpj           = '';
        $number         = '';
        $phone          = '';
        $email          = '';

        if (is_user_logged_in()) {
            $customer = new WC_Customer(wp_get_current_user()->ID);

            $first_name     = $customer->get_first_name();
            $last_name      = $customer->get_last_name();
            $company        = $customer->get_billing_company();
            $cnpj           = $customer->get_meta('billing_cnpj');
            $number         = $customer->get_meta('billing_number');
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
                        <input type="text" name="gcw_number" class="gcw-quote-input" required value="<?php esc_attr_e($number); ?>" />
                    </div>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html_e("Endereço", "gestaoclick"); ?></label>
                        <input type="text" name="gcw_address" class="gcw-quote-input" required disabled value="<?php esc_attr_e($address_html); ?>" />
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

                <h2>Total do orçamento</h2>
                <section id="gcw-quote-checkout-package" class="gcw_quote_totals_section">
                    <span>Produtos</span>
                    <div id="gcw-quote-checkout-items-list">
                        <?php
                        if($quote_items) {
                            foreach ($quote_items as $product) {
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
                    <?php echo wc_price($subtotal_price); ?>
                </section>
                <section id="gcw_quote_shipping_address" class="gcw_quote_totals_section">
                    <div class="gcw_quote_space_between">
                        <span>Entrega</span>
                        <?php echo wc_price($shipping_cost); ?>
                    </div>
                    <p><?php echo $address_html; ?></p>
                </section>
                <section id="gcw_quote_totals_total" class="gcw_quote_totals_section gcw_quote_space_between">
                    <span>Total</span>
                    <?php echo wc_price($total_price); ?>
                </section>
                <section id="gcw_quote_totals_finish">
                    <button type="submit" class="button" id="gcw_finish_quote_button" name="gcw_finish_quote"><?php esc_html_e('Finalizar orçamento', 'gestaoclick'); ?></button>
                </section>

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
        $cnpj           = sanitize_text_field($_POST['gcw_cnpj']); // TODO: Checar CNPJ
        $phone          = sanitize_text_field($_POST['gcw_phone']);
        $number         = sanitize_text_field($_POST['gcw_number']);
        $email          = sanitize_email($_POST['gcw_email']);

        $postcode       = isset($_SESSION['shipping_postcode']) ? $_SESSION['shipping_postcode'] : '';
        $address_1      = isset($_SESSION['shipping_address_1']) ? $_SESSION['shipping_address_1'] : '';
        $neighborhood   = isset($_SESSION['shipping_neigborhood']) ? $_SESSION['shipping_neigborhood'] : '';
        $city           = isset($_SESSION['shipping_city']) ? $_SESSION['shipping_city'] : '';
        $state          = isset($_SESSION['shipping_state']) ? $_SESSION['shipping_state'] : '';

        $quote_items    = isset($_SESSION['quote_items']) ? $_SESSION['quote_items'] : array();

        // Cria ou resgata o usuário logado
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
        } else {
            if (email_exists($email)) {
                wp_send_json_error(array('message' => 'Este e-mail já está registrado. Faça login para concluir.'));
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
        $customer->set_billing_city($city);
        $customer->set_billing_state($state);

        // Para o plugin: "Extra Checkout Fields for Brazil"
        update_user_meta($user_id, 'billing_number',        $number);
        update_user_meta($user_id, 'billing_neighborhood',  $neighborhood);
        update_user_meta($user_id, 'billing_cnpj',          $cnpj);
        $customer->save();

        // Conecta o usuário
        wp_set_auth_cookie($user_id);

        $gc_cliente = new GCW_GC_Cliente($customer, 'quote');
        $gc_cliente->export();

        $gc_orcamento = new GCW_GC_Orcamento($quote_items, $gc_cliente->get_id(), 'quote');
        $gc_orcamento_codigo = $gc_orcamento->export();

        // Criar um novo post do tipo 'quote'
        $quote_id = wp_insert_post(array(
            'post_title'  => 'Orçamento ' . $gc_orcamento_codigo,
            'post_status' => 'publish',
            'post_type'   => 'orcamento',
            'post_author' => $user_id
        ));

        // Marcar a cotação como aberta e armazena os itens do orçamento
        update_post_meta($quote_id, 'status',       'open');
        update_post_meta($quote_id, 'gc_codigo',    $gc_orcamento_codigo);
        update_post_meta($quote_id, 'items',        $quote_items);

        // Limpar os dados do orçamento armazenados na seção.
        session_unset();

        wp_send_json_success(array(
            'message' => 'Orçamento salvo e enviado com sucesso.',
            'redirect_url' => get_permalink($quote_id)
        ));
    }
}