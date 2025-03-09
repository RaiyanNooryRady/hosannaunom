<?php 

function create_addons() {
    $labels = array(
        'name'               => 'Addons',
        'singular_name'      => 'Addon',
        'menu_name'          => 'Addons',
        'add_new'            => 'Add New Addon',
        'add_new_item'       => 'Add New Addon',
        'edit_item'          => 'Edit Addon',
        'new_item'           => 'New Addon',
        'view_item'          => 'View Addon',
        'search_items'       => 'Search Addons',
        'not_found'          => 'No addons found',
        'not_found_in_trash' => 'No addons found in trash'
    );

    $args = array(
        'label'               => 'Addons',
        'labels'              => $labels,
        'public'              => true,
        'menu_icon'           => 'dashicons-plugins-checked',
        'supports'            => array('title', 'editor'),
        'has_archive'         => false,
        'show_in_menu'        => true,
    );

    register_post_type('addons', $args);
}
add_action('init', 'create_addons');

//price for addons
function addons_price_meta_box() {
    add_meta_box('addons_price_meta', 'Addon Price', 'addons_price_meta_callback', 'addons', 'side', 'high');
}
add_action('add_meta_boxes', 'addons_price_meta_box');

function addons_price_meta_callback($post) {
    $price = get_post_meta($post->ID, 'addon_price', true);
    ?>
    <label for="addon_price">Price ($)</label>
    <input type="number" name="addon_price" id="addon_price" value="<?php echo esc_attr($price); ?>" step="0.01" min="0">
    <?php
}

function save_addons_price_meta($post_id) {
    if (isset($_POST['addon_price'])) {
        update_post_meta($post_id, 'addon_price', sanitize_text_field($_POST['addon_price']));
    }
}
add_action('save_post', 'save_addons_price_meta');

// Add new columns to the Addons admin list
function addons_custom_columns($columns) {
    $new_columns = array(
        'cb'           => $columns['cb'], 
        'title'        => $columns['title'],
        'addon_price'  => 'Price', 
        'addon_author' => 'Author', 
        'date'         => $columns['date'] 
    );
    return $new_columns;
}
add_filter('manage_addons_posts_columns', 'addons_custom_columns');

if (!function_exists('addons_custom_column_content')) {
    function addons_custom_column_content($column, $post_id) {
        switch ($column) {
            case 'addon_price':
                $price = get_post_meta($post_id, 'addon_price', true);
                echo ($price !== '') ? '$' . esc_html($price) : '<em>Not Set</em>';
                break;
            case 'addon_author':
                $author_id = get_post_field('post_author', $post_id);
                $author = get_userdata($author_id);
                echo ($author) ? esc_html($author->display_name) : '<em>Unknown</em>';
                break;
        }
    }
}
add_action('manage_addons_posts_custom_column', 'addons_custom_column_content', 10, 2);

