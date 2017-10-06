<?php
/**
 * Plugin Name: Threekit for PaletteEnvy
 * Description: Threekit for PaletteEnvy
 * Version: 1.0.3
 * Author: Exocortex
 * Author URI: exocortex.com
 * Developer: Exocortex
 * Developer URI: http://exocortex.com/
 * Text Domain: Threekit-for-PaletteEnvy
 *
 * Copyright: © 2017 Exocortex
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/

$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
if ( in_array( 'woocommerce/woocommerce.php', $active_plugins) ) {
  // Put your plugin code here
  // add player after single_excerpt(short description) and single_meta
  add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_clara', 10 );
  //add_filter( 'woocommerce_single_product_image_thumbnail_html', 'woocommerce_template_single_clara_filter', 10, 2 );
}

if ( ! function_exists(woocommerce_template_single_clara) ) {
  function woocommerce_template_single_clara() {
    global $product;
    load_template(rtrim(plugin_dir_path(__FILE__),'/') . '/templates/single-product/clara-player.php');

    wp_enqueue_script( 'claraPlayer', 'https://clara.io/js/claraplayer.min.js');
    // load scripts to init clara player
    wp_enqueue_script( 'claraPlayerControl', rtrim(plugin_dir_url(__FILE__),'/') . '/assets/js/ClaraForPaletteenvy.js');
    $dataToBePassed = array(
      'name' => $product->get_name()
    );
    // variables will be json encoded here
    wp_localize_script('claraPlayerControl', 'php_vars', $dataToBePassed);
  }
}

if ( ! function_exists(woocommerce_template_single_clara_filter ) ) {
  function woocommerce_template_single_clara_filter($html, $id) {
    $html = "<div id='clara-embed'></div>" . $html;
    global $product;
    wp_enqueue_script( 'claraPlayer', 'https://clara.io/js/claraplayer.min.js');
    // load scripts to init clara player
    wp_enqueue_script( 'claraPlayerControl', rtrim(plugin_dir_url(__FILE__),'/') . '/assets/js/ClaraForPaletteenvy.js');
    $dataToBePassed = array(
      'name' => $product->get_name()
    );
    // variables will be json encoded here
    wp_localize_script('claraPlayerControl', 'php_vars', $dataToBePassed);
    return $html;
  }
}

?>
