<?php
// Add Date Picker to WooCommerce Product Page
function add_booking_calendar_field()
{
  global $product;

  if ($product->is_type('simple') && has_term('quick-view', 'product_cat', $product->get_id())) {
    // Apply only to simple && quick view products
    echo '<div class="woocommerce-booking-calendar"><br>
				<label for="booking_type">Select a Booking Type</label><br><br>
				<select id="booking_type" name="booking_type"><br>
					<option value="one_time">One Time</option>
					<option value="weekly">Weekly</option>
					<option value="monthly">Monthly</option>
				</select><br><br>
                <label for="booking_date">Select a Booking Date:</label><br><br>
                <input type="date" id="booking_date" name="booking_date" required><br>
              <br>';
  }
}
add_action('woocommerce_before_add_to_cart_button', 'add_booking_calendar_field');

function add_booking_calendar_field2()
{
  global $product;
  echo '<br><br><br>';
}
add_action('woocommerce_after_add_to_cart_button', 'add_booking_calendar_field2');

function save_booking_date_to_cart($cart_item_data, $product_id)
{
  if (!empty($_POST['booking_date']) && !empty($_POST['booking_type'])) {
    $cart_item_data['booking_date'] = sanitize_text_field($_POST['booking_date']);
    $cart_item_data['booking_type'] = sanitize_text_field($_POST['booking_type']);
  }
  return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'save_booking_date_to_cart', 10, 2);

// Display Booking Date in Cart
function display_booking_date_in_cart($item_data, $cart_item)
{
  if (!empty($cart_item['booking_date'] && !empty($cart_item['booking_type']))) {
    $item_data[] = array(
      'name' => 'Booking Date',
      'value' => esc_html($cart_item['booking_date']),
    );
    $item_data[] = array(
      'name' => 'Booking Type',
      'value' => esc_html($cart_item['booking_type'])
    );
  }
  return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_booking_date_in_cart', 10, 2);

// Save Booking Date in Order
function save_booking_date_to_order($item, $cart_item_key, $values, $order)
{
  if (!empty($values['booking_date']) && !empty($values['booking_type'])) {
    $item->add_meta_data('Booking Date', $values['booking_date'], true);
    $item->add_meta_data('Booking Type', $values['booking_type'], true);
  }
}
add_action('woocommerce_checkout_create_order_line_item', 'save_booking_date_to_order', 10, 4);

// Display Booking Date in WooCommerce Admin Order Details
function display_booking_date_in_admin_order($item_id, $item, $order)
{
  $booking_date = $item->get_meta('Booking Date');
  $booking_type = $item->get_meta('Booking Type');
  if (!empty($booking_date) && !empty($booking_type)) {
    echo '<p><strong>Booking Date:</strong> ' . esc_html($booking_date) . '</p>';
    echo '<p><strong>Booking Type:</strong> ' . esc_html($booking_type) . '</p>';
  }
}
add_action('woocommerce_after_order_itemmeta', 'display_booking_date_in_admin_order', 10, 3);
