<?php

namespace ChadoSearch\set\form;

class SetDynamicSelectFilter extends SetElement {

  private $depend_on_id = '';
  private $callback = '';
  private $label_width = 0;
  private $size = 0;
  private $cacheTable = '';
  private $cacheColumns = array();
  private $reset_on_change_id;
  
  /**
   * Setters
   * @return $this
   */
  public function dependOnId ($id) {
    $this->depend_on_id = $id;
    return $this;
  }

  public function labelWidth ($label_width) {
    $this->label_width = $label_width;
    return $this;
  }
  
  public function callback ($callback) {
    $this->callback = $callback;
    return $this;
  }

  public function size ($size) {
    $this->size = $size;
    return $this;
  }
  
  public function cache ($table, $columns = array()) {
    $this->cacheTable = $table;
    $this->cacheColumns = $columns;
    return $this;
  }
  
  public function resetOnChagne($id) {
    $this->reset_on_change_id = $id;
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
  
  public function getLabelWidth () {
    return $this->label_width;
  }

  public function getSize () {
    return $this->size;
  }
  
  public function getCacheTable () {
    return $this->cacheTable;
  }
  
  public function getCacheColumns () {
    return $this->cacheColumns;
  }
  
  public function getResetOnChange() {
    return $this->reset_on_change_id;
  }
}