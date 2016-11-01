<?php

namespace ChadoSearch\form\combo;

class DynamicMarkup extends Filter {
  
  public $depend_on_id;
  public $callback;
  
  public function setForm (&$form, &$form_state) {
    $search_name = $this->search_name;
    $id = $this->id;
    $depend_on_id = $this->depend_on_id;
    
    // Add Ajax to the depending element
    $selected = isset($form_state['values'][$depend_on_id]) ? $form_state['values'][$depend_on_id] : 0;
    $form[$depend_on_id]['#ajax'] = array(
      'callback' => 'chado_search_ajax_form_update',
      'wrapper' => "chado_search-filter-$search_name-$id-field",
      'effect' => 'fade'
    );
    $form[$depend_on_id]['#attribute'] = array ('update' => $id);
    
    $callback = $this->callback;
    if (function_exists($callback)) {
      $selected_value = is_array($selected) ? array_shift($selected) : $selected;
      $markup = $callback($selected_value);
      
      $form [$id] = array(
        '#markup' => $markup,
        '#prefix' => "<div id=\"chado_search-filter-$search_name-$id-field\" class=\"chado_search-filter chado_search-widget form-item\">",
        '#suffix' => "</div>"
      );
    }
    else {
      drupal_set_message('Fatal Error: DynamicMarkup ajax function not implemented', 'error');
    }
  }
  
}