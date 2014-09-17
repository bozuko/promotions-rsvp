<?php
if(function_exists("register_field_group"))
{
	register_field_group(array (
    'id' => 'acf_rsvp-settings',
    'title' => 'RSVP Settings',
    'fields' => array (
      array (
        'key' => 'field_539b66cd69bba',
        'label' => 'RSVP Limit',
        'name' => 'rsvp_limit',
        'type' => 'number',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'min' => '',
        'max' => '',
        'step' => '',
      ),
      array (
        'key' => 'field_53a06a270b126',
        'label' => 'Standby Limit',
        'name' => 'rsvp_standby_limit',
        'type' => 'number',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'min' => '',
        'max' => '',
        'step' => '',
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'promotion_feature',
          'operator' => '==',
          'value' => 'rsvp',
          'order_no' => 0,
          'group_no' => 0,
        ),
        array (
          'param' => 'promotion_tab',
          'operator' => '==',
          'value' => 'rsvp',
          'order_no' => 1,
          'group_no' => 0,
        ),
      ),
    ),
    'options' => array (
      'position' => 'normal',
      'layout' => 'default',
      'hide_on_screen' => array (
      ),
    ),
    'menu_order' => 0,
  ));
}
    