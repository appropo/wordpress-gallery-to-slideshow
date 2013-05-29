WordPress Gallery to Slideshow
==============================
This plugin modifies WordPress´s default gallery (shortcode) function to use it as a slideshow.
Currently this plugin is using [Nivo Slider](http://dev7studios.com/nivo-slider/) as slideshow library.

Assumptions
-----------
- jQuery v1.7+ is enabled in your WordPress project

Getting started
---------------
1. Download the plugin and install it in your plugin directory
2. Activate the plugin in your WordPress admin area
3. Add the following code to your JavaScript; after the <code>wp_head()</code> section in your header
<pre><code>jQuery(window).load(function() {
        jQuery('#slider').nivoSlider();
    });</code></pre>
4. Test the slideshow by adding a gallery to one of your posts

Additional
----------
For customize your new slideshow, check out the [Nivo Slider documentation](http://dev7studios.com/nivo-slider/#/documentation)
