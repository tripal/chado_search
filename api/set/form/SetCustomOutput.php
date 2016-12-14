<?php

namespace ChadoSearch\set\form;

class SetCustomOutput extends SetBase {
  private $options;
  private $defaults;
  private $title;
  private $desc;
  private $groupby_selection;
  
  /**
   * Setters
   * @return $this
   */
  public function options ($options) {
    $this->options = $options;
    return $this;
  }
  
  public function defaults ($defaults) {
    $this->defaults = $defaults;
    return $this;
  }
  
  public function title ($title) {
    $this->title = $title;
    return $this;
  }
  
  public function description ($desc) {
    $this->desc = $desc;
    return $this;
  }
  
  public function groupBySelection ($bool) {
    $this->groupby_selection = $bool;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getOptions() {
    return $this->options;
  }
  
  public function getDefaults() {
    return $this->defaults;
  }
  
  public function getTitle() {
    return $this->title;
  }
  
  public function getDescription() {
    return $this->desc;
  }
  
  public function getGroupBySelection() {
    return $this->groupby_selection;
  }
}