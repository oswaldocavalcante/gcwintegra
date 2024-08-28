<?php

$orcamentos_query = new WP_Query(array(
    'post_type'     => 'orcamento',
    'post_status'     => 'publish',
    'author'        => get_current_user_id(),
)); ?>

<?php if ($orcamentos_query->have_posts()) : ?>

    <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
        <thead>
            <tr>
                <th class="woocommerce-orders-table__header">Orçamento</td>
                <th class="woocommerce-orders-table__header">Data</td>
                <th class="woocommerce-orders-table__header">Situação</td>
                <th class="woocommerce-orders-table__header">Envio</td>
                <th class="gwc-wc-myaccount-quotes-actions">Detalhes</td>

            </tr>
        </thead>

        <tbody>

            <?php while ($orcamentos_query->have_posts()) :
                $orcamentos_query->the_post();
                ?>
                <tr class="woocommerce-orders-table__row">
                    <td class="woocommerce-orders-table__cell"><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></td>
                    <td><?php echo get_the_date(); ?></td>
                    <td><?php echo get_post_meta(get_the_ID(), 'status', true); ?></td>
                    <td><?php echo get_post_meta(get_the_ID(), 'tracking', true); ?></td>
                    <td class="gwc-wc-myaccount-quotes-actions"><a href="<?php echo get_the_permalink(); ?>" class="woocommerce-button button view">Visualizar</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

<?php else : ?>
    <p>Você ainda não possui orçamentos.</p>
<?php endif; ?>