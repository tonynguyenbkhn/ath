<?php

if (!defined('ABSPATH')) {
    exit;
}

remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
remove_action('woocommerce_before_checkout_form_cart_notices', 'woocommerce_output_all_notices', 10);

// add_filter('woocommerce_default_address_fields', 'wcs_checkout_update_fields_order');
// add_filter('woocommerce_checkout_fields', 'wcs_checkout_update_placeholder_fields');

// Move shipping section
// add_filter('woocommerce_cart_ready_to_calc_shipping', '__return_false');

add_action('woocommerce_before_checkout_form', 'wcs_checkout_page_open', 5);
// add_action('woocommerce_before_checkout_form', 'wcs_checkout_render_shop_steps', 6);

function wcs_checkout_page_open()
{
  echo '<div class="page-block page-block--checkout woocommerce-checkout-custom" data-block="checkout-custom">';
  echo '<div class="twmp-checkout-steps" aria-hidden="true">';
  echo '<div class="twmp-checkout-steps__item is-active"><span class="twmp-checkout-steps__index">1</span><span class="twmp-checkout-steps__label">' . esc_html__('Booking information', 'twmp-ath') . '</span></div>';
  echo '<div class="twmp-checkout-steps__line"></div>';
  echo '<div class="twmp-checkout-steps__item"><span class="twmp-checkout-steps__index">2</span><span class="twmp-checkout-steps__label">' . esc_html__('Payment', 'twmp-ath') . '</span></div>';
  echo '</div>';
}

add_action('woocommerce_after_checkout_form', 'wcs_checkout_page_close', 100);

function wcs_checkout_page_close()
{
  echo '</div>';
}

function wcs_checkout_update_fields_order($fields)
{
  unset($fields['company']);
  unset($fields['address_2']);
  unset($fields['postcode']);

  return $fields;
}

function wcs_checkout_update_placeholder_fields($fields)
{
  $fields['billing']['billing_first_name']['placeholder'] = esc_html__('First name', 'twmp-ath');
  $fields['billing']['billing_last_name']['placeholder'] = esc_html__('Last name', 'twmp-ath');
  $fields['billing']['billing_phone']['placeholder'] = esc_html__('Phone', 'twmp-ath');
  $fields['billing']['billing_city']['placeholder'] = esc_html__('City', 'twmp-ath');
  $fields['billing']['billing_email']['placeholder'] = esc_html__('Email', 'twmp-ath');

  $fields['shipping']['shipping_first_name']['placeholder'] = esc_html__('First name', 'twmp-ath');
  $fields['shipping']['shipping_last_name']['placeholder'] = esc_html__('Last name', 'twmp-ath');
  $fields['shipping']['shipping_phone']['placeholder'] = esc_html__('Phone', 'twmp-ath');
  $fields['shipping']['shipping_city']['placeholder'] = esc_html__('City', 'twmp-ath');

  return $fields;
}

function wcs_checkout_render_shop_steps()
{
  get_template_part('templates/blocks/shop-steps', null, []);
}

function twmp_checkout_get_cart_item_key_by_product_id($product_id = 0)
{
  if (!function_exists('WC') || !WC()->cart) {
    return '';
  }

  $product_id = absint($product_id);
  if (!$product_id) {
    $product_id = twmp_checkout_get_ticket_product_id();
  }

  if (!$product_id) {
    return '';
  }

  foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
    if (!empty($cart_item['product_id']) && absint($cart_item['product_id']) === $product_id) {
      return $cart_item_key;
    }
  }

  return '';
}

add_action('devvn_checkout_fields', function ($fields) {
  unset($fields['billing']['billing_state']);
  unset($fields['billing']['billing_city']);
  unset($fields['billing']['billing_address_1']);
  unset($fields['billing']['billing_address_2']);

  return $fields;
}, 10, 1);


// add_filter('woocommerce_checkout_fields', function ($fields) {

//   $fields['billing']['billing_sexy'] = array(
//     'type'     => 'billing_sexy_custom',
//     'required' => true,
//     'priority' => 5,
//   );

//   $fields['billing']['billing_shipping_label'] = array(
//     'type'     => 'billing_shipping_label_custom',
//     'priority' => 200, // sau email
//   );

//   $fields['billing']['billing_delivery_address'] = array(
//     'type'        => 'text',
//     'label'       => '',
//     'placeholder' => 'Nhập địa chỉ nhận hàng',
//     'required'    => true,
//     'class'       => array('form-row-wide'),
//     'priority'    => 300,
//   );

