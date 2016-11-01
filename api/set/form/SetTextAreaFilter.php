<?php

namespace ChadoSearch\set\form;

class SetTextAreaFilter extends SetElement {
  
  private $columns = 20;
  private $rows =5;
  private $required = FALSE;
  private $label_width = 0;

  /**
   * Setters
   * @return $this
   */
  public function columns ($columns) {
    $this->columns = $columns;
    return $this;
  }
  
  public function rows ($rows) {
    $this->rows = $rows;
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
  public function getColumns () {
    return $this->columns;
  }
  
  public function getRows () {
    return $this->rows;
  }
  
  public function getRequired () {
    return $this->required;
  }
  
  public function getLabelWidth () {
    return $this->label_width;
  }
}