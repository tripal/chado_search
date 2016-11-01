<?php

namespace ChadoSearch\set\form;
/**
 * This class contains both ID and Title fields
 * @author ccheng
 *
 */
class SetElement extends SetBase {
  
  private $title = '';
  
  /**
   * Setters
   * @return $this
   */
  public function title ($title) {
    $this->title = $title;
    return $this;
  }

  /**
   * Getters
   */
  public function getTitle () {
    return $this->title;
  }
  
}