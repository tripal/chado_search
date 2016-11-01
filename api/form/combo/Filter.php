<?php

namespace ChadoSearch\form\combo;

class Filter {
  
  public $id;
  public $csform;
  public $search_name;
  public $path;
  public $newline;
  
  public function __construct($csform) {
    $this->csform = $csform;
    $this->search_name = $csform->search_name;
    $this->path = $csform->path;
  }
  
    // Allow the sub-class to override or add new attributes to the form
  // If the subclass has its own veriable(s) to be used to set the form, you have to
  // use this function to add those changes
  public function setForm (&$form, &$form_state) {}
  
  public function attach(&$form, &$form_state) {

    // Warn if ID is not specified
    if (!$this->id) {
      form_set_error('invalid_id', "Please specify an ID for the'" . chado_search_get_class($this) . "'.");
    }
    
    // Allow override or add more attributes
    $this->setForm ($form, $form_state);
    
    // Add a new line at the end of the filter widget
    if ($this->newline) {
      $id = $this->id;
      if(chado_search_get_class($this) == 'BetweenFilter') {
        $id = $this->id2;
      }
      $form[$id]['#suffix'] .= "<div class=\"chado_search-element-newline\"> </div>";
    }
  }
}