//   $fields['billing']['billing_district_district'] = array(
//     'type'        => 'text',
//     'label'       => '',
//     'placeholder' => 'Nhập quận / huyện',
//     'required'    => true,
//     'class'       => array('form-row-first'),
//     'priority'    => 301,
//   );

//   $fields['billing']['billing_wards_and_communes'] = array(
//     'type'        => 'text',
//     'label'       => '',
//     'placeholder' => 'Nhập phường / xã',
//     'required'    => true,
//     'class'       => array('form-row-last'),
//     'priority'    => 302,
//   );

  // $fields['billing']['billing_city_province_shop'] = array(
  //   'type'              => 'select',
  //   'label'             => esc_html__('Province/City', 'twmp-ath'),
  //   'required'          => true,
  //   'class'             => array('form-row-wide'),
  //   'input_class'       => array('regular-select'),
  //   'options'           => array(
  //     ''                => esc_html__('Province/City', 'twmp-ath'),
  //     'Hồ Chí Minh'     => 'Hồ Chí Minh',
  //   )
  // );
//   return $fields;
// });

function twmp_checkout_get_ticket_product_data($product_id = 0)
{
  $product_id = absint($product_id);
  if (!$product_id && function_exists('get_the_ID')) {
    $product_id = absint(get_the_ID());
  }

  $data = array(
    'product_id'   => $product_id,
    'performances'  => array(),
    'ticket_prices' => array(),
  );

  if (!$product_id || !function_exists('get_field')) {
    return $data;
  }

  $performance_rows = (array) get_field('ath_performance_schedule', $product_id);
  foreach ($performance_rows as $row) {
    $datetime_raw = isset($row['performance_datetime']) ? trim((string) $row['performance_datetime']) : '';
    if ($datetime_raw === '') {
      continue;
    }

    $timestamp = strtotime($datetime_raw);
    if (!$timestamp) {
      continue;
    }

    $key = 'performance-' . md5($datetime_raw);
    $day = wp_date('l', $timestamp);
    $date = wp_date('d M Y', $timestamp);
    $time = wp_date('H:i', $timestamp);

    $data['performances'][$key] = array(
      'key'          => $key,
      'datetime'     => $datetime_raw,
      'timestamp'    => $timestamp,
      'day'          => $day,
      'date'         => $date,
      'time'         => $time,
      'display'      => sprintf('%s | %s %s', $day, $date, $time),
      'display_short'=> sprintf('%s %s', $date, $time),
    );
  }

  $price_rows = (array) get_field('ath_ticket_price_options', $product_id);
  foreach ($price_rows as $row) {
    $label = isset($row['label']) ? trim((string) $row['label']) : '';
    $price_raw = isset($row['price']) ? $row['price'] : '';
    $price = (float) wc_format_decimal($price_raw);

    if ($label === '' || $price <= 0) {
      continue;
    }

    $key = 'price-' . md5($label . '|' . $price);
    $data['ticket_prices'][$key] = array(
      'key'   => $key,
      'label' => $label,
      'price' => $price,
    );
  }

  return $data;
}

