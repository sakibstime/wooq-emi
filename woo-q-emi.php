<?php
/*
Plugin Name: WooQ EMI
Plugin URI: https://sakibsti.me/
Description: Displays EMI plans on WooCommerce product pages via a shortcode, calculated based on the product's Regular Price. EMI plans are shown for 3, 6, 9, and 12 months.
Version: 2.2
Author: Md. Sohanur Rahman Sakib
Author URI: https://sakibsti.me/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WooQ_EMI {

    public function __construct() {
        // Register the shortcode [wooq_emi]
        add_shortcode('wooq_emi', array($this, 'render_shortcode'));
        // Enqueue necessary CSS for tooltip styling
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function enqueue_styles() {
        wp_register_style('woo-q-emi-style', false);
        wp_enqueue_style('woo-q-emi-style');
        $custom_css = "
            .woo-q-emi-container {
                margin-top: 0;
                font-size: 14px;
            }
            .woo-q-emi-tooltip {
                position: relative;
                display: inline-block;
                cursor: pointer;
                color: #0073aa;
                text-decoration: underline;
            }
            .woo-q-emi-tooltip .woo-q-emi-tooltiptext {
                visibility: hidden;
                width: 220px;
                background-color: #555;
                color: #fff;
                text-align: left;
                border-radius: 6px;
                padding: 5px;
                position: absolute;
                z-index: 1;
                bottom: 125%;
                left: 50%;
                margin-left: -110px;
                opacity: 0;
                transition: opacity 0.3s;
                font-size: 13px;
                line-height: 1.4;
            }
            .woo-q-emi-tooltip:hover .woo-q-emi-tooltiptext {
                visibility: visible;
                opacity: 1;
            }
            .woo-q-emi-tooltip .woo-q-emi-tooltiptext::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                margin-left: -5px;
                border-width: 5px;
                border-style: solid;
                border-color: #555 transparent transparent transparent;
            }
        ";
        wp_add_inline_style('woo-q-emi-style', $custom_css);
    }

    // Shortcode callback function.
    public function render_shortcode() {
        global $product;

        // Attempt to retrieve the product from the current post if not already available.
        if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
            $product_id = get_the_ID();
            $product = wc_get_product( $product_id );
        }

        if ( ! $product ) {
            return ''; // Return nothing if no product is found.
        }

        // Get the Regular Price for EMI calculation.
        $price = $product->get_regular_price();
        if ( empty( $price ) ) {
            return ''; // Return if price is not available.
        }

        $price = floatval( $price );
        $months = array( 3, 6, 9, 12 );
        $emi_plans = array();

        // Calculate EMI for each month duration and round to a whole number.
        foreach ( $months as $m ) {
            $emi = round( $price / $m );
            $emi_plans[ $m ] = $emi;
        }

        // Build the tooltip content with proper escaping.
        $tooltip_text = '';
        foreach ( $emi_plans as $m => $emi ) {
            $tooltip_text .= 'BDT. ' . esc_html( $emi ) . ' for ' . esc_html( $m ) . ' Month' . ( $m > 1 ? 's' : '' ) . '<br>';
        }

        // Build the final output HTML.
        $output  = '<div class="woo-q-emi-container">';
        $output .= 'Avail EMI Offer <span class="woo-q-emi-tooltip">View Plans';
        $output .= '<span class="woo-q-emi-tooltiptext">' . $tooltip_text . '</span>';
        $output .= '</span>';
        $output .= '</div>';

        return $output;
    }
}

// Initialize the plugin.
new WooQ_EMI();
?>
