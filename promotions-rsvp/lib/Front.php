<?php

class PromotionsRSVP_Front extends Snap_Wordpress_Plugin
{
  
  public function __construct()
  {
    parent::__construct();
    if( !session_id() ) session_start();
  }
  
  protected $ocr = false;
  protected $customer = false;
  
  public function has_customer()
  {
    return $this->customer ? true : false;
  }
  
  public function get_customer()
  {
    return $this->customer;
  }
  
  public function get_ocr()
  {
    return $this->ocr;
  }
  
  /**
   * @wp.action         promotions/process
   */
  public function check_for_ocr()
  {
    if( !Snap::inst('Promotions_Functions')->is_enabled('rsvp') )
      return;
    $ocr = get_query_var('ocr');
    if( !$ocr ) return;
    
    $this->ocr = $ocr;
    $this->customer = Snap::inst('PromotionsRSVP_Functions')->ocr_lookup( $ocr );
    $form = Snap::inst('Promotions_PostType_Promotion')->get_registration_form();
    $field = $form->get_field('ocr');
    if( $field ) $field->set_value( $ocr );
  }
  
  /**
   * @wp.filter         promotions/content/template
   * @wp.priority       100
   */
  public function rsvp_template( $template )
  {
    if( !Snap::inst('Promotions_Functions')->is_enabled('rsvp') ){
      return $template;
    }
    
    // check to see if its after the end
    if( $template == 'afterend'  || $template == 'beforestart') return $template;
    
    $fn = Snap::inst('PromotionsRSVP_Functions');
    
    if( $this->ocr  && $fn->has_customer_entered( $this->ocr ) ){
      return 'rsvp';
    }
    
    if( $fn->total_limit_reached() && !@$_SESSION['entry_id'] ){
      return 'rsvp-limit-reached';
    }
    
    return 'rsvp';
  }
  
}