function twmp_checkout_get_ticket_product_id()
{
  if (!function_exists('WC') || !WC()->cart) {
    return 0;
  }

  foreach (WC()->cart->get_cart() as $cart_item) {
    $product_id = !empty($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
    if (!$product_id) {
      continue;
    }

    $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
    if (!empty($ticket_data['performances']) || !empty($ticket_data['ticket_prices'])) {
      return $product_id;
    }
  }

  return 0;
}

function twmp_checkout_get_ticket_selection_defaults($product_id = 0)
{
  $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
  $performance_key = '';
  $price_key = '';

  if (!empty($ticket_data['performances'])) {
    $performance_key = array_key_first($ticket_data['performances']);
  }

  if (!empty($ticket_data['ticket_prices'])) {
    $price_key = array_key_first($ticket_data['ticket_prices']);
  }

  return array(
    'product_id'      => absint($ticket_data['product_id']),
    'performance_key' => $performance_key,
    'price_key'       => $price_key,
  );
}

function twmp_checkout_resolve_ticket_selection($product_id, $selection)
{
  $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
  $defaults = twmp_checkout_get_ticket_selection_defaults($product_id);

  $performance_key = isset($selection['performance_key']) ? sanitize_key($selection['performance_key']) : '';
  $price_key = isset($selection['price_key']) ? sanitize_key($selection['price_key']) : '';

  if (empty($ticket_data['performances']) || empty($ticket_data['performances'][$performance_key])) {
    $performance_key = $defaults['performance_key'];
  }

  if (empty($ticket_data['ticket_prices']) || empty($ticket_data['ticket_prices'][$price_key])) {
    $price_key = $defaults['price_key'];
  }

  return array(
    'product_id'      => absint($product_id),
    'performance_key' => $performance_key,
    'price_key'       => $price_key,
  );
}

function twmp_checkout_get_ticket_selection_state($product_id = 0)
{
  $defaults = twmp_checkout_get_ticket_selection_defaults($product_id);
  $state = $defaults;

  if (function_exists('WC') && WC()->session) {
    $stored = (array) WC()->session->get('twmp_ticket_selection', array());
    $state = array_merge($state, array_filter($stored, 'strlen'));

    if (empty($state['product_id'])) {
      $state['product_id'] = $defaults['product_id'];
    }

    if (empty($state['performance_key']) && !empty($defaults['performance_key'])) {
      $state['performance_key'] = $defaults['performance_key'];
    }

    if (empty($state['price_key']) && !empty($defaults['price_key'])) {
      $state['price_key'] = $defaults['price_key'];
    }

    WC()->session->set('twmp_ticket_selection', $state);
  }

  return $state;
}

function twmp_checkout_render_ticket_detail_section()
{
  $product_id = twmp_checkout_get_ticket_product_id();
  if (!$product_id) {
    return;
  }

  $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
  if (empty($ticket_data['performances']) && empty($ticket_data['ticket_prices'])) {
    return;
  }

  $state = twmp_checkout_get_ticket_selection_state($product_id);
  $quantity = 1;
  $cart_item_key = twmp_checkout_get_cart_item_key_by_product_id($product_id);

  if ($cart_item_key && function_exists('WC') && WC()->cart) {
    foreach (WC()->cart->get_cart() as $current_cart_item_key => $cart_item) {
      if ($current_cart_item_key !== $cart_item_key) {
        continue;
      }

      $quantity = !empty($cart_item['quantity']) ? max(1, absint($cart_item['quantity'])) : 1;
      break;
    }
  }
  ?>
  <section class="twmp-checkout-card twmp-checkout-card--ticket-detail">
    <input type="hidden" name="twmp_ticket_product_id" value="<?php echo esc_attr($product_id); ?>">

    <header class="twmp-checkout-card__header">
      <span class="twmp-checkout-card__step">2</span>
      <h3 class="twmp-checkout-card__title"><?php echo esc_html__('Ticket detail', 'twmp-ath'); ?></h3>
    </header>

    <div class="twmp-checkout-card__content">
      <?php if (!empty($ticket_data['performances'])) : ?>
        <div class="twmp-checkout-ticket-detail__group twmp-checkout-ticket-detail__group--performance">
          <p class="twmp-checkout-ticket-detail__label"><?php echo esc_html__('Select Performance date *', 'twmp-ath'); ?></p>
          <div class="twmp-checkout-ticket-detail__options twmp-checkout-ticket-detail__options--performance">
          <?php foreach ($ticket_data['performances'] as $option) : ?>
            <label class="twmp-ticket-option <?php echo esc_attr($state['performance_key'] === $option['key'] ? 'is-selected' : ''); ?>">
              <input
                type="radio"
                name="twmp_ticket_performance"
                value="<?php echo esc_attr($option['key']); ?>"
                <?php checked($state['performance_key'], $option['key']); ?>
                required
              >
              <span class="twmp-ticket-option__main">
                <span class="twmp-ticket-option__day"><?php echo esc_html($option['day']); ?></span>
                <span class="twmp-ticket-option__date"><?php echo esc_html($option['date']); ?></span>
              </span>
              <span class="twmp-ticket-option__time"><?php echo esc_html($option['time']); ?></span>
            </label>
          <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($ticket_data['ticket_prices'])) : ?>
        <div class="twmp-checkout-ticket-detail__group twmp-checkout-ticket-detail__group--price">
          <p class="twmp-checkout-ticket-detail__label"><?php echo esc_html__('Select type of ticket', 'twmp-ath'); ?></p>
          <div class="twmp-checkout-ticket-detail__options twmp-checkout-ticket-detail__options--price">
          <?php foreach ($ticket_data['ticket_prices'] as $option) : ?>
            <label class="twmp-ticket-price-option <?php echo esc_attr($state['price_key'] === $option['key'] ? 'is-selected' : ''); ?>">
              <input
                type="radio"
                name="twmp_ticket_price_option"
                value="<?php echo esc_attr($option['key']); ?>"
                <?php checked($state['price_key'], $option['key']); ?>
                required
              >
              <span class="twmp-ticket-price-option__label"><?php echo esc_html($option['label']); ?></span>
              <span class="twmp-ticket-price-option__price"><?php echo wp_kses_post(wc_price($option['price'])); ?></span>
              <span class="twmp-ticket-price-option__unit"><?php echo esc_html__('/ Ticket', 'twmp-ath'); ?></span>
            </label>
          <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="twmp-checkout-ticket-detail__group twmp-checkout-ticket-detail__group--quantity">
        <p class="twmp-checkout-ticket-detail__label"><?php echo esc_html__('Quantity', 'twmp-ath'); ?> *</p>
        <div class="twmp-ticket-quantity" data-ticket-quantity-control>
          <button type="button" class="twmp-ticket-quantity__button" data-ticket-quantity-step="minus" aria-label="<?php echo esc_attr__('Decrease quantity', 'twmp-ath'); ?>">-</button>
          <input
            type="number"
            name="twmp_ticket_quantity"
            class="twmp-ticket-quantity__input"
            value="<?php echo esc_attr($quantity); ?>"
            min="1"
            step="1"
            inputmode="numeric"
            required
          >
          <button type="button" class="twmp-ticket-quantity__button" data-ticket-quantity-step="plus" aria-label="<?php echo esc_attr__('Increase quantity', 'twmp-ath'); ?>">+</button>
        </div>
      </div>
    </div>
  </section>
  <?php
}

