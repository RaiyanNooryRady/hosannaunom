<?php
// Display custom intake form for Cleaning category
add_action('woocommerce_before_add_to_cart_button', 'custom_cleaning_intake_form');
function custom_cleaning_intake_form() {
    global $product;
    
    if (has_term('cleaning', 'product_cat', $product->get_id())) { ?>
        <div id="cleaning-intake-form">
            <h4>Cleaning Intake Form</h4>

            <label for="number_of_bedrooms">Number of Bedrooms</label>
            <input type="number" name="number_of_bedrooms" id="number_of_bedrooms" min="1" value="1"><br><br>

            <label for="number_of_bathrooms">Number of Bathrooms</label>
            <input type="number" name="number_of_bathrooms" id="number_of_bathrooms" min="1" value="1"><br><br>

            <fieldset>
                <legend>Areas of Focus:</legend>
                <label><input type="checkbox" name="areas_of_focus[]" value="Kitchen Deep Clean"> Kitchen Deep Clean ($2)</label><br>
                <label><input type="checkbox" name="areas_of_focus[]" value="Windows"> Windows ($2)</label><br>
                <label><input type="checkbox" name="areas_of_focus[]" value="Living Room"> Living Room ($2)</label><br>
                <label><input type="checkbox" name="areas_of_focus[]" value="Floors"> Floors ($2)</label><br>
            </fieldset><br>

            <label for="pet_considerations">Pet Considerations (Yes/No):</label>
            <select name="pet_considerations" id="pet_considerations">
                <option value="no">No</option>
                <option value="yes">Yes</option>
            </select><br><br>

            <label for="pet_details">Pet Details (if Yes):</label>
            <input type="text" name="pet_details" id="pet_details" placeholder="Enter pet details"><br><br>

            <label for="preferred_cleaning_time">Preferred Cleaning Time</label>
            <select name="preferred_cleaning_time" id="preferred_cleaning_time">
                <option value="standard" data-price="0">Standard (No Extra Cost)</option>
                <option value="morning" data-price="10">Morning (+$10)</option>
                <option value="evening" data-price="15">Evening (+$15)</option>
            </select><br><br>

            <form class="cart" method="post" enctype="multipart/form-data">
                <button type="submit" class="single_add_to_cart_button button alt" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>">
                    <?php echo esc_html($product->single_add_to_cart_text()); ?>
                </button>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            function updatePrice() {
                var base_price = <?php echo $product->get_price(); ?>;
                var bedrooms = parseInt($('#number_of_bedrooms').val()) || 1;
                var bathrooms = parseInt($('#number_of_bathrooms').val()) || 1;
                var areas_of_focus_price = 0;

                $('input[name="areas_of_focus[]"]:checked').each(function() {
                    areas_of_focus_price += 2; 
                });

                var pet_price = $('#pet_considerations').val() === 'yes' ? 5 : 0;
                var cleaning_time_price = parseFloat($('#preferred_cleaning_time option:selected').data('price')) || 0;
                
                var new_price = base_price + (bedrooms * 1) + (bathrooms * 1) + areas_of_focus_price + pet_price + cleaning_time_price;

                $('.woocommerce-Price-amount').text('$' + new_price.toFixed(2));
            }

            $('#number_of_bedrooms, #number_of_bathrooms, input[name="areas_of_focus[]"], #pet_considerations, #preferred_cleaning_time').on('change', updatePrice);
        });
        </script>

        <style>
            #cleaning-intake-form {
                padding: 12px;
                border: 1px solid #eee;
                border-radius: 5px;
                margin: 16px 0;
                box-shadow: 0 0 5px #eee;
                overflow: hidden;
            }
            fieldset {
                border: none;
                padding: 0;
                margin: 0;
            }
            legend {
                font-weight: bold;
            }
            label {
                cursor: pointer;
            }
            .quantity {
                display: none;
            }
            .quantity + button.single_add_to_cart_button {
                display: none;
            }
        </style>
    <?php }
}

