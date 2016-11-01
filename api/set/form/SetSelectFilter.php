<?php

namespace ChadoSearch\set\form;

class SetSelectFilter extends SetElement {
  
  private $multiple = FALSE;
  private $column = '';
  private $table = '';
  private $column_natural_sort = FALSE;
  private $optgroup = NULL;
  private $optgroup_by_pattern = NULL;
  private $cache = FALSE;
  private $required = FALSE;
  private $label_width = 0;
  private $size = 0;
  
  /**
   * Setters
   * @return $this
   */
  public function multiple ($multiple) {
    $this->multiple = $multiple;
    return $this;
  }
  
  public function column ($column) {
    $this->column = $column;
    return $this;
  }
  
  public function table ($table) {
    $this->table = $table;
    return $this;
  }
  
  public function columnNaturalSort ($column_natural_sort) {
    $this->column_natural_sort = $column_natural_sort;
    return $this;
  }
  
  public function optGroup ($optgroup) {
    $this->optgroup = $optgroup;
    return $this;
  }
  
  public function optGroupByPattern ($optgroup_by_pattern) {
    $this->optgroup_by_pattern = $optgroup_by_pattern;
    return $this;
  }
  
  public function cache ($cache) {
    $this->cache = $cache;
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
  public function getColumn () {
    return $this->column;
  }
  
  public function getTable () {
    return $this->table;
  }
  
  public function getColumnNaturalSort() {
    return $this->column_natural_sort;
  }
  
  public function getOptGroup() {
    return $this->optgroup;
  }
  
  public function getOptGroupByPattern() {
    return $this->optgroup_by_pattern;
  }
  
  public function getCache() {
    return $this->cache;
  }
  
  public function getMultiple () {
    return $this->multiple;
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