add_action('woocommerce_checkout_after_customer_details', 'twmp_checkout_render_ticket_detail_section', 20);

add_action('woocommerce_checkout_update_order_review', function ($posted_data) {
  if (!function_exists('WC') || !WC()->session) {
    return;
  }

  parse_str($posted_data, $data);

  $product_id = !empty($data['twmp_ticket_product_id']) ? absint($data['twmp_ticket_product_id']) : twmp_checkout_get_ticket_product_id();
  if (!$product_id) {
    return;
  }

  $selection = twmp_checkout_resolve_ticket_selection($product_id, array(
    'performance_key' => isset($data['twmp_ticket_performance']) ? sanitize_key($data['twmp_ticket_performance']) : '',
    'price_key'       => isset($data['twmp_ticket_price_option']) ? sanitize_key($data['twmp_ticket_price_option']) : '',
  ));

  WC()->session->set('twmp_ticket_selection', $selection);

  $quantity = !empty($data['twmp_ticket_quantity']) ? max(1, absint($data['twmp_ticket_quantity'])) : 0;
  if ($quantity > 0 && function_exists('WC') && WC()->cart) {
    $cart_item_key = twmp_checkout_get_cart_item_key_by_product_id($product_id);
    if ($cart_item_key) {
      $current_quantity = 0;
      foreach (WC()->cart->get_cart() as $current_cart_item_key => $cart_item) {
        if ($current_cart_item_key === $cart_item_key) {
          $current_quantity = !empty($cart_item['quantity']) ? absint($cart_item['quantity']) : 0;
          break;
        }
      }

      if ($current_quantity !== $quantity) {
        WC()->cart->set_quantity($cart_item_key, $quantity, true);
      }
    }
  }
});

add_action('woocommerce_checkout_process', function () {
  $product_id = !empty($_POST['twmp_ticket_product_id']) ? absint($_POST['twmp_ticket_product_id']) : twmp_checkout_get_ticket_product_id();
  if (!$product_id) {
    return;
  }

  $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
  if (empty($ticket_data['performances']) && empty($ticket_data['ticket_prices'])) {
    return;
  }

  $performance_key = isset($_POST['twmp_ticket_performance']) ? sanitize_key(wp_unslash($_POST['twmp_ticket_performance'])) : '';
  $price_key = isset($_POST['twmp_ticket_price_option']) ? sanitize_key(wp_unslash($_POST['twmp_ticket_price_option'])) : '';

  if (!empty($ticket_data['performances']) && empty($performance_key)) {
    wc_add_notice(__('Please select a performance date.', 'twmp-ath'), 'error');
  } elseif (!empty($ticket_data['performances']) && empty($ticket_data['performances'][$performance_key])) {
    wc_add_notice(__('The selected performance date is invalid.', 'twmp-ath'), 'error');
  }

  if (!empty($ticket_data['ticket_prices']) && empty($price_key)) {
    wc_add_notice(__('Please select a ticket type.', 'twmp-ath'), 'error');
  } elseif (!empty($ticket_data['ticket_prices']) && empty($ticket_data['ticket_prices'][$price_key])) {
    wc_add_notice(__('The selected ticket type is invalid.', 'twmp-ath'), 'error');
  }
});

