<?php
// Display custom intake form before the Add to Cart button
add_action('woocommerce_before_add_to_cart_button', 'custom_meal_prep_intake_form');
function custom_meal_prep_intake_form() {
    global $product;
    
    if (has_term('meal-prep', 'product_cat', $product->get_id())) { ?>
        <div id="meal-prep-intake-form">
            <h4>Meal Prep Intake Form</h4>

            <label for="meal_quantity">Meal Quantity (auto price adjuster):</label>
            <input type="number" name="meal_quantity" id="meal_quantity" min="1" value="1"><br><br>

            <fieldset>
                <legend>Dietary Preferences:</legend>
                <label><input type="checkbox" name="dietary_preferences[]" value="Vegetarian"> Vegetarian</label><br>
                <label><input type="checkbox" name="dietary_preferences[]" value="Vegan"> Vegan</label><br>
                <label><input type="checkbox" name="dietary_preferences[]" value="Keto"> Keto</label><br>
                <label><input type="checkbox" name="dietary_preferences[]" value="Gluten-Free"> Gluten-Free</label><br>
                <label><input type="checkbox" name="dietary_preferences[]" value="No Preferences"> No Preferences</label><br>
            </fieldset><br>

            <label for="allergies">Allergies:</label>
            <input type="text" name="allergies" id="allergies" placeholder="List any allergies"><br><br>

            <fieldset>
                <legend>Add-ons (auto price adjuster):</legend>
                <label><input type="checkbox" name="add_ons[]" value="Premium Ingredients" data-price="5"> Premium Ingredients ($5)</label><br>
                <label><input type="checkbox" name="add_ons[]" value="Extra Portions" data-price="5"> Extra Portions ($5)</label><br>
            </fieldset><br>

            <!-- <label for="delivery_address">Delivery Instructions:</label>
            <textarea name="delivery_address" id="delivery_address" placeholder="Enter address"></textarea><br><br> -->

            <label for="delivery_time">Preferred Delivery Time (auto price adjuster):</label>
            <select name="delivery_time" id="delivery_time">
                <option value="standard" data-price="0">Standard (No Extra Cost)</option>
                <option value="morning" data-price="5">Morning (+$5)</option>
                <option value="evening" data-price="8">Evening (+$8)</option>
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
                var quantity = parseInt($('#meal_quantity').val()) || 1;
                var add_on_price = 0;

                $('input[name="add_ons[]"]:checked').each(function() {
                    add_on_price += parseFloat($(this).data('price'));
                });

                var delivery_price = parseFloat($('#delivery_time option:selected').data('price')) || 0;
                var new_price = (base_price * quantity) + add_on_price + delivery_price;

                $('.woocommerce-Price-amount').text('$' + new_price.toFixed(2));
            }

            $('#meal_quantity, input[name="add_ons[]"], #delivery_time').on('change', updatePrice);
        });
        </script>

        <style>
            #meal-prep-intake-form {
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
add_filter('woocommerce_add_cart_item_data', 'save_meal_prep_form_data', 10, 2);
function save_meal_prep_form_data($cart_item_data, $product_id) {
    if (isset($_POST['meal_quantity'])) {
        $cart_item_data['meal_quantity'] = intval($_POST['meal_quantity']);
    }
    if (!empty($_POST['dietary_preferences'])) {
        $cart_item_data['dietary_preferences'] = $_POST['dietary_preferences'];
    }
    if (!empty($_POST['allergies'])) {
        $cart_item_data['allergies'] = sanitize_text_field($_POST['allergies']);
    }
    if (!empty($_POST['add_ons'])) {
        $cart_item_data['add_ons'] = $_POST['add_ons'];
    }
    if (!empty($_POST['delivery_address'])) {
        $cart_item_data['delivery_address'] = sanitize_textarea_field($_POST['delivery_address']);
    }
    if (!empty($_POST['delivery_time'])) {
        $cart_item_data['delivery_time'] = sanitize_text_field($_POST['delivery_time']);
        $cart_item_data['delivery_price'] = ($_POST['delivery_time'] == 'morning') ? 5 : (($_POST['delivery_time'] == 'evening') ? 8 : 0);
    }
    return $cart_item_data;
}

// Display form data in cart and checkout
add_filter('woocommerce_get_item_data', 'display_meal_prep_form_data', 10, 2);
function display_meal_prep_form_data($item_data, $cart_item) {
    if (!empty($cart_item['meal_quantity'])) {
        $item_data[] = ['name' => 'Meal Quantity', 'value' => $cart_item['meal_quantity']];
    }
    if (!empty($cart_item['dietary_preferences'])) {
        $item_data[] = ['name' => 'Dietary Preferences', 'value' => implode(', ', $cart_item['dietary_preferences'])];
    }
    if (!empty($cart_item['allergies'])) {
        $item_data[] = ['name' => 'Allergies', 'value' => $cart_item['allergies']];
    }
    if (!empty($cart_item['add_ons'])) {
        $item_data[] = ['name' => 'Add-ons', 'value' => implode(', ', $cart_item['add_ons'])];
    }
    if (!empty($cart_item['delivery_address'])) {
        $item_data[] = ['name' => 'Delivery Address', 'value' => $cart_item['delivery_address']];
    }
    if (!empty($cart_item['delivery_time'])) {
        $item_data[] = ['name' => 'Preferred Delivery Time', 'value' => ucfirst($cart_item['delivery_time']) . ' (+$' . $cart_item['delivery_price'] . ')'];
    }
    return $item_data;
}

// Modify cart item price dynamically
add_action('woocommerce_before_calculate_totals', 'update_meal_prep_cart_price');
function update_meal_prep_cart_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item) {
        $base_price = $cart_item['data']->get_price();
        $quantity = $cart_item['meal_quantity'] ?? 1;
        $add_ons_price = !empty($cart_item['add_ons']) ? count($cart_item['add_ons']) * 5 : 0;
        $delivery_price = $cart_item['delivery_price'] ?? 0;

        $new_price = ($base_price * $quantity) + $add_ons_price + $delivery_price;
        $cart_item['data']->set_price($new_price);
    }
}


  