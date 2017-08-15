<?php

namespace ChadoSearch\form\combo;

class DynamicTextFields extends Filter {
  
  public $target_ids;
  public $callback;
  public $reset_on_change_id;
  
  public function setForm (&$form, &$form_state) {
    $search_name = $this->search_name;
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
    $reset_on_change_id = $this->reset_on_change_id;
    
    if (function_exists($callback)) {
      $updates = array();
      foreach ($target_ids AS $id) {
        $value = $callback($selected_value, $id);
        $wrapper = "chado_search-$id-wrapper";
        $updates[$id] = array(
          'value' => $value,
          'wrapper' => $wrapper
        );
        // Add a style to make sure space won't get added to the textfield
        $pre = str_replace('class=', 'style="margin-left:0px" class=', $form[$id]['#prefix']);
        $form[$id]['#prefix'] = $pre ."<div id=$wrapper class=\"chado_search-dynamic-text-fields-wrapper\">";
        $form[$id]['#suffix'] = "</div>" . $form[$id]['#suffix'];
      }
      $form[$select_id]['#attribute'] = array ('update' => $updates);
      
      // Add Ajax to reset values on change of another element
      if ($reset_on_change_id) {
        $reset = $form[$reset_on_change_id]['#attribute']['update'];
        foreach ($target_ids AS $id) {
          $form_state['values'][$id] = NULL;
          if (!is_array($reset)) {
            $reset = array('value' => '', $reset => array('wrapper' => "chado_search-filter-$search_name-$reset-field"));
          }
          $reset[$id] = array('value' => '', 'wrapper' => "chado_search-$id-wrapper");
        }
        $form[$reset_on_change_id]['#attribute'] = array ('update' => $reset);
      }
    }
    else {
      drupal_set_message('Fatal Error: DynamicTextFields ajax function not implemented', 'error');
    }
  }  
}
