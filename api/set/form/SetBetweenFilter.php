<?php

namespace ChadoSearch\set\form;

class SetBetweenFilter extends SetElement {
  
  private $id2 = '';
  private $title2 = '';
  private $label_width = 0;
  private $label_width2 = 0;
  private $size = 0;

  /**
   * Setters
   * @return $this
   */
  public function id2 ($id2) {
    $this->id2 = $id2;
    return $this;
  }

  public function title2 ($title2) {
    $this->title2 = $title2;
    return $this;
  }
  public function labelWidth ($label_width) {
    $this->label_width = $label_width;
    return $this;
  }
  
  public function labelWidth2 ($label_width) {
    $this->label_width2 = $label_width;
    return $this;
  }
  
  public function size ($size) {
    $this->size = $size;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getId2 () {
    return $this->id2;
  }

  public function getTitle2 () {
    return $this->title2;
  }
  
  public function getLabelWidth () {
    return $this->label_width;
  }
  
  public function getLabelWidth2 () {
    return $this->label_width2;
  }
  
  public function getSize () {
    return $this->size;
  }
}