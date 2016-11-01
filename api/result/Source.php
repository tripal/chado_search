<?php

namespace ChadoSearch\result;

class Source {

  // In the sub-class, generate the the Souce Code in its constructor and store it in $this->src
  // so the Souce Code can be returned when getSrc() is called
  public $src;
  
  // Return Source Code
  public function getSrc () {
    return $this->src;
  }
  
}