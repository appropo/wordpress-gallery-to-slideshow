<?php
/* -------------------------------------------------------------------------------
* Plugin Name: WordPress Gallery to Slideshow
* Plugin URI: https://github.com/timokujawa/wordpress-gallery-to-slideshow
* Description: Modify WordPress´s default gallery function to use it as slideshow
* Version: 1.0
* Author: Timo Kujawa
* Author https://github.com/timokujawa
* License: GPL2
* -------------------------------------------------------------------------------- */


if ( !defined('GTS_PLUGIN_URL') ) {
  define('GTS_PLUGIN_URL', plugins_url( '/', __FILE__));
};


/* --------------------------------------------
 * Enqueue styles and scripts for the slideshow
 * -------------------------------------------- */
/*function gts_enqueue_files() {
  wp_register_script( 'nivo-slider-script', GTS_PLUGIN_URL . 'scripts/jquery.nivo.slider.pack.js', array('jquery'), false, false );
  wp_enqueue_script( 'nivo-slider-script' );

  wp_register_style( 'nivo-slider-style', GTS_PLUGIN_URL . 'styles/nivo-slider.css', false, false, 'all' );
  wp_enqueue_style( 'nivo-slider-style' );
};
add_action( 'wp_enqueue_scripts', 'gts_enqueue_files' );*/


/* ---------------------------------------------------
 * Main function to re-write the shortcode for gallery
 * --------------------------------------------------- */
function gallery_to_slideshow($attr) {
  $post = get_post();

  static $instance = 0;
  $instance++;

  if (!empty($attr['ids'])) {
    if (empty($attr['orderby'])) {
      $attr['orderby'] = 'post__in';
    }
    $attr['include'] = $attr['ids'];
  }

  $output = apply_filters('post_gallery', '', $attr);

  if ($output != '') {
    return $output;
  }

  if (isset($attr['orderby'])) {
    $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
    if (!$attr['orderby']) {
      unset($attr['orderby']);
    }
  }

  extract(shortcode_atts(array(
    'order'      => 'ASC',
    'orderby'    => 'menu_order ID',
    'id'         => $post->ID,
    'itemtag'    => '',
    'icontag'    => '',
    'captiontag' => '',
    'columns'    => 3,
    'size'       => 'thumbnail',
    'include'    => '',
    'exclude'    => ''
  ), $attr));

  $id = intval($id);

  if ($order === 'RAND') {
    $orderby = 'none';
  }

  if (!empty($include)) {
    $_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));

    $attachments = array();
    foreach ($_attachments as $key => $val) {
      $attachments[$val->ID] = $_attachments[$key];
    }
  } elseif (!empty($exclude)) {
    $attachments = get_children(array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
  } else {
    $attachments = get_children(array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
  }

  if (empty($attachments)) {
    return '';
  }

  if (is_feed()) {
    $output = "\n";
    foreach ($attachments as $att_id => $attachment) {
      $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
    }
    return $output;
  }

  /* -----------------------------
   * Create the new slideshow code
   * ----------------------------- */
  $i        = 0;
  $images   = '';
  $captions = '';

  foreach ($attachments as $id => $attachment) {
    $attachment_array = wp_get_attachment_image_src($id, 'large');
    $link             = $attachment_array[0];
    $description      = ( empty($attachment->post_excerpt) ? " " : wptexturize($attachment->post_excerpt) );

    $images .= '<img src="' . $link . '" data-thumb="' . $link . '" alt="" title="' . $description . '"/>';      

    $i++;
  };

  $output = '<div class="slideshow-wrapper"><div class="slider-wrapper theme-kd"><div class="ribbon"></div><div id="slider" class="nivoSlider">' . $images . '</div></div></div>';

  return $output;
}


/* ------------------------------------------------------------------------
 * Remove WP´s default gallery shortcode and add the re-written one instead
 * ------------------------------------------------------------------------ */
remove_shortcode('gallery');
add_shortcode('gallery', 'gallery_to_slideshow');


/* --------------------------------------------------------- 
 * Remove the unnecessary gallery settings from gallery view
 * --------------------------------------------------------- */
function remove_gallery_settings() {
  echo '<style type="text/css">.gallery-settings *{ display:none; }</style>';
};
add_action( 'admin_print_styles', 'remove_gallery_settings' );  






