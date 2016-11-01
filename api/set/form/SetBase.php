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
  private $newLine = FALSE;
  
  /**
   * Setters
   * @return $this
   */
  public function id ($id) {
    $this->id = $id;
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
  
  public function getNewLine() {
    return $this->newLine;
  }
}