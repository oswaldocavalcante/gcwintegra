<?php

require_once GCW_ABSPATH . 'integrations/gestaoclick/class-gcw-gc-api.php';

class GCW_Shortcode_Quote_Form
{
    public function render()
    {
        ob_start();
        ?>

        <form method="post">

            <h2><?php echo esc_html(__("Instituição", "gestaoclick")); ?></h2>
            <section id="gcw-section-institution" class="gcw-quote-section">
                <div class="gcw-field-wrap">
                    <label><?php echo esc_html(__("Nome fantasia", "gestaoclick")); ?></label>
                    <input type="text" class="gcw-quote-input" name="gcw_cliente_nome" required />
                </div>
                <div class="gcw-field-wrap">
                    <label><?php echo esc_html(__("CNPJ/CPF", "gestaoclick")); ?></label>
                    <input type="text" class="gcw-quote-input" name="gcw_cliente_cpf_cnpj" id="gcw-cliente-cpf-cnpj" required />
                </div>
            </section>

            <h2><?php echo esc_html(__("Responsável", "gestaoclick")); ?></h2>
            <section id="gcw-section-responsable" class="gcw-quote-section">
                <div class="gcw-field-wrap">
                    <label><?php echo esc_html(__("Nome e sobrenome", "gestaoclick")); ?></label>
                    <input type="text" name="gcw_contato_nome" class="gcw-quote-input" required />
                </div>
                <div class="gcw-field-wrap">
                    <label><?php echo esc_html(__("Email", "gestaoclick")); ?></label>
                    <input type="email" name="gcw_contato_email" class="gcw-quote-input" required />
                </div>
                <div class="gcw-field-wrap">
                    <label><?php echo esc_html(__("Telefone", "gestaoclick")); ?></label>
                    <input type="text" name="gcw_contato_telefone" class="gcw-quote-input" required />
                </div>
                <div class="gcw-field-wrap">
                    <label><?php echo esc_html(__("Cargo", "gestaoclick")); ?></label>
                    <input type="text" name="gcw_contato_cargo" class="gcw-quote-input" required />
                </div>
            </section>

            <h2><?php echo esc_html(__("Orçamento", "gestaoclick")); ?></h2>
            <section id="gcw-quote-section-items">
                <fieldset id="gcw-quote-fieldset-1" class="gcw-quote-fieldset">
                    <legend class="gcw-quote-fieldset-legend">
                        <?php echo esc_html(__("Item 1", "gestaoclick")); ?>
                    </legend>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html(__("Nome", "gestaoclick")); ?></label>
                        <input type="text" class="gcw-quote-name gcw-quote-input" name="gcw_item_nome-1" required />
                    </div>
                    <div class="gcw-field-wrap">
                        <label><?php echo esc_html(__("Descrição", "gestaoclick")); ?></label>
                        <input type="text" class="gcw-quote-description gcw-quote-input" name="gcw_item_descricao-1" required />
                    </div>
                    <div class="gcw-field-wrap gcw-field-size">
                        <label><?php echo esc_html(__("Tamanho", "gestaoclick")); ?></label>
                        <select class="gcw-quote-size gcw-quote-input" name="gcw_item_tamanho-1" required>
                            <option value="<?php echo esc_html(__(" Selecionar", "gestaoclick")); ?>" selected="selected">
                                <?php echo esc_html(__("Selecionar", "gestaoclick")); ?>
                            </option>
                            <option value="PP">PP</option>
                            <option value="P">P</option>
                            <option value="M">M</option>
                            <option value="G">G</option>
                            <option value="GG">GG</option>
                            <option value="XG">XG</option>
                            <option value="XGG">XGG</option>
                            <option value="PS">Plus Size</option>
                        </select>
                    </div>
                    <div class="gcw-field-wrap gcw-field-quantity">
                        <label><?php echo esc_html(__("Quantidade", "gestaoclick")); ?></label>
                        <input type="number" class="gcw-quote-quantity gcw-quote-input" name="gcw_item_quantidade-1" required value="10" min="10" inputmode="numeric" pattern="\d*" />
                    </div>
                    <a class="gcw-quote-button-remove" item_id="1">×</a>
                </fieldset>
            </section>
            <a id="gcw-quote-add-item"><?php echo esc_html(__("Adicionar item", "gestaoclick")); ?></a>

            <input type="submit" id="gcw-quote-send" value="<?php echo esc_html(__("Solicitar orçamento", "gestaoclick")); ?>">

            <?php wp_nonce_field('gcw_form_orcamento', 'gcw_nonce_orcamento'); ?>
        </form>

        <?php
        return ob_get_clean();
    }
}