add_action('woocommerce_checkout_create_order', function ($order, $data) {
  $product_id = !empty($_POST['twmp_ticket_product_id']) ? absint($_POST['twmp_ticket_product_id']) : twmp_checkout_get_ticket_product_id();
  if (!$product_id) {
    return;
  }

  $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
  if (empty($ticket_data['performances']) && empty($ticket_data['ticket_prices'])) {
    return;
  }

  $selection = twmp_checkout_resolve_ticket_selection($product_id, array(
    'performance_key' => isset($_POST['twmp_ticket_performance']) ? sanitize_key(wp_unslash($_POST['twmp_ticket_performance'])) : '',
    'price_key'       => isset($_POST['twmp_ticket_price_option']) ? sanitize_key(wp_unslash($_POST['twmp_ticket_price_option'])) : '',
  ));

  $order->update_meta_data('_twmp_ticket_product_id', $product_id);

  if (!empty($selection['performance_key']) && !empty($ticket_data['performances'][$selection['performance_key']])) {
    $performance = $ticket_data['performances'][$selection['performance_key']];
    $order->update_meta_data('_twmp_ticket_performance_key', $performance['key']);
    $order->update_meta_data('_twmp_ticket_performance_label', $performance['display']);
    $order->update_meta_data('_twmp_ticket_performance_datetime', $performance['datetime']);
  }

  if (!empty($selection['price_key']) && !empty($ticket_data['ticket_prices'][$selection['price_key']])) {
    $price_option = $ticket_data['ticket_prices'][$selection['price_key']];
    $order->update_meta_data('_twmp_ticket_price_key', $price_option['key']);
    $order->update_meta_data('_twmp_ticket_price_label', $price_option['label']);
    $order->update_meta_data('_twmp_ticket_price_amount', $price_option['price']);
  }
}, 20, 2);

