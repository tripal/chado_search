<?php

namespace ChadoSearch\set\form;

class SetTab extends SetBase {
  
  private $items = array();
  
  public function items ($items) {
    $this->items = $items;
    return $this;
  }
  
  public function getItems() {
    return $this->items;
  }
  
}