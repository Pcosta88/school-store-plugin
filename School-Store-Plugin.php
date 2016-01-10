<?php
/*
Plugin Name: School Store Pluginin
Plugin URI: patcosta.com
Description: A school store plugin for student run school-stores.
Version: 1.0
Author: Pat Costa
Author URI: patcosta.com
Text Domain: school-store
*/
 
// on plugin activation, setup our default options
register_activation_hook (__FILE__ , 'school_store_install' );
function school_store_install() {
    // set-up default options values
    $ss_options_arr = array(
        'show_inventory' => '', // unchecked by default
        'currency_sign' => '$'
    );
    // save default options
    update_option( 'school_options', $ss_options_arr );
}
 
// Action hook to initalize plugin
add_action('init', 'school_store_init');
function school_store_init()  {
 
    //register products in custom post type
    $labels = array(
        'name' => __( 'Products', 'school-plugin' ),
        'singular_name' => __( 'Product', 'school-plugin' ),
        'add_new' => __( 'Add New', 'school-plugin' ),
        'add_new_item' => __( 'Add New Product', 'school-plugin' ),
        'edit_item' => __( 'Edit Product', 'school-plugin' ),
        'new_item' => __( 'New Product', 'school-plugin' ),
        'all_items' => __( 'All Products', 'school-plugin' ),
        'view_item' => __( 'View Product', 'school-plugin' ),
        'search_items' => __( 'Search Products', 'school-plugin' ),
        'not_found' =>  __( 'No products found', 'school-plugin' ),
        'not_found_in_trash' => __( 'No products found in Trash', 'school-plugin' ),
        'menu_name' => __( 'Products', 'school-plugin' )
      );
   
      $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' )
      );    
 
    register_post_type('school-products', $args);
 
}
 
//action hook to add the post product menu item in the admin dashboard
add_action('admin_menu','school_store_menu');
 
//create submenu item
function school_store_menu() {
    add_options_page (
        __('School Store Settings Page', 'school-plugin'),
        __('School Store Settings', 'school-plugin'),
        'manage_options',
        'school-store-settings',
        'school_store_settings_page'
    );
}
 
//build plugin settings page
 
function school_store_settings_page()  {
 
    //load the plugin array option
    $ss_options_arr = get_option( 'school_options' );
   
    //set the option array values to variables
    $ss_inventory = ( ! empty( $ss_options_arr['show_inventory'] ) ) ? $ss_options_arr['show_inventory'] : '';
    $ss_currency_sign = $ss_options_arr[ 'currency_sign' ];
    ?>
    <div class="wrap">
        <h2><?php _e ('School Store Options', 'school-plugin') ?></h2>
        <form action="options.php" method ="post">
           
            <?php settings_fields( 'school-settings-group' ); ?>
           
            <table class="form-table">
           
                <tr valign="top">
                    <th scope="row"><?php _e ('Show Product Inventory', 'school-plugin') ?></th>
                    <td><input type="checkbox" name="school_options[show_inventory]" <?php echo checked( $ss_inventory,'on' ); ?> /></td>
                 </tr>
                 
                 <tr valign="top">
                     <th scope="row"><?php _e ('Currency Sign', 'school-plugin') ?></th>
                     <td><input type="text" name="school_options[currency_sign]" value="<?php echo esc_attr( $ss_currency_sign ); ?>"size="1" maxlength="1"  /></td>
                 </tr>
             </table>
 
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e ('Save Changes', 'school-plugin') ?>" />
            </p>
           
        </form>
    </div>
<?php
}
 
//Action hook to register the plugin with option settings
add_action ('admin_init', 'school_store_register_settings');
 
function school_store_register_settings() {
    //register array of settngs
    register_setting ('school-settings-group', 'school_options', 'school_sanitize_settings' );
}
 
function school_sanitize_settings( $options ) {
    $options ['show_inventory'] = ( ! empty ( $options['show_inventory'] ) ) ? sanitize_text_field ($options['show_inventory']) : '';
    $options['currency_sign'] = ( ! empty ( $options['currency_sign'] ) ) ? sanitize_text_field ($options['currency_sign']) : '';
    return $options;
}
 
//Action hook to register the Products Meta Box
add_action ('add_meta_boxes', 'school_store_register_meta_box');
 
function school_store_register_meta_box () {
    //create custom meta box
    add_meta_box (
        'school-product-meta',
        __('Product Information', 'school-plugin'),
        'school_meta_box',
        'school-products',
        'side', 'default'
    );
}
 
