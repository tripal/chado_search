<?php

namespace ChadoSearch\form\combo;

use ChadoSearch\Set;

class DynamicSelectFilter extends Filter {
  
  public $title;
  public $depend_on_id;
  public $callback;
  public $size;
  
  public function setForm (&$form, &$form_state) {
    $search_name = $this->search_name;
    $id = $this->id;
/*     $id_prefix = $id . '_prefix';
    $id_suffix = $id . '_suffix'; */
    $id_label = $id . '_label';
    $title = $this->title;
    $depend_on_id = $this->depend_on_id;
    $width = '';
    if ($this->label_width) {
      $width = "style=\"width:" . $this->label_width ."px\"";
    }
    $size = $this->size;

    // Add Ajax to the depending element
    $selected = isset($form_state['values'][$depend_on_id]) ? $form_state['values'][$depend_on_id] : 0;
    $form[$depend_on_id]['#ajax'] = array(
      'callback' => 'chado_search_ajax_form_update',
      'wrapper' => "chado_search-filter-$search_name-$id-field",
      'effect' => 'fade'
    );
    $form[$depend_on_id]['#attribute'] = array ('update' => $id);

    // Wrap the widget around a <div> tag
/*     $form[$id_prefix] = array (
      '#markup' => "<div id=\"chado_search-filter-$search_name-$id\" class=\"chado_search-filter chado_search-widget\">"
    ); */
    // Add Label
    $this->csform->addMarkup(Set::markup()->id($id_label)->text($title));
    $form[$id_label]['#prefix'] =
      "<div id=\"chado_search-filter-$search_name-$id-label\" class=\"chado_search-filter-label form-item\" $width>";
    $form[$id_label]['#suffix'] =
      "</div>";
    // Add Select
    $callback = $this->callback;
    if (function_exists($callback)) {
      $selected_value = is_array($selected) ? array_shift($selected) : $selected;
      $this->csform->addSelect(Set::select()->id($id)->options($callback($selected_value))->size($size));
      $form[$id]['#prefix'] =
        "<div id=\"chado_search-filter-$search_name-$id-field\" class=\"chado_search-filter-field chado_search-widget\">";
      $form[$id]['#suffix'] =
        "</div>";
/*       $form[$id_suffix] = array (
        '#markup' => "</div>"
      ); */
    }
    else {
      drupal_set_message('Fatal Error: DynamicSelectFilter ajax function not implemented', 'error');
    }
  }
}
