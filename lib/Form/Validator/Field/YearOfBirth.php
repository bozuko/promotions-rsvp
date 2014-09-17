<?php
/**
 * @validator_field.name    match_year
 * @validator_field.label   Match Birth Year
 *
 */
class PromotionsRSVP_Form_Validator_Field_YearOfBirth extends Snap_Wordpress_Form2_Validator_Field_Abstract
{
  const INVALID = 'Mismatch';
  
  public static $message_templates = array(
    self::INVALID  => 'The birth date did not match. Please try again'
  );
  
  public function validate()
  {
    $value = $this->get_field()->get_value();
    $ocr = $this->get_field()->get_form()->get_field('ocr')->get_value();
    $customer = Snap::inst('PromotionsRSVP_Functions')->ocr_lookup( $ocr );
    if( !$customer ){
      self::add_message( self::INVALID );
      return false;
    }
    if( @$customer->birth_year != $value['year'] ){
      
      self::add_message( self::INVALID );
      return false;
    }
    
    return true;
  }
}