add_action('woocommerce_before_calculate_totals', function ($cart) {
  if (is_admin() && !defined('DOING_AJAX')) {
    return;
  }

  if (!function_exists('WC') || !WC()->session || !WC()->cart) {
    return;
  }

  $selection = (array) WC()->session->get('twmp_ticket_selection', array());
  $product_id = !empty($selection['product_id']) ? absint($selection['product_id']) : twmp_checkout_get_ticket_product_id();
  if (!$product_id) {
    return;
  }

  $ticket_data = twmp_checkout_get_ticket_product_data($product_id);
  if (empty($ticket_data['ticket_prices'])) {
    return;
  }

  $price_key = !empty($selection['price_key']) ? sanitize_key($selection['price_key']) : '';
  if (empty($price_key) || empty($ticket_data['ticket_prices'][$price_key])) {
    $defaults = twmp_checkout_get_ticket_selection_defaults($product_id);
    $price_key = $defaults['price_key'];
    if (empty($price_key) || empty($ticket_data['ticket_prices'][$price_key])) {
      return;
    }

    $selection['product_id'] = $product_id;
    $selection['price_key'] = $price_key;
    WC()->session->set('twmp_ticket_selection', $selection);
  }

  $price = (float) $ticket_data['ticket_prices'][$price_key]['price'];
  foreach ($cart->get_cart() as $cart_item) {
    $cart_item_product_id = !empty($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
    if ($cart_item_product_id !== $product_id || empty($cart_item['data'])) {
      continue;
    }

    $cart_item['data']->set_price($price);
  }
}, 20);

add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
  $ticket_product_id = $order->get_meta('_twmp_ticket_product_id');
  $ticket_performance = $order->get_meta('_twmp_ticket_performance_label');
  $ticket_price_label = $order->get_meta('_twmp_ticket_price_label');
  $ticket_price_amount = $order->get_meta('_twmp_ticket_price_amount');

  if (!$ticket_product_id && !$ticket_performance && !$ticket_price_label) {
    return;
  }

  echo '<div class="address">';
  echo '<h3>' . esc_html__('Ticket detail', 'twmp-ath') . '</h3>';

  if ($ticket_performance) {
    echo '<p><strong>' . esc_html__('Performance:', 'twmp-ath') . '</strong> ' . esc_html($ticket_performance) . '</p>';
  }

  if ($ticket_price_label) {
    echo '<p><strong>' . esc_html__('Ticket type:', 'twmp-ath') . '</strong> ' . esc_html($ticket_price_label);
    if ($ticket_price_amount !== '') {
      echo ' - ' . wp_kses_post(wc_price((float) $ticket_price_amount));
    }
    echo '</p>';
  }

  echo '</div>';
}, 20);
/**
add_filter('woocommerce_form_field_billing_sexy_custom', function ($field, $key, $args, $value) {

  $value = empty($value) ? 'male' : $value;

  ob_start();
?>
  <p class="form-row billing-radio form-row-wide" id="billing_sexy_field" data-priority="10">
    <span class="woocommerce-input-wrapper">
      <input type="radio" class="input-radio " value="male" name="<?php echo esc_attr($key); ?>" id="billing_sexy_male" <?php checked($value, 'male'); ?>>
      <label for="billing_sexy_male" class="radio ">Anh
      </label>
      <input type="radio" class="input-radio " value="female" name="<?php echo esc_attr($key); ?>" id="billing_sexy_female" <?php checked($value, 'female'); ?>>
      <label for="billing_sexy_female" class="radio ">Chị
      </label>
    </span>
  </p>
<?php

  return ob_get_clean();
}, 10, 4);

add_filter('woocommerce_form_field_billing_shipping_label_custom', function ($field, $key, $args, $value) {
  $value = empty($value) ? 'nhan-hang-tai-nha' : $value;
  ob_start();
?>
  <p class="form-row billing-radio form-row-wide" id="billing_delivery_form_field" data-priority="90">
    <label for="billing_delivery_form_nhan-hang-tai-nha" class="">Hình thức nhận hàng
      <span class="optional">(tuỳ chọn)</span>
    </label>
    <span class="woocommerce-input-wrapper">
      <input type="radio" class="input-radio " value="nhan-hang-tai-nha" name="billing_delivery_form" id="billing_delivery_form_nhan-hang-tai-nha" <?php checked($value, 'nhan-hang-tai-nha'); ?>>
      <label for="billing_delivery_form_nhan-hang-tai-nha" class="radio ">Nhận hàng tại nhà
        <span class="optional">(tuỳ chọn)</span>
      </label>
      <input type="radio" class="input-radio " value="nhan-tai-cua-hang" name="billing_delivery_form" id="billing_delivery_form_nhan-tai-cua-hang" <?php checked($value, 'nhan-tai-cua-hang'); ?>>
      <label for="billing_delivery_form_nhan-tai-cua-hang" class="radio ">Nhận tại cửa hàng
        <span class="optional">(tuỳ chọn)</span>
      </label>
    </span>
  </p>
<?php
  return ob_get_clean();
}, 10, 4);

// Lưu custom fields vào order meta
add_action('woocommerce_checkout_update_order_meta', 'twmp_save_custom_checkout_fields');
function twmp_save_custom_checkout_fields($order_id) {
  // Lưu billing_sexy
  if (!empty($_POST['billing_sexy'])) {
    update_post_meta($order_id, '_billing_sexy', sanitize_text_field($_POST['billing_sexy']));
  }

  // Lưu billing_delivery_form (từ billing_shipping_label)
  if (!empty($_POST['billing_delivery_form'])) {
    update_post_meta($order_id, '_billing_delivery_form', sanitize_text_field($_POST['billing_delivery_form']));
  }

  // Lưu các field text bổ sung (mặc dù WooCommerce tự động lưu, nhưng đảm bảo)
  if (!empty($_POST['billing_delivery_address'])) {
    update_post_meta($order_id, '_billing_delivery_address', sanitize_text_field($_POST['billing_delivery_address']));
  }

  if (!empty($_POST['billing_district_district'])) {
    update_post_meta($order_id, '_billing_district_district', sanitize_text_field($_POST['billing_district_district']));
  }

  if (!empty($_POST['billing_wards_and_communes'])) {
    update_post_meta($order_id, '_billing_wards_and_communes', sanitize_text_field($_POST['billing_wards_and_communes']));
  }
}

// Hiển thị custom fields trong admin order details
add_action('woocommerce_admin_order_data_after_billing_address', 'twmp_display_custom_fields_in_admin');
function twmp_display_custom_fields_in_admin($order) {
  $sexy = get_post_meta($order->get_id(), '_billing_sexy', true);
  $delivery_form = get_post_meta($order->get_id(), '_billing_delivery_form', true);
  $delivery_address = get_post_meta($order->get_id(), '_billing_delivery_address', true);
  $district = get_post_meta($order->get_id(), '_billing_district_district', true);
  $wards = get_post_meta($order->get_id(), '_billing_wards_and_communes', true);

  if ($sexy || $delivery_form || $delivery_address || $district || $wards) {
    echo '<div class="address">';
    echo '<h3>' . esc_html__('Thông tin bổ sung', 'twmp-ath') . '</h3>';
    if ($sexy) {
      echo '<p><strong>' . esc_html__('Giới tính:', 'twmp-ath') . '</strong> ' . esc_html($sexy === 'male' ? 'Anh' : 'Chị') . '</p>';
    }
    if ($delivery_form) {
      $delivery_label = $delivery_form === 'nhan-hang-tai-nha' ? 'Nhận hàng tại nhà' : 'Nhận tại cửa hàng';
      echo '<p><strong>' . esc_html__('Hình thức nhận hàng:', 'twmp-ath') . '</strong> ' . esc_html($delivery_label) . '</p>';
    }
    if ($delivery_address) {
      echo '<p><strong>' . esc_html__('Địa chỉ nhận hàng:', 'twmp-ath') . '</strong> ' . esc_html($delivery_address) . '</p>';
    }
    if ($district) {
      echo '<p><strong>' . esc_html__('Quận/Huyện:', 'twmp-ath') . '</strong> ' . esc_html($district) . '</p>';
    }
    if ($wards) {
      echo '<p><strong>' . esc_html__('Phường/Xã:', 'twmp-ath') . '</strong> ' . esc_html($wards) . '</p>';
    }
    echo '</div>';
  }
}

add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
  if (!empty($_POST['billing_birth_date'])) {
    update_post_meta($order_id, '_billing_birth_date', sanitize_text_field($_POST['billing_birth_date']));
  }

  if (!empty($_POST['billing_age'])) {
    update_post_meta($order_id, '_billing_age', absint($_POST['billing_age']));
  }
}, 20);

add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
  $birth_date = get_post_meta($order->get_id(), '_billing_birth_date', true);
  $age = get_post_meta($order->get_id(), '_billing_age', true);

  if (!$birth_date && !$age) {
    return;
  }

  echo '<div class="address">';
  echo '<h3>' . esc_html__('Booking information', 'twmp-ath') . '</h3>';

  if ($birth_date) {
    echo '<p><strong>' . esc_html__('Date of Birth:', 'twmp-ath') . '</strong> ' . esc_html($birth_date) . '</p>';
  }

  if ($age) {
    echo '<p><strong>' . esc_html__('Age:', 'twmp-ath') . '</strong> ' . esc_html($age) . '</p>';
  }

  echo '</div>';
}, 30);

// add_filter('woocommerce_checkout_fields', function ($fields) {
//   $fields['billing']['billing_delivery_address'] = array(
//     'type'        => 'select',
//     'label'       => esc_html__('Province/City', 'twmp-ath'),
//     'required'    => true,
//     'class'       => array('form-row-wide'),
//     'input_class' => 'regular-select',
//     'options'     => get_tinh_thanh_pho()
//   );
//   return $fields;
// });

// add_filter('woocommerce_checkout_fields', function ($fields) {
//   $fields['billing']['billing_district_shop'] = array(
//     'type'        => 'select',
//     'label'       => esc_html__('District', 'twmp-ath'),
//     'required'    => true,
//     'class'       => array('form-row-wide'),
//     'input_class' => array('regular-select'),
//     'options'     => array(
//       ''          => esc_html__('District', 'twmp-ath'),
//       'Quận 1'    => 'Quận 1',
//     )
//   );
//   return $fields;
// });
 */
