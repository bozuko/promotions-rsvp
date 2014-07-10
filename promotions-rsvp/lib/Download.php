<?php

class PromotionsRSVP_Download extends Snap_Wordpress_Plugin
{
  protected $promotion = false;
  protected $delimiter = ',';
  protected $string_enclosure = '"';
  protected $fp = null;
  protected $chunk_size = 500;
  protected $is_download = false;
  
  public function __construct()
  {
    parent::__construct();
    $this->init();
  }
  protected function init()
  {
    if( $_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['_action']) ) return;
    if( !wp_verify_nonce($_POST['_action'], 'download_promotion_entries') ) return;
    
    if( !current_user_can('download_promotion_entries') ) {
      wp_die("I'm sorry Dave, I'm afraid I can't do that.");
    }
    
    $promotion = get_post( $_POST['promotion'] );
    if( !$promotion || $promotion->post_type != 'promotion' ){
      $this->error = 'Invalid promotion';
      return;
    }
    
    $this->promotion = $promotion;
    $now = Snap::inst('Promotions_Functions')->now();
    
    global $post;
    $post = $promotion;
    setup_postdata( $post );
    
    $this->is_download = true;
  }
  
  /**
   * @wp.action     promotions/register_fields
   * @wp.priority   999
   */
  public function download()
  {
    if( !$this->is_download ) return;
    
    if( !apply_filters('promotions/download/continue', true, $this->promotion->ID ) )
      return;
    
    
    $now = Snap::inst('Promotions_Functions')->now();
    
    // lets set the headers
    header('Content-Type: text/plain');
    $filename = $this->promotion->post_name.'-'.$now->format('Y-m-d').'.txt';
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    
    $this->fp = fopen('php://output', $w);
    $this->do_download();
    fclose( $this->fp );
    exit;
  }
  
  protected function do_download()
  {
    // get the fields...
    
    // do this in chunks...
    $i = 0;
    global $wpdb;
    $sql = <<<SQL
SELECT `entry`.`ID` FROM {$wpdb->posts} `entry`

  LEFT JOIN {$wpdb->posts} `reg`
    ON `entry`.`post_parent` = `reg`.`ID`
  
  WHERE `entry`.`post_type` = 'entry'
    AND `reg`.`post_parent` = %d
  
    AND `entry`.`ID` > %d 
  ORDER BY `entry`.`ID` ASC
  LIMIT %d
SQL;

    $this->current_id = 0;
    
    $wpdb->show_errors( true );
    
    ini_set('memory_limit', '256M');
    set_time_limit(0);
    
    $this->export = array(
      'entry.post_title'   => 16,
      'entry.post_date'    => 20,
      'entry.meta.address1'     => 30,
      'entry.meta.address2'     => 30,
      'entry.meta.city'         => 30,
      'entry.meta.state'        => 20,
      'entry.meta.zipcode'      => 5,
      'entry.meta.phone'        => 10,
      'entry.meta.address_confirmation' => 1,
      'entry.meta.delivery_method'  => 1
    );
    
    global $wp_actions;
    
    while( ($ids = $wpdb->get_col($wpdb->prepare($sql, $this->promotion->ID, $this->current_id, $this->chunk_size) )) ){
      foreach( $ids as $id){
        $this->do_row( $id );
        $this->current_id = $id;
      }
      wp_cache_flush();
      $wpdb->queries = array();
      $wp_actions = array();
    }
  }
  
  
  protected function do_row( $id )
  {
    $entry = get_post( $id );
    $registration = get_post( $entry->post_parent );
    $values = array();
    foreach( $this->export as $key => $length ){
      $parts = explode('.', $key);
      $object = array_shift( $parts );
      $obj = $$object;
      if( count( $parts ) == 1 ){
        $property = array_shift($parts);
        $value = isset( $obj->$property ) ? $obj->$property : '';
        
        if( $property == 'post_date' ){
          $value = preg_replace('/[^\d]/', '', $value );
          $value = substr( str_pad( $value, $length, '0' ), 0, $length );
        }
        
        else {
          $value = substr( str_pad( $value, $length ), 0, $length );
        }
        $values[] = $value;
      }
      else {
        if( $parts[0] == 'meta' ){
          $property = $parts[1];
          $value = get_post_meta( $obj->ID, $property, true);
          $value = apply_filters('promotions/registration/meta/fetch_from_db', $value, $property, $obj->ID);
          
          // special cases:
          switch( $property ){
            case 'phone':
              $value = preg_replace('/[^\d]/', '', $value);
              break;
            
            
          }
          
          $value = substr( str_pad( $value, $length ), 0, $length );
          $values[] = $value;
        }
      }
    }
    
    fwrite( $this->fp, implode('',$values)."\n" );
  }
}
