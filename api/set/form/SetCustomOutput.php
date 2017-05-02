<?php

namespace ChadoSearch\set\form;

class SetCustomOutput extends SetBase {
  private $options;
  private $defaults;
  private $title;
  private $desc;
  private $collapsible;
  private $collapsed;
  private $replace_star;
  
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
  
  public function collapsible($sollapsible) {
    $this->collapsible = $sollapsible;
    return $this;
  }
  
  public function collapsed($collapsed) {
    $this->collapsed= $collapsed;
    return $this;
  }
  
  public function replaceStarWithSelection () {
    $this->replace_star = TRUE;
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
  
  public function getCollapsible() {
    return $this->collapsible;
  }
  
  public function getcollapsed() {
    return $this->collapsed;
  }
  
  public function getDescription() {
    return $this->desc;
  }
  
  public function getReplaceStarWithSelection() {
    return $this->replace_star;
  }
}