<?php

namespace ChadoSearch\result;

class CustomDownload extends Source {
  
  public function __construct($search_id, $customDownload) {
    if (count($customDownload) > 0) {
      $html = $this->htmlCustomDownload($search_id, $customDownload);
      $this->src = $html;
    }
  }
  
  private function htmlCustomDownload($search_id, $customDownload) {
    $div = "";
    // Custom Download(s)
    $counter_separator = 0;
    foreach ($customDownload AS $kd => $vd) {
      if ($kd == 'disable_default') {
        $separator_off = 1;
      } else {
        $div .=
        "<div class=\"chado_search-download-label\" style=\"margin-right:0px;\">
                  <a href=\"javascript:void(0)\" onClick=\"" . $search_id . "_download('$kd');return false;\">
                      $vd
                      </a>
                      </div>";
                      if (!$separator_off || $counter_separator < count($customDownload) - 2) {
                        $div .=
                        "<div id=\"$search_id-download-separator\" class=\"chado_search-download-separator\">
                        |
                        </div>";
                        $counter_separator ++;
                      }
      }
    }
    return $div;
  }
}