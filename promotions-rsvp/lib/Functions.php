<?php

class PromotionsRSVP_Functions
{
  public function ocr_lookup( $ocr )
  {
    global $wpdb;
    $sql = <<<SQL
SELECT * FROM {$wpdb->posts}
  WHERE post_type = %s
    AND post_parent = %d
    AND post_name = %s
SQL;
    $results = $wpdb->get_results(
      $wpdb->prepare( $sql, 'rsvp-customer', get_the_ID(), "ocr-$ocr" )
    );
    if( !$results || !count($results) ) return false;
    $result = $results[0];
    $meta = get_post_custom( $result->ID );
    foreach( $meta as $key => $values ){
      $result->$key = $values[0];
    }
    return $result;
  }
  
  public function has_customer_entered( $ocr )
  {
    global $wpdb;
    $sql = <<<SQL
SELECT entry.ID FROM {$wpdb->posts} entry
  JOIN {$wpdb->posts} reg ON entry.post_parent = reg.ID
  WHERE reg.post_type = %s
    AND entry.post_type = %s
    AND reg.post_parent = %d
    AND reg.post_title = %s
SQL;
    $id = $wpdb->get_var(
      $wpdb->prepare( $sql, 'registration', 'entry', get_the_ID(), "$ocr" )
    );
    if( !$id ) return false;
    return $id;
  }
  
  public function total_limit_reached()
  {
    global $wpdb;
    $sql = <<<SQL
SELECT COUNT(*) FROM {$wpdb->posts} entry
  JOIN {$wpdb->posts} reg ON entry.post_parent = reg.ID
  WHERE reg.post_type = %s
    AND entry.post_type = %s
    AND reg.post_parent = %d
SQL;
    $count = $wpdb->get_var(
      $wpdb->prepare( $sql, 'registration', 'entry', get_the_ID() )
    );
    
    $limit = get_field('rsvp_limit');
    $standby = get_field('rsvp_standby_limit');
    
    return $count >= $limit+$standby;
  }
}