//build product meta box
function school_meta_box ( $post ) {
   
   //get our custom meta box values
    $ss_sku = get_post_meta ($post->ID, '_school_product_sku', true);
    $ss_price = get_post_meta ($post->ID, '_school_product_price', true);
    $ss_weight = get_post_meta ($post->ID, '_school_product_weight', true);
    $ss_color = get_post_meta ($post->ID, '_school_product_color', true);
    $ss_inventory = get_post_meta($post->ID, '_school_product_inventory', true);
 
//nonce field for security
    wp_nonce_field ('meta-box-save', 'school-plugin');
 
    //display meta box form
    echo '<table>';
    echo '<tr>';
   
    echo '<td>' .__('Sku', 'school-plugin') .': </td>
    <td> <input type="text" name="school_product_sku" value=" '.esc_attr ( $ss_sku ).' " size="10"> </td>';
    echo '<tr> </tr>';
 
    echo '<td>' .__('Price', 'school-plugin') .': </td>
    <td> <input type="text" name="school_product_price" value=" '.esc_attr ( $ss_price ).' " size="5"> </td>';
    echo '<tr> </tr>';
 
    echo '<td>' .__('Weight', 'school-plugin') .': </td>
    <td> <input type="text" name="school_product_weight" value=" '.esc_attr ( $ss_weight ).' " size="5"> </td>';
    echo '<tr> </tr>';
 
    echo '<td>' .__('Color', 'school-plugin') .': </td>
    <td> <input type="text" name="school_product_color" value=" '.esc_attr ( $ss_color ).' " size="5"> </td>';
    echo '<tr> </tr>';
 
//For more inventory options follow the examples below and tack one at the end
    echo '<td> Inventory: </td>
    <td> <select name="school_product_inventory"
           id="school_product_inventory">
           <option value="In Stock" '
           .selected ($ss_inventory, 'In Stock', false ) . '>'
           .__('In Stock', 'school-plugin')
           . '</option>
 
           <option value="Backordered" '
           .selected ($ss_inventory, 'Backordered', false ) . '>'
           .__('Backordered', 'school-plugin')
           . '</option>
 
          <option value="Out of Stock" '
           .selected ($ss_inventory, 'Out of Stock', false ) . '>'
           .__('Out of Stock', 'school-plugin')
           . '</option>
 
           <option value="Discontinued" '
           .selected ($ss_inventory, 'Discontinued', false ) . '>'
           .__('Discontinued', 'school-plugin')
           . '</option>
    </select> </td>';
    echo '</tr>';
 
    //display the meta box shortcode legend section
    echo '<tr> <td colspan="2"> <hr> </td> </tr>';
    echo '<tr> <td colspan="2"> <strong>' . __('Shortcode Legend', 'school-plugin').'</strong> </td> </tr>';
    echo '<tr> <td>'.__('Sku', 'school-plugin').': </td> <td> [hs show=sku] </td> </tr>';
    echo '<tr> <td>'.__('Price', 'school-plugin').': </td> <td> [hs show=price] </td> </tr>';
    echo '<tr> <td>'.__('weight', 'school-plugin').': </td> <td> [hs show=weight] </td> </tr>';
    echo '<tr> <td>'.__('Color', 'school-plugin').': </td> <td> [hs show=color] </td> </tr>';
    echo '<tr> <td>'.__('Inventory', 'school-plugin').': </td> <td> [hs show=inventory] </td> </tr>';        
    echo '</table>';
}
//Action hook to save the meta box data when the post is saved
add_action ( 'save_post', 'school_store_save_meta_box' );
 
