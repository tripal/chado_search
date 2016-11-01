<?php

namespace ChadoSearch\set\form;

class SetFieldset extends SetElement {
  
  private $start_widget = '';
  private $end_widget = '';
  private $collapased = FALSE;
  private $description = NULL;
  
  /**
   * Setters
   * @return $this
   */
  public function startWidget ($start_widget) {
    $this->start_widget = $start_widget;
    return $this;
  }
  
  public function endWidget ($end_widget) {
    $this->end_widget = $end_widget;
    return $this;
  }
  
  public function collapased ($collapased) {
    $this->collapased = $collapased;
    return $this;
  }
  
  public function description ($description) {
    $this->description = $description;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getStartWidget() {
    return $this->start_widget;
  }
  
  public function getEndWidget() {
    return $this->end_widget;
  }
  
  public function getCollapased() {
    return $this->collapased;
  }
  
  public function getDescription () {
    return $this->description;
  }
  
}