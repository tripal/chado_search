<?php

namespace ChadoSearch\form\control;

class TextField extends Element {
  
  public function __construct($search_name, $id) {
    parent::__construct($search_name, $id, strtolower(chado_search_get_class($this)));
  }
}