//save meta box data
function school_store_save_meta_box ($post_id) {
 
//verify the post type is for school products and metadata has been posted
 
    if (get_post_type ($post_id) == 'school_products'
        && isset ( $_POST ['school_product_sku'] ) ) {
        //if autosave skip saving data
 
        if (defined ('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
 
        //check nonce for security
 
        check_admin_referer ('meta-box-save', 'school-plugin');
 
        //save the metabox data as post meta data
        update_post_meta ($post_id, '_school_product_sku', sanitize_text_field ( $_POST['school_product_sku' ] ) );
        update_post_meta ($post_id, '_school_product_price', sanitize_text_field ( $_POST['school_product_price' ] ) );
        update_post_meta ($post_id, '_school_product_weight', sanitize_text_field ( $_POST['school_product_weight' ] ) );
        update_post_meta ($post_id, '_school_product_color', sanitize_text_field ( $_POST['school_product_color' ] ) );
        update_post_meta ($post_id, '_school_product_inventory', sanitize_text_field ( $_POST['school_product_inventory' ] ) );
    }
 
}
//Action hook to create products shortcode
add_shortcode ('ss', 'school_store_shortcode');
 
//create shortcode
function school_store_shortcode ($atts, $content=null) {
    global $post;
 
    extract (shortcode_atts ( array(
      "show"=> '' ), $atts ) );
 
//Load Options Arrary
    $ss_options_arr = get_option ('school_options');
 
    if ($show=='sku') {
        $ss_show = get_post_meta ($post->ID, '_school_product_sku', true);
 
    }elseif ($show =='price') {
 
        $ss_show = $ss_options_arr ['currency_sign']. get_post_meta ( $post->ID, '_school_product_price', true);
 
    }elseif ($show =='weight') {
 
        $ss_show = get_post_meta ( $post->ID, '_school_product_weight', true);
 
    }elseif ($show =='color') {
 
        $ss_show = get_post_meta ( $post->ID, '_school_product_color', true);
 
    }elseif ($show =='inventory') {
 
        $ss_show = get_post_meta ( $post->ID, '_school_product_inventory', true);
   }
    // return the shortcode value
    return $ss_show;
}
 
// Action hook to create plugin widget
add_action ('widgets_init', 'school_store_register_widgets');
// register widget
function school_store_register_widgets() {
    register_widget ('ss_widget');
}
 
// ss_widget class
class ss_widget extends WP_Widget {
 
    //process our new widget
    function __construct() {
        parent::__construct(
            // Base ID of your widget
            'ss_widget',
 
            // Widget name will appear in UI
            __('Products Widget', 'school-plugin'),
 
            // Widget description
            array(
                'description' => __( 'Display School Store Products', 'school-plugin' ),
                'classname'=> 'ss-widget-class',
            )
        );
    }
 
    //build our widget settings form
    function form ($instance) {
        $defaults = array(
            'title' => __( 'Products', 'school-plugin' ),
            'number_products' => 3
        );
        $instance = wp_parse_args ((array) $instance, $defaults);
        $title= $instance['title'];
        $number_products = $instance['number_products'];
        ?>
        <p>
            <?php echo __( 'Title', 'school-plugin' ) ?>:
            <input class="widefat" name="<?php echo $this->get_field_name ('title'); ?>" type="text" value="<?php echo esc_attr ( $title); ?>" />
        </p>
        <p>
            <?php echo __('Number of Products', 'school-plugin') ?> :
            <input name="<?php echo $this->get_field_name ('number_products'); ?>" type="number" min="1" max="15" value="<?php echo esc_attr ($number_products); ?>" />
        </p>
        <?php
    }
 
    //save our widget settings
    function update ($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance ['title'] = sanitize_text_field ($new_instance['title']);
        $instance ['number_products'] = absint ($new_instance ['number_products']);
        return $instance;
    }
 
    //display widget
    function widget ( $args, $instance ) {
        global $post;
        extract ($args);
        echo $before_widget;
        $title = apply_filters ('widget_title', $instance['title']);
        $number_products = $instance ['number_products'];
 
        if ( ! empty($title ) ) {
            echo $before_title . esc_html ($title) . $after_title;
        }
 
        //custom query to retrive products
        $args = array(
            'post-type' => 'school_products',
            'post_per_page' => absint ($number_products)
        );
       
        $dispProducts = new WP_Query();
        $dispProducts->query($args);
       
        while ($dispProducts->have_posts() ) :
            $dispProducts ->the_post();
 
            //load options array
            $ss_options_arr = get_option( 'school-plugin');
 
            //load custom meta values
            $ss_price = get_post_meta ( $post->ID, '_school_product_price', true);
            $ss_inventory = get_post_meta ( $post->ID, '_school_product_inventory', true);
 
            echo '<p>' .__('Price', 'school-plugin'). ':' .$ss_options_arr ['currency_sign']. $ss_price . '</p>';
 
            //check if show inventory option is enabled
            if ($ss_options_arr ['show_inventory'] ) {
 
                //display the inventory meta data for this product
                echo '<p>' .__('Stock', 'school-plugin'). ':' . $ss_inventory . '</p>';
            }
            echo '<hr>';
 
        endwhile;
 
        wp_reset_postdata();
 
        echo $after_widget;
    }
 
}