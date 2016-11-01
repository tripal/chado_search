<?php

namespace ChadoSearch\form\combo;

use ChadoSearch\Set;

class BetweenFilter extends Filter {
  
  public $id2;
  public $title;
  public $title2;
  public $label_width;
  public $label_width2;
  public $size;
  
  public function setForm (&$form, &$form_state) {
    $this->csform->addLabeledFilter(Set::labeledFilter()->id($this->id)->title($this->title)->size($this->size)->labelWidth($this->label_width));
    $this->csform->addLabeledFilter(Set::labeledFilter()->id($this->id2)->title($this->title2)->size($this->size)->labelWidth($this->label_width2));
  }
  
}
