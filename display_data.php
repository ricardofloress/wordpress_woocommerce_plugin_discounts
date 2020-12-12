<?php
global $wpdb;

$table_name = $wpdb->prefix . 'pwu_price_discounts';

$discount_results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY customer_id" );
?>
<div style='width: 95%'>
    <h1 style='display: block; margin-bottom: 50px;'>Descontos Categoria Cliente - Plug With Us</h1>
    <?php
if ( isset( $_GET['saved'] ) ) echo
'<div class="updated" style="margin: 5px 0px 25px;">
  <p>' . __( 'Desconto Alterado com sucesso!' ) . '</p>
</div>';
?>

    <?php
if ( isset( $_GET['error'] ) ) echo
'<div class="notice notice-error" style="margin: 5px 0px 25px;">
  <p>' . __( 'Não foi possível alterar o desconto!' ) . '</p>
</div>';
?>
    <table class='wp-list-table widefat fixed striped table-view-list'>
        <tr>
            <th class='manage-column'>Cliente</th>
            <th class='manage-column'>Categoria</th>
            <th class='manage-column'>Desconto</th>
            <th class='manage-column'>Ação</th>

        </tr>
        <?php
foreach ( $discount_results as $discount ) {
    ?>
        <tr>
            <form method='post'>
                <td><?php echo get_user_by( 'id', $discount->customer_id )->display_name ?></td>
                <input type='hidden' name='user_id' value="<?php echo $discount->customer_id ?>">
                <td><?php echo get_term_by( 'id', $discount->category_id, 'product_cat' )->name ?></td>
                <input type='hidden' name='category_id' value="<?php echo $discount->category_id ?>">
                <td><input type='number' name='discount' value="<?php echo ($discount->discount * 100 ) ?>" /> %</td>
                <td><button type='submit' name='edit_discount'>Alterar</button></td>
            </form>
        </tr>
        <?php
}
?>
    </table>
    <br>
</div>
<?php

function update_discount() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'pwu_price_discounts';

    $user_id = $_POST['user_id'];
    $category_id = $_POST['category_id'];
    $discount = ( $_POST['discount'] )/100;

    if ( isset( $_POST['edit_discount'] ) ) {

        $execut = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET discount = $discount WHERE customer_id =  $user_id AND category_id = $category_id" ) );

        if ( $execut ) {
            //echo '<script type="text/javascript">alert("Desconto Alterado!");</script>';
            /*$success_msg = '<div class="notice notice-success is-dismissible">
            <p>Desconto Alterado!</p>
            <button type="button" class="notice-dismiss">
            <span class="screen-reader-text">Descartar este aviso.</span>
            </button>
            </div>';
            //header( 'Refresh:0' );

            $message = __( $success_msg, 'pwu_discount' );
            */
            //show_message( $message, 'error' );
            /*include_once( 'plugwithus-discount.php' );
            add_action( 'admin_notices', 'sample_admin_notice__success' );
            */
            wp_redirect( 'admin.php?page=pwu-discount' . '&saved=1' );

        } else {
            wp_redirect( 'admin.php?page=pwu-discount' . '&error=1' );

        }
    }
}

?>