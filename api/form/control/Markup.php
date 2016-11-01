<?php

namespace ChadoSearch\form\control;

class Markup extends Element {

  public function __construct($search_name, $id) {
    parent::__construct($search_name, $id, strtolower(chado_search_get_class($this)));
    $this->prefix = "<div id=\"chado_search-$search_name-$this->type-$id\" class=\"chado_search-$search_name-$this->type chado_search-$this->type chado_search-widget form-item\">";
  }
}