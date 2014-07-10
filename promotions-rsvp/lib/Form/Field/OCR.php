<?php
/**
 * @field.name                        ocr
 * @field.label                       OCR
 */
class PromotionsRSVP_Form_Field_OCR extends Snap_Wordpress_Form2_Field_Abstract
{
  public function set_value( $value )
  {
    if( is_array( $value ) ){
      $value = implode( '', $value );
    }
    parent::set_value( $value );
  }
  
  public function get_html()
  {
    $value = $this->get_value();
    $children = array(array(
      'tag'         => 'input',
      'attributes'  => array(
        'type'        => 'hidden',
        'value'       => $value,
        'name'        => $this->get_name()
      )
    ));
    for( $i=0; $i<4; $i++){
      $sub_value = '';
      if( $value ) $sub_value = substr( $value, $i*4, 4);
      $children[] = array(
        'tag'         => 'input',
        'attributes'  => array(
          'type'        => 'text',
          'class'       => 'form-control',
          'name'        => '_'.$this->get_name().'['.$i.']',
          'value'       => $sub_value,
          'id'          => $this->get_id().(!$i?'':'_'.$i),
          'disabled'    => 'disabled'
        )
      );
    }
    
    $config = array(
      'tag'         => 'div',
      'attributes'  => array(
        'class'       => 'ocr-control'
      ),
      'children'    => $children
    );
    $html = Snap_Util_Html::tag( $config );
    return $this->apply_filters('html', $html);
  }
    
}
