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
    $link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);
    
    $bla = wp_get_attachment_image_src($id, $size);
    $link = $bla[0];

    if ( trim($attachment->post_excerpt) ) {

      $images   .= '<img src="' . $link . '" data-thumb="' . $link . '" alt="" title="#caption_' . $i . '"/>';
      $captions .= '<div id="caption_' . $i . '" class="nivo-html-caption">' . wptexturize($attachment->post_excerpt) . '</div>';

    } else {

      $images   .= '<img src="' . $link . '" data-thumb="' . $link . '" alt=""/>';

    }

    $i++;
  };

  $output = '<div class="slider-wrapper"><div id="slider" class="nivoSlider">' . $images . '</div>' . $captions . '</div>';

  return $output;
}


/* ------------------------------------------------------------------------
 * Remove WP´s default gallery shortcode and add the re-written one instead
 * ------------------------------------------------------------------------ */
remove_shortcode('gallery');
add_shortcode('gallery', 'gallery_to_slideshow');


/* ------------------------------------------- 
 * Remove the now unnecessary gallery settings
 * ------------------------------------------- */

