<?php

namespace ChadoSearch\set\form;

class SetSelect extends SetElement {

  private $options = array();
  private $multiple = FALSE;
  private $size = 0;

  /**
   * Setters
   * @return $this
   */
  public function options ($options) {
    $this->options = $options;
    return $this;
  }
  
  public function multiple ($multiple) {
    $this->multiple = $multiple;
    return $this;
  }
  
  public function size ($size) {
    $this->size = $size;
    return $this;
  }

  /**
   * Getters
   */
  public function getOptions () {
    return $this->options;
  }
  
  public function getMultiple () {
    return $this->multiple;
  }
  
  public function getSize () {
    return $this->size;
  }
  
}