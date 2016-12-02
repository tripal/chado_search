<?php

namespace ChadoSearch\result;

class SettingsBox extends Source {
  
  public function __construct($search_id, $headers) {
    $html = $this->htmlSettingsBox($search_id, $headers);
    $this->src = $html; 
  }
  
  private function htmlSettingsBox($search_id, $headers) {
    $controls = '<h4>Display:</h4>';
    $col = 'counter';
    $controls .= 
      "<label><input id=\"chado_search-$search_id-settings-$col\" type=\"checkbox\" checked
        onClick=\"
          (function ($) {
             function getCookie(cname) {
               var name = cname + \"=\";
               var ca = document.cookie.split(';');
               for(var i = 0; i <ca.length; i++) {
                 var c = ca[i];
                 while (c.charAt(0)==' ') {
                   c = c.substring(1);
                 }
                 if (c.indexOf(name) == 0) {
                   return c.substring(name.length,c.length);
                 }
               }
               return \"\";
             }
             alert(getCookie(.chado_search-result_column-$col));
             if($('#chado_search-$search_id-settings-$col').prop('checked')) {
               document.cookie = '.chado_search-result_column-$col=show';
             } else {
               document.cookie = '.chado_search-result_column-$col=hide';
             }
             return false;
          })(jQuery);\">
         Counter 
       </label>";
    foreach($headers AS $k => $name) {
      $key = explode(':', $k);
      $col = $key[0];
    }
    $controls .= '</div>';
    $settingsBox =
      "<div id=\"chado_search-$search_id-settings-box-control\" class=\"chado_search-settings-box-control\">
          <a href=\"javascript:void(0)\" onClick=\"(function ($) {;$('.chado_search-$search_id-settings-box').show();return false;})(jQuery);\">
            Customize results
          </a>
        </div>
        <div id=\"chado_search-$search_id-settings-box-overlay\" class=\"chado_search-settings-box-overlay chado_search-$search_id-settings-box\"
        onClick=\"(function ($) {;$('.chado_search-$search_id-settings-box').hide();return false;})(jQuery);\">
        </div>
        <div id=\"chado_search-$search_id-settings-box-message\" class=\"chado_search-settings-box-message chado_search-$search_id-settings-box\">
          <div>$controls</div>
        </div>";
    $js = 
    "<script type=\"text/javascript\">
        (function ($) {
            $(document).ready(function(){
             update_column();
            });
            function update_column() {
             //alert(document.cookie); 
            }
            })(jQuery);
    </script>";
    $settingsBox .= $js;
    return $settingsBox;
  }
}
