<?php
$users = get_users();
$orderby = 'name';
$order = 'asc';
$hide_empty = false;
$cat_args = array(
    'orderby' => $orderby,
    'order' => $order,
    'hide_empty' => $hide_empty,
);
$categories = get_terms( 'product_cat', $cat_args );
?>
<h1 style='display: block; margin-bottom: 50px;'>Adicionar Desconto</h1>
<form method='post'>
    <div style='display: block; margin-bottom: 20px;'>

        <label for='user_id'>Cliente</label>
        <select id='user_id' name='user_id'>
            <?php
foreach ( $users as $user ) {
    ?>
            <option value='<?php echo $user->id?>'><?php echo $user->display_name?></option>
            <?php
}
?>
        </select>
    </div>
    <div style='display: block; margin-bottom: 20px;'>

        <label for='category_id'>Categoria</label>
        <select id='category_id' name='category_id'>
            <?php
foreach ( $categories as $category ) {
    ?>
            <option value='<?php echo $category->term_id?>'><?php echo $category->name?></option>
            <?php
}
?>

        </select>
    </div>

    <div style='display: block; margin-bottom: 20px;'>
        <div style='display: block; margin-bottom: 20px;'>
            <label for='discount'>Desconto ( 1% a 100% )</label>
            <input id='discount' name='discount' type='number' step='.01'>
        </div>
        <button type='submit' name='add_discount'>Adicionar Disconto</button>
</form>

<?php

function insert_new_discount() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'pwu_price_discounts';

    $user_id = $_POST['user_id'];
    $category_id = $_POST['category_id'];
    $discount = ( $_POST['discount'] )/100;

    if ( isset( $_POST['add_discount'] ) ) {

        $execut = $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET discount = $discount WHERE customer_id =  $user_id AND category_id = $category_id" ) );

        if ( $execut ) {
            echo '<script type="text/javascript">alert("Novo Desconto Adicionado!");</script>';
        } else {
            echo '<script type="text/javascript">alert("Desconto já existente por favor edite na tabela principal!");</script>';

        }
    }
}

?>