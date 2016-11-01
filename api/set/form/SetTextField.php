<?php

namespace ChadoSearch\set\form;

class SetTextField extends SetElement {
  
  private $required = FALSE;
  private $size;

  /**
   * Setters
   * @return $this
   */
  public function required ($required) {
    $this->required = $required;
    return $this;
  }
  
  public function size ($size) {
    $this->size = $size;
    return $this;
  }

  /**
   * Getters
   */
  public function getRequired () {
    return $this->required;
  }
  
  public function getSize () {
    return $this->size;
  }
  
}