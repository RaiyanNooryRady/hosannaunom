<?php

function child_enqueue_files() {
    // Parent and child theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style','elementor-frontend'));

    wp_enqueue_script('script', get_stylesheet_directory_uri() . '/assets/js/scripts.js', array('jquery'), '', true);
}

add_action('wp_enqueue_scripts', 'child_enqueue_files', 20);

// require files
//require_once get_stylesheet_directory() . '/inc/addons.php';
//require_once get_stylesheet_directory() . '/inc/booking.php';

function enqueue_flatpickr_script() {
    wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), null, true);
    wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');   
}
add_action('wp_enqueue_scripts', 'enqueue_flatpickr_script');





// Add Custom Fields to Product Page (based on Product ID or Category)
function add_custom_fields_to_product_page() {
    global $product;
    
    if ( $product->is_type( 'simple' ) ) {
        
        // Check the product ID or category to show specific form
        if ( $product->get_id() === 123 ) { // Meal Prep Service Product ID
            // Meal Prep Intake Form
            echo '<h3>Meal Prep Intake Form</h3>';
            echo '<label><input type="checkbox" name="dietary_preferences[]" value="Vegetarian"> Vegetarian</label><br>';
            echo '<label><input type="checkbox" name="dietary_preferences[]" value="Vegan"> Vegan</label><br>';
            echo '<label><input type="checkbox" name="dietary_preferences[]" value="Keto"> Keto</label><br>';
            echo '<label><input type="checkbox" name="dietary_preferences[]" value="Gluten-Free"> Gluten-Free</label><br>';
            echo '<label><input type="checkbox" name="dietary_preferences[]" value="No Preferences"> No Preferences</label><br>';
            echo '<textarea name="allergies" rows="4" placeholder="List any allergies..."></textarea><br>';
            echo '<input type="number" name="meal_quantity" min="1" value="1" step="1"> meals per week<br>';
            echo '<label><input type="checkbox" name="addons[]" value="Premium Ingredients"> Premium Ingredients (+$5)</label><br>';
            echo '<label><input type="checkbox" name="addons[]" value="Extra Portions"> Extra Portions (+$10)</label><br>';
            echo '<textarea name="delivery_instructions" rows="4" placeholder="Enter delivery instructions (address, time, etc.)"></textarea><br>';
        } 
        elseif ( $product->get_id() === 456 ) {
            echo '<h3>Cleaning Intake Form</h3>';
            echo '<label><input type="checkbox" name="cleaning_preferences[]" value="Deep Cleaning"> Deep Cleaning</label><br>';
            echo '<label><input type="checkbox" name="cleaning_preferences[]" value="Regular Cleaning"> Regular Cleaning</label><br>';
            echo '<label><input type="checkbox" name="cleaning_preferences[]" value="Eco-Friendly Products"> Eco-Friendly Products</label><br>';
            echo '<textarea name="cleaning_instructions" rows="4" placeholder="Special Cleaning Instructions..."></textarea><br>';
        } 
        elseif ( $product->get_id() === 789 ) { 
            echo '<h3>Laundry Intake Form</h3>';
            echo '<label><input type="checkbox" name="laundry_preferences[]" value="Delicate Fabrics"> Delicate Fabrics</label><br>';
            echo '<label><input type="checkbox" name="laundry_preferences[]" value="Extra Detergent"> Extra Detergent</label><br>';
            echo '<textarea name="laundry_instructions" rows="4" placeholder="Laundry Special Instructions..."></textarea><br>';
        }
    }
}
add_action( 'woocommerce_before_single_product', 'add_custom_fields_to_product_page' );

