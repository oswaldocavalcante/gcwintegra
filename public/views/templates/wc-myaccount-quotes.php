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
                <?php foreach (wc_get_account_orders_columns() as $column_id => $column_name) : ?>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr($column_id); ?>"><span class="nobr"><?php echo esc_html($column_name); ?></span></th>
                <?php endforeach; ?>
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
                    <td><?php echo wc_price(get_post_meta(get_the_ID(), 'total', true)); ?></td>
                    <td class="gwc-wc-myaccount-quotes-actions"><a href="<?php echo get_the_permalink(); ?>" class="woocommerce-button button view">Visualizar</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

<?php else : ?>
    <p>Você ainda não possui orçamentos.</p>
<?php endif; ?>