add_filter('woocommerce_checkout_fields', function ($fields) {
  $hidden_billing_fields = array(
    'billing_company',
    'billing_address_1',
    'billing_address_2',
    'billing_city',
    'billing_state',
    'billing_postcode',
  );

  foreach ($hidden_billing_fields as $field_key) {
    if (isset($fields['billing'][$field_key])) {
      $fields['billing'][$field_key]['type'] = 'hidden';
      $fields['billing'][$field_key]['required'] = false;
    }
  }

  if (isset($fields['billing']['billing_country'])) {
    $fields['billing']['billing_country']['type'] = 'hidden';
    $fields['billing']['billing_country']['required'] = false;
    $fields['billing']['billing_country']['default'] = 'VN';
  }

  if (isset($fields['billing']['billing_first_name'])) {
    $fields['billing']['billing_first_name']['type'] = 'text';
    $fields['billing']['billing_first_name']['required'] = true;
    $fields['billing']['billing_first_name']['label'] = '';
    $fields['billing']['billing_first_name']['placeholder'] = esc_html__('First Name', 'twmp-ath');
    $fields['billing']['billing_first_name']['class'] = array('form-row-first', 'twmp-checkout-field');
    $fields['billing']['billing_first_name']['priority'] = 10;
  }

  if (isset($fields['billing']['billing_last_name'])) {
    $fields['billing']['billing_last_name']['type'] = 'text';
    $fields['billing']['billing_last_name']['required'] = true;
    $fields['billing']['billing_last_name']['label'] = '';
    $fields['billing']['billing_last_name']['placeholder'] = esc_html__('Last Name', 'twmp-ath');
    $fields['billing']['billing_last_name']['class'] = array('form-row-last', 'twmp-checkout-field');
    $fields['billing']['billing_last_name']['priority'] = 20;
  }

  if (isset($fields['billing']['billing_phone'])) {
    $fields['billing']['billing_phone']['type'] = 'tel';
    $fields['billing']['billing_phone']['required'] = true;
    $fields['billing']['billing_phone']['label'] = '';
    $fields['billing']['billing_phone']['placeholder'] = esc_html__('Phone number', 'twmp-ath');
    $fields['billing']['billing_phone']['class'] = array('form-row-first', 'twmp-checkout-field');
    $fields['billing']['billing_phone']['priority'] = 30;
  }

  $fields['billing']['billing_birth_date'] = array(
    'type'        => 'date',
    'label'       => '',
    'placeholder' => esc_html__('Date of Birth', 'twmp-ath'),
    'required'    => true,
    'class'       => array('form-row-first', 'twmp-checkout-field'),
    'priority'    => 40,
  );

  if (isset($fields['billing']['billing_email'])) {
    $fields['billing']['billing_email']['type'] = 'email';
    $fields['billing']['billing_email']['required'] = true;
    $fields['billing']['billing_email']['label'] = '';
    $fields['billing']['billing_email']['placeholder'] = esc_html__('Email', 'twmp-ath');
    $fields['billing']['billing_email']['class'] = array('form-row-last', 'twmp-checkout-field');
    $fields['billing']['billing_email']['priority'] = 50;
  }

  $fields['billing']['billing_age'] = array(
    'type'              => 'number',
    'label'             => '',
    'placeholder'       => esc_html__('Age', 'twmp-ath'),
    'required'          => true,
    'class'             => array('form-row-last', 'twmp-checkout-field'),
    'custom_attributes' => array(
      'min'       => 1,
      'step'      => 1,
      'inputmode' => 'numeric',
    ),
    'priority'          => 60,
  );

  return $fields;
}, 20);

