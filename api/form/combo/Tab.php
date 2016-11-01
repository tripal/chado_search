<?php

namespace ChadoSearch\form\combo;

class Tab extends Filter {

  public $items;
  
  public function setForm (&$form, &$form_state) {
    $id = $this->id;
    $items = $this->items;
    $tabs = "";
    foreach ($items AS $k => $v) {
      $tabs .=
        "<div class=\"chado_search-tab-space\" style=\"float:left\">
           &nbsp;
         </div>
         <div class=\"chado_search-tab\" style=\"float:left\">
           <a href=\"$k\">
             $v
           </a>
         </div>";
    }
    $tabs .= 
      "<div class=\"chado_search-tab-space\" style=\"float:left;\">
          &nbsp;
        </div>";
    $code = 
      "<style type=\"text/css\">
          .chado_search-tab-space {
            margin-top:10px;
            margin-bottom:10px;
            padding:3px 0px;
            float:left;
            width:10px;
            border-bottom:1px solid #CCCCCC;
          }
          .chado_search-tab {
            margin-top:10px;
            margin-bottom:10px;
            padding: 3px 8px;
            float:left;
            border-left: 1px solid #CCCCCC;
            border-top: 1px solid #CCCCCC;
            border-right: 1px solid #CCCCCC;
            background-color: #DDDDDD;
            border-radius: 5px 5px 0px 0px;
          }
          .chado_search-tab:hover {
            background-color: #EEEEEE;
          }
        </style>

        <script type=\"text/javascript\">
          // Disable hyperlink for current tab and set its background white
          var as = document.getElementsByTagName('a');
          if (as) {
            for (var i = 0; i < as.length; i ++) {
              if (as[i].parentNode.className == 'chado_search-tab' && 
                  (location.href.indexOf(as[i].href, location.href.length - as[i].href.length)) !== -1) {
                as[i].parentNode.style.backgroundColor = '#FFFFFF';
                var text = as [i].innerHTML;
                as[i].parentNode.innerHTML = text;
              }
            }
          }
        </script>";
    $form [$id] = array(
      '#id' => 'chado_search-id-' . $id,
      '#markup' => $tabs . $code,
      '#prefix' => "<div id=\"chado_search-markup-$id\" class=\"chado_search-markup chado_search-widget\" style=\"width:100%\">",
      '#suffix' => "</div>"
    );
  }
  
}