<?php 

add_action('woocommerce_before_add_to_cart_button', 'custom_laundry_service_datepicker');

function custom_laundry_service_datepicker() {
    global $product;
    $price_per_day = get_post_meta($product->get_id(), '_price_per_day', true);
?>
    <div style="margin-top:10px; margin-bottom:10px;">
        <label for="service_date">Select Duration:</label>
        <input type="text" id="service_date" name="service_date" class="datepicker" required>
    </div>
    <p id="date_duration" style="margin-top: 10px; font-weight: bold; color: #333;"></p>
    <p>Price Per Day: <strong>$<span id="price_per_day_display"><?php echo $price_per_day; ?></span></strong></p>
    <p>Total Price: <strong><span id="calculated_price">$0.00</span></strong></p>

    <input type="hidden" id="price_per_day" value="<?php echo $price_per_day; ?>">
    <input type="hidden" id="total_price" name="total_price" value="0">
<?php
}

add_filter('woocommerce_add_cart_item_data', 'save_custom_price_in_cart', 10, 2);
function save_custom_price_in_cart($cart_item_data, $product_id) {
    if (!empty($_POST['service_date']) && !empty($_POST['total_price'])) {
        $cart_item_data['service_date'] = sanitize_text_field($_POST['service_date']);
        $cart_item_data['custom_price'] = floatval($_POST['total_price']); 
    }
    return $cart_item_data;
}

// Update cart total dynamically
add_action('woocommerce_before_calculate_totals', 'set_custom_cart_price');
function set_custom_cart_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;
    
    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['custom_price']) && !empty($cart_item['custom_price'])) {
            $cart_item['data']->set_price($cart_item['custom_price']);
        }
    }
}

add_filter('woocommerce_get_item_data', 'display_custom_price_in_cart', 10, 2);
function display_custom_price_in_cart($item_data, $cart_item) {
    if (!empty($cart_item['service_date'])) {
        $item_data[] = array(
            'name' => 'Service Duration',
            'value' => esc_html($cart_item['service_date'])
        );
    }
    if (!empty($cart_item['custom_price'])) {
        $item_data[] = array(
            'name' => 'Total Price',
            'value' => '$' . number_format($cart_item['custom_price'], 2)
        );
    }
    return $item_data;
}


//admin
add_action('woocommerce_product_options_pricing', 'add_price_per_day_field');
function add_price_per_day_field() {
    woocommerce_wp_text_input(array(
        'id' => '_price_per_day',
        'label' => __('Price Per Day', 'woocommerce'),
        'desc_tip' => 'true',
        'description' => __('Enter the price per day for this service.', 'woocommerce'),
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '0.01',
            'min' => '0'
        )
    ));
}

// Save the "Price Per Day" value when the product is updated
add_action('woocommerce_admin_process_product_object', 'save_price_per_day_field');
function save_price_per_day_field($product) {
    if (isset($_POST['_price_per_day'])) {
        $product->update_meta_data('_price_per_day', sanitize_text_field($_POST['_price_per_day']));
    }
}
