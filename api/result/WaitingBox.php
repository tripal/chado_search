<?php

namespace ChadoSearch\result;

class WaitingBox extends Source {
  
  public function __construct($search_id) {
    $html = $this->htmlWaitingBox($search_id);
    $this->src = $html; 
  }
  
  private function htmlWaitingBox($search_id) {
    $waitingBox =
      "<div id=\"chado_search-$search_id-waiting-box-overlay\" class=\"chado_search-waiting-box-overlay chado_search-$search_id-waiting-box\">
        </div>
        <div id=\"chado_search-$search_id-waiting-box-message\" class=\"chado_search-waiting-box-message chado_search-$search_id-waiting-box\">
          <h3>Please wait...</h3>
        </div>";
    return $waitingBox;
  }
}