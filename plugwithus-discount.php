<?php
/**
* Plugin Name: PWU Discount
* Plugin URI: https://www.plugwithus.com/
* Description: This plugin aplies discount by category deppending on the user that its current logged in: The shop manager has the possibility to give disccount to a client in different categories.
* Version: 1.0
* Author: Plug With Us
* Author URI: http://plugwithus.com/
**/

global $pwu_db_version;
$pwu_db_version = '1.0';

/**
* Esta funcção adiciona o menu e os submenus para o plugin
*/

function PWU_PriceAndDiscounts_setup_menu()
 {
    //menu principal
    add_menu_page( 'Descontos', 'PWU Descontos', 'manage_options', 'pwu-discount', 'display_data_page' );
    //submenu
    // add_submenu_page( 'pwu-discount', ' Adicionar Desconto', 'Adicionar Desconto', 'manage_options', 'add-discount', 'add_discount_page' );
}

add_action( 'admin_menu', 'PWU_PriceAndDiscounts_setup_menu' );

/**
* Cria a tabela na base de dados quando o plugin é activado
*/

function pwu_install()
 {
    //variavel global da base de dados do wordpress
    global $wpdb;
    global $pwu_db_version;

    //nome da tabela com o prefixo da base de dados
    $table_name = $wpdb->prefix . 'pwu_price_discounts';

    $charset_collate = $wpdb->get_charset_collate();

    //query
    $sql = "CREATE TABLE $table_name (
        id int (11) NOT NULL AUTO_INCREMENT,
        customer_id int (11) NOT NULL,
        category_id int(11) NOT NULL,
        discount DECIMAL(10,2) NOT NULL,
        PRIMARY KEY(id),
        CONSTRAINT unique_customer_category UNIQUE(customer_id, category_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    //executa e cria a base de dados
    dbDelta( $sql );

    add_option( 'pwu_db_version', $pwu_db_version );
}

register_activation_hook( __FILE__, 'pwu_install' );

/**
* Ao ativar o plugin vai criar para os utilizador existentes descontos por categprias mas tudo a 0
*/

function pwu_install_data()
 {
    //variavel global da base de dados do wordpress
    global $wpdb;
    //nome da tabela com o prefixo da base de dados
    $table_name = $wpdb->prefix . 'pwu_price_discounts';
    //argumentos para a pesquisa de categorias
    $orderby = 'name';
    $order = 'asc';
    $hide_empty = false;
    $cat_args = array(
        'orderby' => $orderby,
        'order' => $order,
        'hide_empty' => $hide_empty,
    );
    //vamos buscar as categorias aos termos
    $product_categories = get_terms( 'product_cat', $cat_args );
    //vamos buscar todos os usuarios
    $users = get_users();
    if ( !empty( $product_categories ) && !empty( $users ) ) {
        //precorremos ambos os arrays de categorias e usuarios
        foreach ( $users as $user ) {
            foreach ( $product_categories as $key => $category ) {
                //fazemos o insert de um novo desconto a 0 para o respetivo id de categoria e usuario
                $query = $wpdb->insert( $table_name,
                array(
                    'customer_id' => $user->id,
                    'category_id' => $category->term_id,
                    'discount' => 0
                ) );
            }

        }
    }

}

register_activation_hook( __FILE__, 'pwu_install_data' );

/**
* Elimnina a tabela na base de dados quando o plugin é desactivado
*/

function pwu_uninstall()
 {
    //variavel global da base de dados do wordpress
    global $wpdb;

    //nome da tabela com o prefixo da base de dados
    $table_name = $wpdb->prefix . 'pwu_price_discounts';

    //Executa a query e faz Drop da tabela
    $sql = $wpdb->query( "DROP TABLE $table_name" );

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $sql );
}

register_deactivation_hook( __FILE__, 'pwu_uninstall' );

/**
* Esta função quando existe um user criado, cria todos os registos na tabela de descontos para todos as categorias no utilizador criado
*
* @param number o id do utilizador
*/

function when_user_create( $user_id )
 {
    //variavel global da base de dados do wordpress
    global $wpdb;
    //nome da tabela com o prefixo da base de dados
    $table_name = $wpdb->prefix . 'pwu_price_discounts';
    //argumentos para a pesquisa de categorias
    $orderby = 'name';
    $order = 'asc';
    $hide_empty = false;
    $cat_args = array(
        'orderby' => $orderby,
        'order' => $order,
        'hide_empty' => $hide_empty,
    );
    //vamos buscar as categorias aos termos
    $product_categories = get_terms( 'product_cat', $cat_args );
    //vamos buscar todos os usuarios
    if ( !empty( $product_categories ) ) {
        //precorremos o array de categorias
        foreach ( $product_categories as $key => $category ) {
            //fazemos o insert de um novo desconto a 0 para o respetivo id de categoria e novo usuario
            $query = $wpdb->insert( $table_name,
            array(
                'customer_id' => $user_id,
                'category_id' => $category->term_id,
                'discount' => 0
            ) );
        }
    }
}

add_action( 'user_register', 'when_user_create', 10, 1 );

/**
* Esta função quando existe um user elimindado, elimina todos os registos na tabela de descontos com o customer_id
*
* @param number o id do utilizador
*/
add_action( 'delete_user', 'when_user_delete' );

