<?php

namespace ChadoSearch\form\combo;

use ChadoSearch\Set;

class TextFilter extends Filter {

  public $title;
  public $required;
  public $label_width;
  public $size;
  
  public function setForm (&$form, &$form_state) {
    $search_name = $this->search_name;
    $id = $this->id;
    $id_label = $id . "_label";
    $id_op = $id . "_op";
    $width = '';
    if ($this->label_width) {
      $width = "style=\"width:" . $this->label_width ."px\"";
    }
    $options = array ('contains' => 'contains', 'exactly' => 'exactly', 'starts' => 'starts with', 'ends' => 'ends with');
    $this->csform->addMarkup(Set::markup()->id($id_label)->text($this->title));
    $this->csform->addSelect(Set::select()->id($id_op)->options($options));
    $this->csform->addTextfield(Set::textField()->id($id)->required($this->required)->size($this->size));
    $form[$id_label]['#prefix'] = 
      "<div id=\"chado_search-filter-$search_name-$id\" class=\"chado_search-filter chado_search-widget\">
         <div id=\"chado_search-filter-$search_name-$id-label\" class=\"chado_search-filter-label form-item\" $width>";
    $form[$id_label]['#suffix'] = 
      "  </div>";
    $form[$id_op]['#prefix'] = 
      "  <div  id=\"chado_search-filter-$search_name-$id-op\" class=\"chado_search-filter-op\">";
    $form[$id_op]['#suffix'] = 
      "  </div>";
    $form[$id]['#prefix'] = 
      "  <div id=\"chado_search-filter-$search_name-$id-field\" class=\"chado_search-filter-field\">";
    $form[$id]['#suffix'] = 
      "  </div>
        </div>";
  }
}