// Save custom field data to the cart (based on product/service)
function save_custom_fields_to_cart( $cart_item_data, $product_id ) {
    
    // Meal Prep
    if ( isset( $_POST['dietary_preferences'] ) && $product_id === 123 ) {
        $cart_item_data['dietary_preferences'] = $_POST['dietary_preferences'];
    }
    if ( isset( $_POST['allergies'] ) && $product_id === 123 ) {
        $cart_item_data['allergies'] = sanitize_textarea_field( $_POST['allergies'] );
    }
    if ( isset( $_POST['meal_quantity'] ) && $product_id === 123 ) {
        $cart_item_data['meal_quantity'] = intval( $_POST['meal_quantity'] );
    }
    if ( isset( $_POST['addons'] ) && $product_id === 123 ) {
        $cart_item_data['addons'] = $_POST['addons'];
    }
    if ( isset( $_POST['delivery_instructions'] ) && $product_id === 123 ) {
        $cart_item_data['delivery_instructions'] = sanitize_textarea_field( $_POST['delivery_instructions'] );
    }

    // Cleaning
    if ( isset( $_POST['cleaning_preferences'] ) && $product_id === 456 ) {
        $cart_item_data['cleaning_preferences'] = $_POST['cleaning_preferences'];
    }
    if ( isset( $_POST['cleaning_instructions'] ) && $product_id === 456 ) {
        $cart_item_data['cleaning_instructions'] = sanitize_textarea_field( $_POST['cleaning_instructions'] );
    }

    // Laundry
    if ( isset( $_POST['laundry_preferences'] ) && $product_id === 789 ) {
        $cart_item_data['laundry_preferences'] = $_POST['laundry_preferences'];
    }
    if ( isset( $_POST['laundry_instructions'] ) && $product_id === 789 ) {
        $cart_item_data['laundry_instructions'] = sanitize_textarea_field( $_POST['laundry_instructions'] );
    }

    return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'save_custom_fields_to_cart', 10, 2 );

// Display custom fields in the cart
function display_custom_fields_in_cart( $item_data, $cart_item ) {
    
    // Display Meal Prep fields
    if ( isset( $cart_item['dietary_preferences'] ) ) {
        $item_data[] = array(
            'name'  => 'Dietary Preferences',
            'value' => implode( ', ', $cart_item['dietary_preferences'] ),
        );
    }
    if ( isset( $cart_item['allergies'] ) ) {
        $item_data[] = array(
            'name'  => 'Allergies',
            'value' => $cart_item['allergies'],
        );
    }
    if ( isset( $cart_item['meal_quantity'] ) ) {
        $item_data[] = array(
            'name'  => 'Meal Quantity',
            'value' => $cart_item['meal_quantity'] . ' meals per week',
        );
    }
    if ( isset( $cart_item['addons'] ) ) {
        $item_data[] = array(
            'name'  => 'Add-ons',
            'value' => implode( ', ', $cart_item['addons'] ),
        );
    }
    if ( isset( $cart_item['delivery_instructions'] ) ) {
        $item_data[] = array(
            'name'  => 'Delivery Instructions',
            'value' => $cart_item['delivery_instructions'],
        );
    }

    // Display Cleaning fields
    if ( isset( $cart_item['cleaning_preferences'] ) ) {
        $item_data[] = array(
            'name'  => 'Cleaning Preferences',
            'value' => implode( ', ', $cart_item['cleaning_preferences'] ),
        );
    }
    if ( isset( $cart_item['cleaning_instructions'] ) ) {
        $item_data[] = array(
            'name'  => 'Cleaning Instructions',
            'value' => $cart_item['cleaning_instructions'],
        );
    }

    // Display Laundry fields
    if ( isset( $cart_item['laundry_preferences'] ) ) {
        $item_data[] = array(
            'name'  => 'Laundry Preferences',
            'value' => implode( ', ', $cart_item['laundry_preferences'] ),
        );
    }
    if ( isset( $cart_item['laundry_instructions'] ) ) {
        $item_data[] = array(
            'name'  => 'Laundry Instructions',
            'value' => $cart_item['laundry_instructions'],
        );
    }

    return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'display_custom_fields_in_cart', 10, 2 );
