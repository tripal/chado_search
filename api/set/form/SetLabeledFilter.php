<?php

namespace ChadoSearch\set\form;

class SetLabeledFilter extends SetElement {
  
  private $size = 0;
  private $required = FALSE;
  private $label_width = 0;
  
  /**
   * Setters
   * @return $this
   */
  public function size ($size) {
    $this->size = $size;
    return $this;
  }
  
  public function required ($required) {
    $this->required = $required;
    return $this;
  }  

  public function labelWidth ($label_width) {
    $this->label_width = $label_width;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getSize () {
    return $this->size;
  }
  
  public function getRequired () {
    return $this->required;
  }
  
  public function getLabelWidth () {
    return $this->label_width;
  }
}