add_filter('woocommerce_add_to_cart_redirect', function ($url) {
  if (!empty($_REQUEST['twmp_buy_now'])) {
    return wc_get_cart_url();
  }

  return $url;
});

add_filter('woocommerce_checkout_fields', function ($fields) {
  $fields['billing']['billing_first_name']['type'] = 'hidden';
  $fields['billing']['billing_first_name']['required'] = false;
  $fields['billing']['billing_country']['type'] = 'hidden';
  $fields['billing']['billing_country']['required'] = false;
  $fields['billing']['billing_address_1']['type'] = 'hidden';
  $fields['billing']['billing_address_1']['required'] = false;
  $fields['billing']['billing_address_2']['type'] = 'hidden';
  $fields['billing']['billing_postcode']['type'] = 'hidden';
  $fields['billing']['billing_city']['type'] = 'hidden';
  $fields['billing']['billing_city']['required'] = false;
  $fields['billing']['billing_country']['default'] = 'VN'; // đổi thành mã quốc gia mong muốn

  $fields['billing']['billing_last_name']['placeholder'] = 'Nhập họ và tên';
  $fields['billing']['billing_phone']['placeholder']     = 'Nhập số điện thoại';
  $fields['billing']['billing_email']['placeholder']     = 'Nhập địa chỉ email';
  return $fields;
});
