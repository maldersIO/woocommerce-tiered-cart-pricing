<?php
/**
* Plugin Name: Easy Tiered Pricing for WooCommerce
* Plugin URI: https://github.com/maldersIO/woocommerce-tiered-cart-pricing/
* Description: Effortlessly set up discounts based on cart total ranges
* Version: 1.0.0
* Author: The maldersIO Team
* Author URI: https://malders.io
* License: GNU v3.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/* Easy Tiered Pricing for WooCommerce */
//______________________________________________________________________________
// Add a new settings page under WooCommerce menu
function add_tiered_pricing_settings_page() {
    add_submenu_page(
        'woocommerce',
        'Tiered Pricing',
        'Tiered Pricing',
        'manage_options',
        'tiered_pricing_settings',
        'render_tiered_pricing_settings_page'
    );
}
add_action('admin_menu', 'add_tiered_pricing_settings_page');

// Render the settings page
function render_tiered_pricing_settings_page() {
    ?>
    <div class="wrap">
        <h1>Tiered Pricing Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('tiered_pricing_settings');
            do_settings_sections('tiered_pricing_settings');
            submit_button('Save Changes');
            ?>
        </form>
    </div>
    <?php
}

// Register and initialize settings, and handle saving and retrieving discounts
function tiered_pricing_settings_init() {
    register_setting('tiered_pricing_settings', 'tiered_pricing_discounts', 'sanitize_discounts');
    
    add_settings_section(
        'tiered_pricing_section',
        'Tiered Pricing Discounts',
        'tiered_pricing_section_callback',
        'tiered_pricing_settings'
    );
    
    add_settings_field(
        'tiered_pricing_discounts_field',
        '',
        'tiered_pricing_discounts_field_callback',
        'tiered_pricing_settings',
        'tiered_pricing_section'
    );
}
add_action('admin_init', 'tiered_pricing_settings_init');

// Render section callback
function tiered_pricing_section_callback() {
    echo 'Add tiered pricing discounts here';
}

// Render field callback for discounts with ranges
function tiered_pricing_discounts_field_callback() {
    $discounts = get_option('tiered_pricing_discounts', array());
    ?>
    <div id="tiered_pricing_discounts">
		<style>p input,label{display:block!important;min-width:100px;}.form-table td p{margin:10px;}.remove_discount{min-width:100%}</style>
        <table class="form-table">
    <tr>
        <th>From</th>
        <th>To</th>
        <th>Discount</th>
    </tr>
    <?php
    if ($discounts) {
        foreach ($discounts as $index => $discount) {
            echo '<tr class="discount-row">';
            echo '<td><input type="number" name="tiered_pricing_discounts[' . $index . '][from]" value="' . esc_attr($discount['from']) . '" placeholder="From"></td>';
            echo '<td><input type="number" name="tiered_pricing_discounts[' . $index . '][to]" value="' . ($discount['to'] ? esc_attr($discount['to']) : '') . '" placeholder="To"></td>';
            echo '<td><input type="number" name="tiered_pricing_discounts[' . $index . '][amount]" value="' . esc_attr($discount['amount']) . '" placeholder="Discount Amount"></td>';
            echo '<td><button type="button" class="button remove_discount button-primary">Remove</button></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3">No discounts added yet.</td></tr>';
    }
    ?>
    <tr>
        <td colspan="3">
            <button type="button" class="button" id="add_discount">Add Discount</button>
        </td>
    </tr>
</table>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('#add_discount').click(function() {
                var index = $('.discount-row').length;
                var row = '<div class="discount-row"><p>From $<input type="number" name="tiered_pricing_discounts[' + index + '][from]" placeholder="From"> to $<input type="number" name="tiered_pricing_discounts[' + index + '][to]" placeholder="To">Discount  %<input type="number" name="tiered_pricing_discounts[' + index + '][amount]" placeholder="Discount Amount"></p><button type="button" class="button remove_discount">Remove</button></div>';
                $('#tiered_pricing_discounts table').append(row);
            });
            $(document).on('click', '.remove_discount', function() {
                $(this).closest('.discount-row').remove();
            });
        });
    </script>
    <?php
}

// Sanitize discounts
function sanitize_discounts($input) {
    $sanitized_input = array();
    if (is_array($input)) {
        foreach ($input as $index => $discount) {
            $from = sanitize_text_field($discount['from']);
            $to = sanitize_text_field($discount['to']);
            $amount = sanitize_text_field($discount['amount']);
            if (!empty($from) && !empty($amount)) {
                $sanitized_input[] = array(
                    'from' => $from,
                    'to' => $to,
                    'amount' => $amount
                );
            }
        }
    }
    return $sanitized_input;
}

// Apply discounts based on ranges
function apply_tiered_pricing_discounts($cart) {
    $discounts = get_option('tiered_pricing_discounts', array());
    if ($discounts) {
        $cart_total = $cart->get_cart_contents_total();
        foreach ($discounts as $discount) {
            if ($cart_total >= $discount['from'] && ($discount['to'] === '' || $cart_total <= $discount['to'])) {
                $discount_amount = $cart_total * ($discount['amount'] / 100);
                $cart->add_fee('Discount', -$discount_amount);
                break; // Apply only one discount
            }
        }
    }
}
add_action('woocommerce_cart_calculate_fees', 'apply_tiered_pricing_discounts');

//______________________________________________________________________________
// All About Updates

//  Begin Version Control | Auto Update Checker
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
// ***IMPORTANT*** Update this path to New Github Repository Master Branch Path
	'https://github.com/maldersIO/woocommerce-tiered-cart-pricing',
	__FILE__,
// ***IMPORTANT*** Update this to New Repository Master Branch Path
	'woocommerce-tiered-cart-pricing'
);
//Enable Releases
$myUpdateChecker->getVcsApi()->enableReleaseAssets();
//Optional: If you're using a private repository, specify the access token like this:
//
//
//Future Update Note: Comment in these sections and add token and branch information once private git established
//
//
//$myUpdateChecker->setAuthentication('your-token-here');
//Optional: Set the branch that contains the stable release.
//$myUpdateChecker->setBranch('stable-branch-name');

//______________________________________________________________________________
/* PluginName End */
?>
