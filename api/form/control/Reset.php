<?php

namespace ChadoSearch\form\control;

class Reset extends Element {

  public $path;
  
  public function __construct($search_name, $id) {
    parent::__construct($search_name, $id, strtolower(chado_search_get_class($this)));
    $this->type = 'button';
    $this->value = 'Reset';
  }
  
  public function setForm(&$form, &$form_state) {
    global $base_url;
    $goto ="$base_url/$this->path";
    $form[$this->id]['#attributes'] = array('onclick' => "window.location='$goto';return false;");
  }
  
}