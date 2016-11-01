<?php

namespace ChadoSearch\form\combo;

use ChadoSearch\Set;

class SelectOptionFilter extends Filter {
  
  public $title;
  public $options;
  public $required;
  public $multiple;
  public $nokeyconversion;
  public $label_width;
  public $size;
  
  public function setForm (&$form, &$form_state) {
    $search_name = $this->search_name;
    $id = $this->id;
    $id_label = $id . "_label";
    $options = $this->options;
    $title = $this->title;
    $multiple = $this->multiple;
    $width = '';
    if ($this->label_width) {
      $width = "style=\"width:" . $this->label_width ."px\"";
    }
    $size = $this->size;
    if (!$this->nokeyconversion) {
      foreach ($options as $k => $v) {
        $options [$v] = $v;
        unset ($options[$k]);
      }
    }
    if (!$this->required) {
      array_unshift($options, 'Any');
    }
    if ($title) {
      $this->csform->addMarkup(Set::markup()->id($id_label)->text($title));
      $form[$id_label]['#prefix'] = 
        "<div id=\"chado_search-filter-$search_name-$id\" class=\"chado_search-filter chado_search-widget\">
            <div id=\"chado_search-filter-$search_name-$id-label\" class=\"chado_search-filter-label form-item\" $width>";
      $form[$id_label]['#suffix'] = 
        "  </div>";
      $this->csform->addSelect(Set::select()->id($id)->options($options)->multiple($multiple)->size($size));
      $form[$id]['#prefix'] = 
        "  <div id=\"chado_search-filter-$search_name-$id-field\" class=\"chado_search-filter-field\">";
    } else {
      $this->csform->addSelect(Set::select()->id($id)->options($options)->multiple($multiple)->size($size));
      $form[$id]['#prefix'] = 
        "  <div id=\"chado_search-filter-$search_name-$id\" class=\"chado_search-filter chado_search-widget\">
              <div id=\"chado_search-filter-$search_name-$id-field\" class=\"chado_search-filter-field\">";
    }
    $form[$id]['#suffix'] = 
      "      </div>
            </div>";
  }
  
}