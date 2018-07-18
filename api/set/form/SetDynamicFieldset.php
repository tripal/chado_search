<?php

namespace ChadoSearch\set\form;

class SetDynamicFieldset extends SetBase {
  
  private $depend_on_id = '';
  private $callback = '';
  private $title = '';
  private $description = '';
  private $collapsible = TRUE;
  private $collapsed = FALSE;
  private $witdh;
  
  /**
   * Setters
   * @return $this
   */
  public function dependOnId ($id) {
    $this->depend_on_id = $id;
    return $this;
  }
  
  public function callback ($callback) {
    $this->callback = $callback;
    return $this;
  }
  
  public function title ($title) {
    $this->title = $title;
    return $this;
  }
  
  public function description ($desc) {
    $this->description = $desc;
    return $this;
  }
  
  public function collapsible ($collapsible) {
    $this->collapsible = $collapsible;
    return $this;
  }
  
  public function collapsed () {
    $this->collapsed = TRUE;
    return $this;
  }
  
  public function width ($width) {
    $this->width = $width;
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
  
  public function getTitle () {
    return $this->title;
  }
  
  public function getDescription () {
    return $this->description;
  }
  
  public function getCollapsible () {
    return $this->collapsible;
  }
  
  public function getCollapsed () {
    return $this->collapsed;
  }
  
  public function getWidth () {
    return $this->width;
  }
}