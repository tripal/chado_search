<?php

namespace ChadoSearch\set\form;

class SetTextArea extends SetElement {
  
  private $columns = 20;
  private $rows =5;
  private $required = FALSE;

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
  
}