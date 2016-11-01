<?php

namespace ChadoSearch\set\form;

class SetMarkup extends SetBase {

  private $text = '';
  
  /**
   * Setters
   * @return $this
   */
  public function text ($text) {
    $this->text = $text;
    return $this;
  }
  
  /**
   * Getters
   */
  public function getText () {
    return $this->text;
  }
  
}