<?php

namespace ChadoSearch\set\form;

class SetTextFilter extends SetElement {
  
  private $size = 0;
  private $label_width = 0;
  private $required = FALSE;
  
  /**
   * Setters
   * @return $this
   */
  public function size ($size) {
    $this->size = $size;
    return $this;
  }
  
  public function labelWidth ($label_width) {
    $this->label_width = $label_width;
    return $this;
  }

  public function required ($required) {
    $this->required = $required;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getSize () {
    return $this->size;
  }
  
  public function getLabelWidth () {
    return $this->label_width;
  }
  
  public function getRequired () {
    return $this->required;
  }
}