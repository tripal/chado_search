<?php

namespace ChadoSearch\form\control;

use ChadoSearch\form\control\Element;

class File extends Element {
  
  public $label;
  public $instruction;
  public $label_width;
  
  public function __construct($search_name, $id) {
    parent::__construct($search_name, $id, strtolower(chado_search_get_class($this)));
    $this->attributes = array(
      'enctype' => 'multipart/form-data',
      'class' => array('chado_search-filter-field')
    );
  }
  
  public function setForm(&$form, &$form_state) {
    $width = '';
    if ($this->label_width) {
      $width = "style=\"width:" . $this->label_width ."px\"";
    }
    $form[$this->id]['#prefix'] =
      "<div id=\"chado_search-file-$this->search_name-$this->id\" class=\"chado_search-file chado_search-widget\">
          <div id=\"chado_search-filter-$this->search_name-$this->id-label\" class=\"chado_search-filter-label form-item\" $width>
            $this->label
          </div>";
    $suffix = $form[$this->id]['#suffix'];
    $form[$this->id]['#suffix'] =
      "  <div id=\"chado_search-filter-$this->search_name-$this->id-description\" class=\"chado_search-filter-description\">
            $this->instruction
          </div>
        </div>";
    if ($this->newline) {
      $form[$this->id]['#suffix'] .= "<div class=\"chado_search-element-newline\"> </div>";
    }
  }
  
}