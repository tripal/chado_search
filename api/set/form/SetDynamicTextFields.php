<?php

namespace ChadoSearch\set\form;

class SetDynamicTextFields extends SetBase{
  
  private $target_ids = array();
  private $callback = '';
  private $reset_on_change_id;
  
  /**
   * Setters
   * @return $this
   */
  
  public function targetIds ($target_ids) {
    $this->target_ids = $target_ids;
    return $this;
  }
  
  public function callback ($callback) {
    $this->callback = $callback;
    return $this;
  }
  
  public function resetOnChagne($id) {
    $this->reset_on_change_id = $id;
    return $this;
  }
  
  /**
   * Getters
   */ 
  public function getTargetIds() {
    return $this->target_ids;
  }
  
  public function getCallback() {
    return $this->callback;
  }
  
  public function getResetOnChange() {
    return $this->reset_on_change_id;
  }
}