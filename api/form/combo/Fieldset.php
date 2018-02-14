<?php

namespace ChadoSearch\form\combo;

class Fieldset extends Filter {
  
  public $id;
  public $title;
  public $start_widget;
  public $end_widget;
  public $desc;
  public $collapased;
  public $clearboth;
  
  public function setForm (&$form, &$form_state) {
    $search_name = $this->search_name;
    $id = $this->id;
    $title = $this->title;
    $start_widget = $this->start_widget;
    $end_widget = $this->end_widget;
    $desc = $this->desc;
    $collapased = $this->collapased;
    
    $prefix = $form[$start_widget. "_label"]['#prefix'];
    if (!isset($form_state['values'][$id])) {
      if ($collapased) {
        $form_state['values'][$id] = "hide";
      } else {
        $form_state['values'][$id] = "show";
      }
    }
    
    $clearboth = "";
    if($this->clearboth) {
        $clearboth = " style=\"clear:both\"";
    }
    
    $append =
    "<fieldset id=\"chado_search-fieldset-$search_name-$id\" class=\"chado_search-fieldset\"$clearboth>";
    if ($title) {
      $append .= 
        "<legend  id=\"chado_search-fieldset-$search_name-$id-legend\" class=\"chado_search-fieldset-legend\" onClick=\"chado_search_fieldset_toggle(this);\">
            <a href=\"#\">$title</a>
         </legend>";
    }
    $append .= 
      "<div id=\"chado_search-fieldset-$search_name-$id-content\" class=\"chado_search-fieldset-content\">";
    if ($desc) {
      $append .= 
        "<div id=\"chado_search-fieldset-$search_name-$id-description\" class=\"chado_search-fieldset-description\">
            $desc
         </div>";
    }
    $append .= $prefix;
    
    // If the start_widget has a label, add the fieldset before the label.
    if (!$prefix) {
      $prefix = $form[$start_widget]['#prefix'];
      $form[$start_widget]['#prefix'] = $append;
    // Otherwise, add the fieldset before the start_widget
    } else {
      $form[$start_widget . "_label"]['#prefix'] = $append;
    }
    $suffix = $form[$end_widget]['#suffix'];
    $form[$end_widget]['#suffix'] = $suffix . "</div></fieldset>";
    $form [$id] = array(
      '#attributes' => array('id' => "chado_search-fieldset-$search_name-$id-status-hidden"),
      '#type' => 'hidden',
      '#default_value' => key_exists('values', $form_state) ? $form_state['values'][$id] : NULL,
    );
    $form [$id]['#attributes']['id'] = "chado_search-fieldset-$search_name-$id-status-hidden";
    $form [$id]['#attributes']['class'][] ='chado_search-fieldset-status-hidden';
  }
}