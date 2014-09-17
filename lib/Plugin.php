<?php

class PromotionsRSVP_Plugin extends Promotions_Plugin_Base
{
  public function init()
  {
    $this->register_field_groups(
      'rsvp-database',
      'rsvp-settings'
    );
    
    // register post types
    Snap::inst('PromotionsRSVP_PostType_Customer');
    
    if( is_admin() ){
      Snap::inst('PromotionsRSVP_Admin');
    }
    else {
      Snap::inst('PromotionsRSVP_Front');
    }
    
    // add our end point
    add_rewrite_endpoint('ocr', EP_PERMALINK | EP_ROOT );
  }
  
  /**
   * @wp.action         promotions/api/register
   */
  public function register_methods( $api )
  {
    $api->add('PromotionsRSVP_API');
  }
  
  /**
   * @wp.action         snap/form/field/register
   */
  public function register_field( $form )
  {
    $form->register('PromotionsRSVP_Form_Field_OCR');
  }
  /**
   * @wp.action         snap/form/validator/field/register
   */
  public function register_validator( $form )
  {
    $form->register('PromotionsRSVP_Form_Validator_Field_YearOfBirth');
  }
  
  /**
   * @wp.filter       promotions/features
   */
  public function add_feature( $features )
  {
    $features['rsvp'] = 'RSVP';
    return $features;
  }
  
  /**
   * @wp.filter     promotions/tabs/promotion/register
   * @wp.priority   10
   */
  public function register_tab( $tabs )
  {
    $tabs['rsvp'] = 'RSVP';
    return $tabs;
  }
  
  /**
   * @wp.filter     promotions/tabs/promotion/display
   * @wp.priority   10
   */
  public function display_tabs( $tabs, $post )
  {
    if( Snap::inst('Promotions_Functions')->is_enabled('rsvp', $post->ID) )
      return $tabs;
    unset( $tabs['rsvp'] );
    return $tabs;
  }
  
  /**
   * @wp.filter
   */
  public function upload_mimes( $mimes = array() )
  {
    // allow csv
    $mimes['csv'] = 'text/csv';
    return $mimes;
  }
  
  /**
   * @wp.action   promotions/api/result?method=rsvp_certify
   */
  public function increment_counters( $result )
  {
    if( $result && @$result['success'] ){
      Snap::inst('Promotions_Analytics')
        ->increment('registrations')
        ->increment('entries')
        ->increment('registration_entries');
    }
    return $result;
  }
  
  /**
   * @wp.action   promotions/api/result?method=rsvp_forget
   */
  public function decrement_counters( $result )
  {
    if( $result && @$result['success'] ){
      Snap::inst('Promotions_Analytics')
        ->decrement('registrations')
        ->decrement('entries')
        ->decrement('registration_entries');
    }
    return $result;
  }
  
}