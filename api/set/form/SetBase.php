<?php

namespace ChadoSearch\set\form;

/**
 * This is the base configuration which contains only a ID field
 * 
 * @author ccheng
 *
 */
class SetBase {
  
  private $id = '';
  private $display = '';
  private $fieldset_id = '';
  private $default_value = array();
  private $newLine = FALSE;
  
  /**
   * Setters
   * @return $this
   */
  public function id ($id) {
    $this->id = $id;
    return $this;
  }

  public function display ($value) {
    $this->display = $value;
    return $this;
  }
  
  public function fieldset ($fieldset_id) {
    $this->fieldset_id = $fieldset_id;
    return $this;
  }  
  
  public function defaultValue ($values) {
    $this->default_value = $values;
    return $this;
  }
  
  public function newLine() {
    $this->newLine = TRUE;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getId () {
    return $this->id;
  }
  
  public function getDisplay () {
    return $this->display;
  }
  
  public function getFieldset () {
    return $this->fieldset_id;
  }  
  
  public function getDefaultValue () {
    return $this->default_value;
  }
  
  public function getNewLine() {
    return $this->newLine;
  }
}