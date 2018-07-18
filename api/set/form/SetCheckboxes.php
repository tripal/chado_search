<?php

namespace ChadoSearch\set\form;

class SetCheckboxes extends SetElement {

  private $options = array();
  
  /**
   * Setters
   * @return $this
   */  
  public function options ($options) {
    $this->options = $options;
    return $this;
  }
  
  /**
   * Getters
   */  
  public function getOptions () {
    return $this->options;
  }
  
}