//product edit page
function add_addons_meta_box() {
    add_meta_box(
        'product_addons_meta',
        'Select Addons',
        'display_product_addons_meta_box',
        'product',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_addons_meta_box');

function display_product_addons_meta_box($post) {
    $selected_addons = get_post_meta($post->ID, '_selected_addons', true);
    $addons = get_posts(array(
        'post_type' => 'addons',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    if ($addons) {
        echo '<p>Select Addons for this Product:</p>';
        foreach ($addons as $addon) {
            $checked = (is_array($selected_addons) && in_array($addon->ID, $selected_addons)) ? 'checked' : '';
            $price = get_post_meta($addon->ID, 'addon_price', true);
            $description = get_post_field('post_content', $addon->ID); 
            
            echo '<div style="margin-bottom:10px; padding:5px; border:1px solid #ddd;">';
            echo '<label><input type="checkbox" name="product_addons[]" value="' . $addon->ID . '" ' . $checked . '> ';
            echo '<strong>' . $addon->post_title . ' (+$' . $price . ')</strong></label>';
            echo '<p style="font-size:12px; color:#555; margin:5px 0;">' . esc_html($description) . '</p>';
            echo '</div>';
        }
    } else {
        echo 'No addons available.';
    }
}


function save_product_addons_meta($post_id) {
    if (isset($_POST['product_addons'])) {
        update_post_meta($post_id, '_selected_addons', $_POST['product_addons']);
    } else {
        delete_post_meta($post_id, '_selected_addons');
    }
}
add_action('save_post', 'save_product_addons_meta');

//frontend
function show_selected_addons_on_product_page() {
    global $post;
    $selected_addons = get_post_meta($post->ID, '_selected_addons', true);

    if (!empty($selected_addons)) {
        echo '<div class="product-addons"><h5>Available Services</h5>';
        
        foreach ($selected_addons as $addon_id) {
            $addon_title = get_the_title($addon_id);
            $addon_price = get_post_meta($addon_id, 'addon_price', true);
            echo '<p><label><input type="checkbox" name="extra_addons[]" value="' . $addon_id . '" data-price="' . $addon_price . '"> ' . $addon_title . ' (+$' . $addon_price . ')</label></p>';
        }
        echo '</div>';
    }

    ?>
    <script>
    // jQuery(document).ready(function($) {
    //     var basePrice = <?php echo get_post_meta(get_the_ID(), '_price', true); ?>;
    //     $('input[name="extra_addons[]"]').change(function() {
    //         var totalPrice = basePrice;
    //         $('input[name="extra_addons[]"]:checked').each(function() {
    //             totalPrice += parseFloat($(this).data('price'));
    //         });
    //         $('.woocommerce-Price-amount').text('$' + totalPrice.toFixed(2));
    //     });
    // });
    </script>
    <?php
}
add_action('woocommerce_before_add_to_cart_button', 'show_selected_addons_on_product_page');



function add_selected_addons_to_cart($cart_item_data, $product_id) {
    if (isset($_POST['extra_addons'])) {
        $extra_price = 0;
        $selected_addons = array();

        foreach ($_POST['extra_addons'] as $addon_id) {
            $price = get_post_meta($addon_id, 'addon_price', true);
            $extra_price += (float) $price;
            $selected_addons[] = get_the_title($addon_id) . ' (+$' . $price . ')';
        }

        $cart_item_data['selected_addons'] = $selected_addons;
        $cart_item_data['extra_price'] = $extra_price;
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_selected_addons_to_cart', 10, 2);

function update_cart_price_with_addons($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item) {
        if (!empty($cart_item['extra_price'])) {
            $cart_item['data']->set_price($cart_item['data']->get_price() + $cart_item['extra_price']);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'update_cart_price_with_addons');

function display_selected_addons_cart($item_name, $cart_item, $cart_item_key) {
    if (!empty($cart_item['selected_addons'])) {
        $item_name .= '<br><strong>Extras:</strong> ' . implode(', ', $cart_item['selected_addons']);
    }
    return $item_name;
}
add_filter('woocommerce_cart_item_name', 'display_selected_addons_cart', 10, 3);

function save_addons_order_meta($item, $cart_item_key, $values, $order) {
    if (!empty($values['selected_addons'])) {
        $item->add_meta_data('Selected Addons', implode(', ', $values['selected_addons']), true);
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'save_addons_order_meta', 10, 4);

function add_addons_to_order_email($fields, $sent_to_admin, $order) {
    $addons_list = array(); 
    
    foreach ($order->get_items() as $item) {
        $addons = $item->get_meta('Selected Addons', true); 
        
        if (!empty($addons)) {
            if (is_array($addons)) {
                $addons_list = array_merge($addons_list, $addons); 
            } else {
                $addons_list[] = $addons;
            }
        }
    }

    if (!empty($addons_list)) {
        $fields['selected_addons'] = array(
            'label' => __('Selected Addons', 'woocommerce'),
            'value' => implode(', ', $addons_list),
        );
    }

    return $fields;
}
add_filter('woocommerce_email_order_meta_fields', 'add_addons_to_order_email', 10, 3);


