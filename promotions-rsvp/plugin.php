<?php
/*
Plugin Name: Promotions: RSVP for Altria
Plugin URI: http://bozuko.com
Description: Altria RSVP Plugin
Version: 1.0.0
Author: Bozuko
Author URI: http://bozuko.com
License: Proprietary
*/

add_action('promotions/plugins/load', function()
{
  define('PROMOTIONS_RSVP_DIR', dirname(__FILE__));
  define('PROMOTIONS_RSVP_URL', plugins_url('/', __FILE__));
  
  Snap_Loader::register( 'PromotionsRSVP', PROMOTIONS_RSVP_DIR . '/lib' );
  Snap::inst('PromotionsRSVP_Plugin');
}, 100);