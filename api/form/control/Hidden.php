<?php

namespace ChadoSearch\form\control;

use ChadoSearch\form\control\Element;

class Hidden extends Element {
  
  public function __construct($search_name, $id) {
    parent::__construct($search_name, $id, strtolower(chado_search_get_class($this)));
    $this->attributes = array('id' => 'chado_search-id-' . $id);
  }
}