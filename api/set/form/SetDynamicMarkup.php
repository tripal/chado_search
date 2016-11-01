<?php

namespace ChadoSearch\set\form;

class SetDynamicMarkup extends SetBase {
  
  private $depend_on_id = '';
  private $callback = '';
  
  /**
   * Setters
   * @return $this
   */
  public function dependOnId ($id) {
    $this->depend_on_id = $id;
    return $this;
  }
  
  public function callback ($callback) {
    $this->callback = $callback;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getDependOnId () {
    return $this->depend_on_id;
  }
  
  public function getCallback() {
    return $this->callback;
  }
  
}