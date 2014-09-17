<?php

class PromotionsRSVP_Admin extends Snap_Wordpress_Plugin
{
  public function __construct()
  {
    parent::__construct();
    Snap::inst('PromotionsRSVP_Download');
  }
  
  /**
   * @wp.action         acf/save_post
   * @wp.priority       200
   */
  public function import_database( $post_id )
  {
    
    if( get_post_type( $post_id ) != 'promotion' ) return;
    if( !Snap::inst('Promotions_Functions')->is_enabled('rsvp', $post_id ) ) return;
    
    $db = get_field('rsvp_database_file', $post_id);
    if( !$db ) return;
    $id = $db['id'];
    
    // get the file
    $path = get_attached_file( $id );
    
    $format = get_field('rsvp_database_file_format', $post_id);
    
    // import!
    if( $format ){
      $this->reset( $post_id );
      $this->import( $path, $format, $post_id );
    }
    
    wp_delete_post( $id );
  }
  
  protected function reset( $parent_id )
  {
    global $wpdb;
    
    $sql = <<<SQL
DELETE FROM {$wpdb->postmeta} m
  JOIN {$wpdb->posts} p
    ON p.ID = m.post_id
  WHERE p.`post_type` = 'rsvp-customer'
    AND p.post_parent = %d
SQL;

    $wpdb->query( $wpdb->prepare( $sql, $parent_id ) );
    
    
    $sql = <<<SQL
DELETE FROM {$wpdb->posts}
  WHERE `post_type` = 'rsvp-customer'
    AND `post_parent` = %d
SQL;
    $wpdb->query( $wpdb->prepare( $sql, $parent_id ) );
  }
  
  protected function import( $path, $format, $parent_id )
  {
    
    set_time_limit( 0 );
    ini_set('memory_limit', '256M');
    
    $columns = $this->parse_format( $format );
    $fp = fopen( $path, 'r');
    $count = 0;
    while( false !== ($line = fgets($fp)) ){
      $data = array();
      foreach( $columns as $col ){
        $data[$col['name']] = substr($line, $col['start'], $col['length']);
      }
      
      // check for OCR
      $title = "OCR ".$data['ocr'];
      $post_id = wp_insert_post(array(
        'post_title'      => $title,
        'post_type'       => 'rsvp-customer',
        'post_parent'     => $parent_id,
        'post_name'       => 'ocr-'.$data['ocr']
      ));
      
      foreach( $data as $key => $value )
        update_post_meta( $post_id, $key, trim($value), true);
        
      if( $count++ % 1000 === 999 ){
        wp_cache_flush();
        $wpdb->queries = array();
        $wp_actions = array();
      }
    }
  }
  
  protected function parse_format( $format )
  {
    $columns = array();
    $start = 0;
    foreach( preg_split("/[,\n]/", $format) as $col ){
      list( $name, $length ) = explode(':', $col);
      $columns[] = array(
        'name'    => $name,
        'length'  => $length,
        'start'   => $start
      );
      $start += $length;
    }
    return $columns;
  }
  
  /**
   * @wp.filter     acf/create_field/type=message
   */
  public function rsvp_database_message( $val )
  {
    if( 'RSVP Database Message' !== $val['label'] ) return;
    
    global $wpdb;
    
    $sql = <<<SQL
SELECT COUNT(*) FROM {$wpdb->posts}
  WHERE post_parent = %d
    AND post_type = 'rsvp-customer'
SQL;
    
    $promotion_id = get_the_ID();
    $count = $wpdb->get_var( $wpdb->prepare( $sql, $promotion_id ) );
    
    ?>
    <?= (int)$count ?> customer records in the database.
    <?php
    return $val;
  }
}
