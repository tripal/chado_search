<?php

namespace ChadoSearch\form\combo;

use ChadoSearch\Set;

class LabeledFilter extends Filter {
  
  public $title;
  public $required;
  public $size;
  public $label_width;
  
  public function setForm(&$form, &$form_state) {
    $search_name = $this->search_name;
    $id = $this->id;
    $id_label = $id . "_label";
    $width = '';
    if ($this->label_width) {
      $width = "style=\"width:" . $this->label_width ."px\"";
    }
    $this->csform->addMarkup(Set::markup()->id($id_label)->text($this->title));
    $this->csform->addTextfield(Set::textField()->id($id)->required($this->required)->size($this->size ? $this->size : 20));
    $form[$id_label]['#prefix'] = 
      "<div id=\"chado_search-filter-$search_name-$id\" class=\"chado_search-filter chado_search-widget\">
         <div id=\"chado_search-filter-$search_name-$id-label\" class=\"chado_search-filter-label form-item\" $width>";
    $form[$id_label]['#suffix'] = 
        "</div>";
    $form[$id]['#prefix'] = 
        "<div id=\"chado_search-filter-$search_name-$id-field\" class=\"chado_search-filter-field\">";
    $form[$id]['#suffix'] = 
      "  </div>
        </div>";
  }
  
}
