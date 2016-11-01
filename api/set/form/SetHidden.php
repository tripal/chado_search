<?php

namespace ChadoSearch\set\form;

class SetHidden extends SetBase {
  
  private $value = NULL;

  /**
   * Setters
   * @return $this
   */
  public function value ($value) {
    $this->value = $value;
    return $this;
  }

  /**
   * Getters
   */
  public function getValue () {
    return $this->value;
  }
  
}