<?php

namespace ChadoSearch\set\form;

class SetSelectOptionFilter extends SetElement {
  
  private $options = array();
  private $multiple = FALSE;
  private $nokeyconversion = FALSE;
  private $required = FALSE;
  private $label_width = 0;
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

  public function noKeyConversion ($nokeyconversion) {
    $this->nokeyconversion = $nokeyconversion;
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

  public function getNoKeyConversion() {
    return $this->nokeyconversion;
  }
  
  public function getRequired () {
    return $this->required;
  }
  
  public function getLabelWidth () {
    return $this->label_width;
  }
  
  public function getSize () {
    return $this->size;
  }
  
}