<?php

namespace ChadoSearch\set\form;

class SetSelectShortCut extends SetBase {
  
  private $pretext = '';
  private $posttext = '';
  private $value = NULL;
  
  /**
   * Setters
   * @return $this
   */
  public function pretext ($pretext) {
    $this->pretext = $pretext;
    return $this;
  }
  
  public function posttext ($posttext) {
    $this->posttext = $posttext;
    return $this;
  }

  public function value ($value) {
    $this->value = $value;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getPretext() {
    return $this->pretext;
  }
  
  public function getPosttext() {
    return $this->posttext;
  }

  public function getValue () {
    return $this->value;
  }
  
}