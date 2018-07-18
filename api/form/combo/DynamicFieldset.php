<?php

namespace ChadoSearch\form\combo;

class DynamicFieldset extends Filter {
  
  public $depend_on_id;
  public $callback;
  public $title;
  public $description;
  public $collapsible;
  public $collapsed;
  public $width;
  public $display;
  
  public function setForm (&$form, &$form_state) {
    $search_name = $this->search_name;
    $id = $this->id;
    $depend_on_id = $this->depend_on_id;
    $title = $this->title;
    $desc = $this->description;
    $collapsible = $this->collapsible;
    $collapsed = $this->collapsed;
    $style = '';   
    if ($this->width) {
      if (preg_match('/\d+%$/', $this->width)) {
        $style =  "style=\"width:" . $this->width;
        if ($this->display) {
          $style .= ';display:' . $this->display;
        }
        $style .= "\"";
      }
      else {
        $style =  "style=\"width:" . $this->width . "px";
        if ($this->display) {
          $style .= ';display:' . $this->display;
        }
        $style .= "\"";
      }
    }
    $js = '';
    if ($this->display) {
      $js = 
        '<script type=\"text/javascript\">
          (function ($) {
             $(document).ready(
               function() {
                 $(\'#chado_search-filter-' . "$search_name-$id" . '-field\').css("display","' . $this->display . '");
               }
             )
          })(jQuery)</script>';
    }
    
    // Add Ajax to the depending element
    $selected = isset($form_state['values'][$depend_on_id]) ? $form_state['values'][$depend_on_id] : 0;
    $form[$depend_on_id]['#ajax'] = array(
      'path' => 'chado_search_ajax_callback',
      'wrapper' => "chado_search-filter-$search_name-$id-field",
      'effect' => 'fade'
    );
    if(isset($form[$depend_on_id]['#attribute']['update'])) {
      $updates = $form[$depend_on_id]['#attribute']['update'];
      if (!is_array($updates)) {
        $updates = array($updates => array('wrapper' => "chado_search-filter-$search_name-$updates-field"));
      }
      $updates[$id] = array('wrapper' => "chado_search-filter-$search_name-$id-field");
      $form[$depend_on_id]['#attribute'] = array ('update' => $updates);
    }
    else {
      $form[$depend_on_id]['#attribute'] = array ('update' => $id);
    }
    
    $callback = $this->callback;
    if (function_exists($callback)) {
      $selected_value = is_array($selected) ? array_shift($selected) : $selected;
      $markup = $callback($selected_value, $form, $form_state);
      
      $form [$id] = array(
        '#type' => 'fieldset',
        '#title' => $title,
        '#description' => $desc,
        '#collapsible' => $collapsible,
        '#collapsed' => $collapsed,
        '#prefix' => "$js<div id=\"chado_search-filter-$search_name-$id-field\" class=\"chado_search-filter chado_search-widget form-item\" $style>",
        '#suffix' => "</div>"
      );
    }
    else {
      drupal_set_message('Fatal Error: DynamicFieldset ajax function not implemented', 'error');
    }
  }
  
}