<?php

namespace ChadoSearch\set\form;

class SetFile extends SetElement {

  private $description = NULL;
  private $size = 0;
  private $label_width = 0;
  
  /**
   * Setters
   * @return $this
   */
  public function description ($description) {
    $this->description = $description;
    return $this;
  }

  public function size ($size) {
    $this->size = $size;
    return $this;
  }

  public function labelWidth ($label_width) {
    $this->label_width = $label_width;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getDescription () {
    return $this->description;
  }
  
  public function getSize () {
    return $this->size;
  }
  
  public function getLabelWidth () {
    return $this->label_width;
  }
  
}