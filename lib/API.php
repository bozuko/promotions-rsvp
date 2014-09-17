<?php

class PromotionsRSVP_API
{
  /**
   * @methods     ["post"]
   */
  public function rsvp_certify( $params = array() )
  {
    $form = Snap::inst('Promotions_Functions')->get_registration_form();
    $valid = $form->validate( $params, array(
      'ocr', 
      'certified_smoker',
      'birthdate',
      'agree_to_terms',
      'captcha'
    ));
    
    if( !$valid ) return array(
      'success'     => $valid,
      'errors'      => array(
        'form'        => $form->get_form_errors(),
        'fields'      => $form->get_field_errors()
      )
    );
    
    $ocr = $form->get_field('ocr')->get_value();
    
    // lets insert this
    $reg_id = wp_insert_post(array(
      'post_title'    => $ocr,
      'post_name'     => $ocr,
      'post_type'     => 'registration',
      'post_parent'   => get_the_ID()
    ));
    
    $entry_id = wp_insert_post(array(
      'post_date'     => Snap::inst('Promotions_Functions')->now()->format('Y-m-d H:i:s'),
      'post_title'    => $ocr,
      'post_name'     => $ocr,
      'post_type'     => 'entry',
      'post_content'  => 'confirmed',
      'post_parent'   => $reg_id
    ));
    
    global $wpdb;
    
    // check to see if we reached the cap.
    $limit = get_field('rsvp_limit');
    
    $sql = <<<SQL
SELECT COUNT(*) FROM {$wpdb->posts} r
  JOIN {$wpdb->posts} e ON e.post_parent = r.ID
  WHERE r.post_parent = %d
    AND e.post_type = 'entry'
    AND r.post_type = 'registration'
    AND e.ID <= %d
SQL;
    
    
    /*
    $now = Snap::inst('Promotions_Functions')->now();
    $now->modify('-20 minutes');
    */
    
    $count = $wpdb->get_var(
      $wpdb->prepare( $sql, get_the_ID(), $entry_id )
    );
    
    $limit_reached = $count > $limit;
    $_SESSION['entry_id'] = $entry_id;
    $_SESSION['limit_reached'] = $limit_reached;
    
    if( !$limit_reached ){
      update_post_meta($entry_id, 'address_confirmation', 1);
      update_post_meta($entry_id, 'delivery_method', 1);
    }
    
    return array(
      'success'         => true,
      'registration_id' => $reg_id,
      'entry_id'        => $entry_id,
      'limit_reached'   => $limit_reached,
      'limit'           => $limit,
      'number'          => $count,
      'information'     => Snap::inst('PromotionsRSVP_Functions')->ocr_lookup( $ocr )
    );
  }
  
  /**
   * @methods     ["post"]
   */
  public function rsvp_confirm( $params = array() )
  {
    $entry_id = @$params['entry_id'];
    if( !$entry_id ) return array(
      'success'         => false,
      'error'           => 'Invalid Entry ID'
    );
    
    $entry = get_post( $entry_id );
    if( $entry->post_type != 'entry' ) return array(
      'success'         => false,
      'error'           => 'Invalid Entry'
    );
    
    // check to make sure it matches the provided ocr
    $ocr = @$params['ocr'];
    if( !$ocr ) return array(
      'success'         => false,
      'error'           => 'No OCR provided'
    );
    
    if( $ocr != $entry->post_title ) return array(
      'success'         => false,
      'error'           => 'OCR does not match'
    );
    
    $form = Snap::inst('Promotions_Functions')->get_registration_form();
    $form->set_data($params);
    
    if( isset( $params['limit_reached_contact'] ) ){
      update_post_meta( $entry_id, 'limit_reached_contact', $form->get_field('limit_reached_contact')->get_value() );
      update_post_meta( $entry_id, 'phone', $form->get_field('phone')->get_value_formatted() );
      update_post_meta( $entry_id, 'address_confirmation', '');
      update_post_meta( $entry_id, 'delivery_method', '');
    }
    else {
      
      foreach( $params as $key => $val ){
        $field = $form->get_field($key);
        
        if( $field ){
          $value = $field->get_value_formatted();
          if( @$params['address_confirmation'] != 2 ){
            if( in_array($key, array('address1','address2','city','state','zipcode','phone') ) ){
              $value = '';
            }
          }
          update_post_meta( $entry_id, $key, $value );
        }
      }
      
      if( @$params['delivery_method'] == 2 ){
        update_post_meta( $entry_id, 'address_confirmation', '' );
      }
    }
    
    unset( $_SESSION['entry_id'] );
    
    return array(
      'success'         => true
    );
    
  }
  
  /**
   * @methods     ["post"]
   */
  public function rsvp_forget( $params = array() )
  {
    $entry_id = @$params['entry_id'];
    if( !$entry_id ) return array(
      'success'         => false,
      'error'           => 'Invalid Entry ID'
    );
    
    $entry = get_post( $entry_id );
    if( $entry->post_type != 'entry' ) return array(
      'success'         => false,
      'error'           => 'Invalid Entry'
    );
    
    // check to make sure it matches the provided ocr
    $ocr = @$params['ocr'];
    if( !$ocr ) return array(
      'success'         => false,
      'error'           => 'No OCR provided'
    );
    
    if( $ocr != $entry->post_title ) return array(
      'success'         => false,
      'error'           => 'OCR does not match'
    );
    
    if( @$_SESSION['entry_id'] != $entry->ID ) return array(
      'success'         => false,
      'error'           => 'Session does not exist'
    );
    
    wp_delete_post( $entry->post_parent, true);
    wp_delete_post( $entry->ID, true );
    unset( $_SESSION['entry_id'] );
    return array(
      'success' => true
    );
    
  }
  
}
