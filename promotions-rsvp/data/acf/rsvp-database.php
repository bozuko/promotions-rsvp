<?php
if(function_exists("register_field_group"))
{
	register_field_group(array (
    'id' => 'acf_rsvp-database',
    'title' => 'RSVP Database',
    'fields' => array (
      array (
        'key' => 'field_538fe0aba4512',
        'label' => 'RSVP Database Message',
        'name' => '',
        'type' => 'message',
        'message' => '',
      ),
      array (
        'key' => 'field_538fdde02b43d',
        'label' => 'Database File Format',
        'name' => 'rsvp_database_file_format',
        'type' => 'textarea',
        'default_value' => '',
        'placeholder' => '',
        'maxlength' => '',
        'rows' => '',
        'formatting' => 'br',
      ),
      array (
        'key' => 'field_538fde042b43e',
        'label' => 'Database File',
        'name' => 'rsvp_database_file',
        'type' => 'file',
        'save_format' => 'object',
        'library' => 'all',
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
    