function when_user_delete( $user_id ) {
    //variavel global da base de dados do wordpress
    global $wpdb;
    //nome da tabela com o prefixo da base de dados
    $table_name = $wpdb->prefix . 'pwu_price_discounts';
    //faz o delete na base de dados na tabela de discontos onde exista aquele user_id
    $removefromdb = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE customer_id = $user_id" ) );
    //$wpdb->delete( $table_name, array( 'customer_id' => $user_id ) );
}

/**
* Esta função quando existe uma categoria criada, cria todos os registos na tabela de descontos para todos os users
*
* @param number o id do termo da categoria
*/
add_action( 'create_product_cat', 'when_category_create' );

function when_category_create( $term_id ) {
    //variavel global da base de dados do wordpress
    global $wpdb;
    //nome da tabela com o prefixo da base de dados
    $table_name = $wpdb->prefix . 'pwu_price_discounts';
    //vamos buscar todos os usuarios
    $users = get_users();
    if ( !empty( $users ) ) {
        //precorremos o array de usuarios
        foreach ( $users as $user ) {
            //fazemos o insert de um novo desconto a 0 para o respetivo id de categoria e usuario
            $query = $wpdb->insert( $table_name,
            array(
                'customer_id' => $user->id,
                'category_id' => $term_id,
                'discount' => 0
            ) );
        }
    }

}

/**
* Esta função quando existe uma categoria eliminada elimina todos os registos da tabela de descontos
*
* @param number o id do termo da categoria
*/
add_action( 'delete_product_cat', 'when_category_delete' );

function when_category_delete( $term_id ) {
    //variavel global da base de dados do wordpress
    global $wpdb;
    //nome da tabela com o prefixo da base de dados
    $table_name = $wpdb->prefix . 'pwu_price_discounts';
    //Fazmos o delete na tabela na base de dados onde existe aquela categoria
    $removefromdb = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE category_id = $term_id" ) );
}

/**
* Pagina de adicionar desconto
*/

function add_discount_page() {
    include_once( 'insert_discounts.php' );
    insert_new_discount();
}

/**
* Pagina de ver data
*/

function display_data_page() {
    include_once( 'display_data.php' );
    update_discount();
}

/**
* Esta função coloca o preço com desconto de categoria/cliente no carrinho de compras
*
* @param object  objecto do carrinho de compras
*/

function prod_new_price( $cart_object ) {
    
    //variavel global da base de dados do wordpress
    global $wpdb;
    //Usuario do momento
    $current_user = wp_get_current_user();
    //nome da tabela com o prefixo da base de dados
    $table_name = $wpdb->prefix . 'pwu_price_discounts';
    //vai precorrer todos os items no carrinho de compras

    foreach ($cart_object->cart_contents as $key => $value) {

        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return;

        if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
            return;

        //id do produto do carrinho
        $product_id = $value['product_id'];
        //id de categoria( vai buscar a tabela dos termos na base de dados por 'product_cat' pelo produto id )
        $category_id = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) )[0];
        //faz um select na base de dados de descontos e retira o desconto para o aplicar
        $discount = $wpdb->get_results( "SELECT * FROM $table_name WHERE customer_id = $current_user->id AND category_id=$category_id " )[0]->discount;

        $price = floatval($value['data']->get_price());

        $new_price = $price - $price * $discount;

        //echo "Desconto: " . $discount . ", Preço: " . $value['data']->get_price() . ", Novo Preço: " . $new_price;
    


        // se o desconto tiver ente 0 e 1
        if ( $discount >= 0 && $discount <= 1 ) {
            //aplica o desconto( faz o set do novo preço)
            $value['data']->set_price($new_price);
        }
    }
}

add_action( 'woocommerce_before_calculate_totals', 'prod_new_price', 10, 2 );

/**
* Esta função coloca o preço antigo e o desconto por baixo do nome do produto para mostrar ao cliente
*
* @param object objecto do actual produto do carrinho de compras
*/

function wc_cart_item_name_hyperlink( $link, $product_data ) {
    $current_user = wp_get_current_user();

    if ( $current_user->id != 0){
        //variavel global da base de dados do wordpress
        global $wpdb;
        //Usuario do momento
        $current_user = wp_get_current_user();
        //nome da tabela com o prefixo da base de dados
        $table_name = $wpdb->prefix . 'pwu_price_discounts';
        //id do produto do carrinho
        $product_id = $product_data['product_id'];
        //id de categoria( vai buscar a tabela dos termos na base de dados por 'product_cat' pelo produto id )
        $category_id = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) )[0];
        //faz um select na base de dados de descontos e retira o desconto para fazer display
        $discount = $wpdb->get_results( "SELECT * FROM $table_name WHERE customer_id = $current_user->id AND category_id=$category_id " )[0]->discount;

        echo  '<dl class="">
                    <dt class="">'. wc_get_product( $product_data['product_id'] )->name . '</dt> 
                    <dt class="">PVP:  '. wc_get_product( $product_data['product_id'] )->price . '€ </dt>        
                    <dt class="">Desconto:  ' . $discount * 100 . '% </dt>      
                </dl>';
    }
}

add_filter( 'woocommerce_cart_item_name', 'wc_cart_item_name_hyperlink', 10, 2 );