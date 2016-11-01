<?php

namespace ChadoSearch\form\combo;

class Throbber extends Filter {
  
  public function setForm (&$form, &$form_state) {
    $id = $this->id;
    $suffix = $form[$id]['#suffix'];
    $suffix .= 
      "<div id=\"chado_search-throbber-$id\" class=\"chado_search-throbber ajax-progress ajax-progress-throbber\">
         <div class=\"throbber\">
         </div>
       </div>";
    $form[$id]['#suffix'] = $suffix;
  }
  
}