// Save form data to cart
add_filter('woocommerce_add_cart_item_data', 'save_cleaning_intake_form_data', 10, 2);
function save_cleaning_intake_form_data($cart_item_data, $product_id) {
    if (isset($_POST['number_of_bedrooms'])) {
        $cart_item_data['number_of_bedrooms'] = intval($_POST['number_of_bedrooms']);
    }
    if (isset($_POST['number_of_bathrooms'])) {
        $cart_item_data['number_of_bathrooms'] = intval($_POST['number_of_bathrooms']);
    }
    if (!empty($_POST['areas_of_focus'])) {
        $cart_item_data['areas_of_focus'] = $_POST['areas_of_focus'];
    }
    if (!empty($_POST['pet_considerations'])) {
        $cart_item_data['pet_considerations'] = $_POST['pet_considerations'];
    }
    if (!empty($_POST['pet_details'])) {
        $cart_item_data['pet_details'] = sanitize_text_field($_POST['pet_details']);
    }
    if (!empty($_POST['preferred_cleaning_time'])) {
        $cart_item_data['preferred_cleaning_time'] = sanitize_text_field($_POST['preferred_cleaning_time']);
        $cart_item_data['cleaning_time_price'] = ($_POST['preferred_cleaning_time'] == 'morning') ? 10 : (($_POST['preferred_cleaning_time'] == 'evening') ? 15 : 0);
    }
    return $cart_item_data;
}

// Display form data in cart and checkout
add_filter('woocommerce_get_item_data', 'display_cleaning_intake_form_data', 10, 2);
function display_cleaning_intake_form_data($item_data, $cart_item) {
    if (!empty($cart_item['number_of_bedrooms'])) {
        $item_data[] = ['name' => 'Number of Bedrooms', 'value' => $cart_item['number_of_bedrooms']];
    }
    if (!empty($cart_item['number_of_bathrooms'])) {
        $item_data[] = ['name' => 'Number of Bathrooms', 'value' => $cart_item['number_of_bathrooms']];
    }
    if (!empty($cart_item['areas_of_focus'])) {
        $item_data[] = ['name' => 'Areas of Focus', 'value' => implode(', ', $cart_item['areas_of_focus'])];
    }
    if (!empty($cart_item['pet_considerations'])) {
        $item_data[] = ['name' => 'Pet Considerations', 'value' => ucfirst($cart_item['pet_considerations'])];
    }
    if (!empty($cart_item['pet_details'])) {
        $item_data[] = ['name' => 'Pet Details', 'value' => $cart_item['pet_details']];
    }
    if (!empty($cart_item['preferred_cleaning_time'])) {
        $item_data[] = ['name' => 'Preferred Cleaning Time', 'value' => ucfirst($cart_item['preferred_cleaning_time']) . ' (+$' . $cart_item['cleaning_time_price'] . ')'];
    }
    return $item_data;
}

// Modify cart item price dynamically
add_action('woocommerce_before_calculate_totals', 'update_cleaning_cart_price');
function update_cleaning_cart_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item) {
        $base_price = $cart_item['data']->get_price();
        $bedrooms = $cart_item['number_of_bedrooms'] ?? 1;
        $bathrooms = $cart_item['number_of_bathrooms'] ?? 1;
        $areas_of_focus_price = !empty($cart_item['areas_of_focus']) ? count($cart_item['areas_of_focus']) * 10 : 0;
        $pet_price = $cart_item['pet_considerations'] === 'yes' ? 5 : 0;
        $cleaning_time_price = $cart_item['cleaning_time_price'] ?? 0;

        $new_price = $base_price + ($bedrooms * 20) + ($bathrooms * 15) + $areas_of_focus_price + $pet_price + $cleaning_time_price;
        $cart_item['data']->set_price($new_price);
    }
}
