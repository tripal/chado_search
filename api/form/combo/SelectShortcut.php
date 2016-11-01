<?php

namespace ChadoSearch\form\combo;

class SelectShortcut extends Filter {
  
  public $selectbox_id;
  public $value;
  public $pretext;
  public $postext;
  
  public function setForm (&$form, &$form_state) {
    $search_name = $this->search_name;
    $id = $this->id;
    $selectbox_id = $this->selectbox_id;
    $value =$this->value;
    $pretext = $this->pretext;
    $postext = $this->postext;
    $suffix = $form[$selectbox_id]['#suffix'];
    $suffix .=
      "<div id=\"chado_search-filter-$search_name-$id\" class=\"chado_search-select-shortcut chado_search-widget form-item\">
         $pretext
         <a href=\"#\" onClick=\"$('#chado_search-id-$selectbox_id').val('$value');$('#chado_search-id-$selectbox_id').change();\">
           $value
         </a>
         $postext
    </div>";
    $form[$selectbox_id]['#suffix'] = $suffix;
  }
  
}