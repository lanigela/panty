<?php
/**
 * Plugin Name: Threekit for PaletteEnvy
 * Description: Threekit for PaletteEnvy
 * Version: 1.0.0
 * Author: Daniel Zhou
 * Author URI: daniel@exocortex.com
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
  add_filter( 'attachment_fields_to_edit', 'woo_embed_video', 20, 2);
  add_action( 'edit_attachment', 'woo_save_embed_video' );
  add_action( 'wp_head', 'woo_scripts_styles' );
  add_filter('woocommerce_single_product_image_thumbnail_html', 'remove_thumbnail_html');
  add_action( 'woocommerce_product_thumbnails', 'woo_display_embed_video', 20 );
}

function woo_embed_video( $form_fields, $attachment ) {

    $post_id = (int) $_GET[ 'post' ];
    $nonce = wp_create_nonce( 'bdn-attach_' . $attachment->ID );
    $attach_image_action_url = admin_url( "media-upload.php?tab=library&post_id=$post_id" );
    $field_value = get_post_meta( $attachment->ID, 'videolink_id', true );
    $video_site = get_post_meta( $attachment->ID, 'video_site', true );
    $youtube = ($video_site == 'youtube') ? 'checked' : '';
    $vimeo = ($video_site == 'vimeo') ? 'checked' : '';
    $checked = '';
    if(empty($youtube) && empty($vimeo))
    {
        $checked = 'checked';
    }
    $form_fields['videolink_id'] = array(
        'value' => $field_value ? $field_value : '',
        'input' => "text",
        'label' => __( 'Video Link ID' )
    );
    $form_fields['video_site'] = array(
        'input' => 'html',
        'value' => $video_site,
        'html' => "<input type='radio' name='attachments[{$attachment->ID}][video_site]' value='youtube' $youtube $checked> Youtube
                   <input type='radio' name='attachments[{$attachment->ID}][video_site]' value='vimeo' $vimeo> Vimeo",
        'helps' => __( '<b>For Eg.:</b> <br>"112233445" for URL - https://vimeo.com/112233445 <br>
                     <br>"n93gYncUD" for URL - https://www.youtube.com/watch?v=n93gYncUD' )
    );
    return $form_fields;
}

function woo_save_embed_video( $attachment_id ) {
    if ( isset( $_REQUEST['attachments'][$attachment_id]['videolink_id'] ) ) {
        $videolink_id = $_REQUEST['attachments'][$attachment_id]['videolink_id'];
        update_post_meta( $attachment_id, 'videolink_id', $videolink_id );
    }
    if ( isset( $_REQUEST['attachments'][$attachment_id]['video_site'] ) ) {
        $video_site = $_REQUEST['attachments'][$attachment_id]['video_site'];
        update_post_meta( $attachment_id, 'video_site', $video_site );
    }
}

function remove_thumbnail_html($html){
    $html = '';
    return $html;
}

function woo_scripts_styles() {
    $enable_lightbox = get_option( 'woocommerce_enable_lightbox' );   ?>
    <style>
    .play-overlay{
        background: url('<?php rtrim(plugin_dir_path(__FILE__),'/') . './assets/play.png';?>') center center no-repeat;
        height: 61px;
        margin: -80px -2px 0 0;
        position: relative;
        z-index: 10;
    }
    </style>
    <script>
        jQuery(document).ready(function(){
            var enable_lightbox = '<?php echo $enable_lightbox;?>';
            if(enable_lightbox == 'no'){
                jQuery('.thumbnails .zoom').click(function(e){
                    e.preventDefault();
                    var photo_fullsize =  jQuery(this).attr('href');
                    if (jQuery('.images iframe').length > 0)
                    {
                        if(photo_fullsize.indexOf('youtube') > (-1) || photo_fullsize.indexOf('vimeo') > (-1)){
                            jQuery('.images iframe:first').attr('src', photo_fullsize);
                        } else {
                            jQuery('.images iframe:first').replaceWith('<img src="'+photo_fullsize+'" alt="Placeholder">');
                        }
                    } else {
                       if(photo_fullsize.indexOf('youtube') > (-1) || photo_fullsize.indexOf('vimeo') > (-1)){
                            jQuery('.images img:first').replaceWith( '<iframe src="'+photo_fullsize+'" frameborder="0" allowfullscreen></iframe>' );
                        } else {
                            jQuery('.images img:first').attr('src', photo_fullsize);
                        }
                    }
                });
            }
        });
    </script>
<?php }

function woo_display_embed_video( $html ) {
    // Get WooCommerce Global
    global $woocommerce;
    global $product;

    $attachment_ids = $product->get_gallery_attachment_ids();
    $enable_lightbox = get_option( 'woocommerce_enable_lightbox' );

    if ( $attachment_ids ) {
        $newhtml = "";
        $loop       = 0;
        $columns    = apply_filters( 'woocommerce_product_thumbnails_columns', 3 );
        $newhtml = '<div class="thumbnails columns-' . $columns.'">';
        foreach ( $attachment_ids as $attachment_id ) {
            $classes = array( 'zoom' );
            if ( $loop == 0 || $loop % $columns == 0 )
                $classes[] = 'first';
            if ( ( $loop + 1 ) % $columns == 0 )
               $classes[] = 'last';
            $image_link = wp_get_attachment_url( $attachment_id );
            if ( ! $image_link )
                continue;
            $video_link = '';
            $image       = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ) );
            $image_class = esc_attr( implode( ' ', $classes ) );
            $image_title = esc_attr( get_the_title( $attachment_id ) );
            $videolink_id = get_post_meta( $attachment_id, 'videolink_id', true );
            $video_site = get_post_meta( $attachment_id, 'video_site', true );
            if(!empty($videolink_id) && !empty($video_site)){
                switch ($video_site) {
                    case 'youtube':
                        $video_link = ($enable_lightbox == 'yes') ? 'https://www.youtube.com/watch?v='.$videolink_id : 'https://www.youtube.com/embed/'.$videolink_id;
                        break;
                    case 'vimeo':
                        $video_link = ($enable_lightbox == 'yes') ? 'https://vimeo.com/'.$videolink_id : 'https://player.vimeo.com/video/'.$videolink_id;
                        break;
                }
            }
            $video = '';
            if(!empty($video_link)){
                $video = '<div class="play-overlay"></div>';
            }
            $link = (empty($video_link)) ? $image_link : $video_link;
            $newhtml .= '<a href="'.$link.'" class="'. $image_class.'" title="'. $image_title.'" data-rel="prettyPhoto[product-gallery]">'.$image.$video.'</a>';
            $loop++;
        }
        $newhtml .= '</div>';
    }
    echo $newhtml;
}

?>
