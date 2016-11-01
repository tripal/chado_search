<?php

namespace ChadoSearch\form\combo;

class DynamicTextFields extends Filter {
  
  public $target_ids;
  public $callback;
  
  public function setForm (&$form, &$form_state) {
    $target_ids = $this->target_ids;
    $select_id = $this->id;

    // Add Ajax to the depending element
    $selected = isset($form_state['values'][$select_id]) ? $form_state['values'][$select_id] : 0;
    $form[$select_id]['#ajax'] = array(
      'callback' => 'chado_search_ajax_form_update',
      'effect' => 'fade'
    );
    
    $callback = $this->callback;
    $selected_value = is_array($selected) ? array_shift($selected) : $selected;

    if (function_exists($callback)) {
      $updates = array();
      foreach ($target_ids AS $id) {
        $value = $callback($selected_value, $id);
        $wrapper = "chado_search-$id-wrapper";
        $updates[$id] = array(
          'value' => $value,
          'wrapper' => $wrapper
        );

        $form[$id]['#prefix'] = $form[$id]['#prefix'] ."<div id=$wrapper class=\"chado_search-dynamic-text-fields-wrapper\">";
        $form[$id]['#suffix'] = "</div>" . $form[$id]['#suffix'];
      }
      $form[$select_id]['#attribute'] = array ('update' => $updates);
    }
    else {
      drupal_set_message('Fatal Error: DynamicTextFields ajax function not implemented', 'error');
    }
  }  
}
