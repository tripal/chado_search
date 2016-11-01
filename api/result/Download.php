<?php

namespace ChadoSearch\result;

use ChadoSearch\SessionVar;

class Download extends Source {
  
  public function __construct($search_id, $path, $show) {
    $js = $this->jsDownload($search_id, $path, $show);
    $this->src = $js; 
  }
  
  private function jsDownload($search_id, $path, $show) {
    if ($path == NULL) {
      $path = "search/$search_id/download";
    } else {
      $path = $path . "/download";
    }
    $dpost = "form_build_id=" . $_POST['form_build_id'];
    global $base_url;
    $js =
      "<script type=\"text/javascript\">
          (function ($) {
            function " . $search_id . "_download (custom) {
              if (custom) {
                var sendData = '$dpost' + '&custom_function_call=' + custom;
              } else {
                var sendData = '$dpost';
              }
              var link = '$base_url';
              link += '/$path';
              $('.chado_search-$search_id-waiting-box').show();
              $.ajax({
                url: link,
                data: sendData,
                dataType: 'json',
                type: 'POST',
                success: function(data){
                  window.location = data.path;
                  $('.chado_search-$search_id-waiting-box').hide();
                }
              });
             }
             window." . $search_id . "_download = " . $search_id . "_download;
          })(jQuery);
       </script>";
    if ($show) {
      $js .=
        "<div id=\"$search_id-table-download\" class=\"chado_search-download-links\">
            <a href=\"javascript:void(0)\" onClick=\"" . $search_id . "_download();return false;\">
              Table
            </a>
         </div>";
    }
    return $js;
  }
  
  // Set up download
  public static function createDownload ($search_id, $path, $headers) {
  
    // If header is not defined, return
    if (!$headers) {
      $headers = SessionVar::getSessionVar($search_id, 'default-headers');
      if (!$headers) {
        return array();
      }
    }
    // Get the SQL from $_SESSION
    // Try to get SQL that includes '</br>' tag
    $sql = SessionVar::getSessionVar($search_id, 'download');
    if (!$sql) {
      // If no SQL with </br> tag found, get default SQL
      $sql = SessionVar::getSessionVar($search_id, 'sql');
    }
    if (!$sql) {
      return array('path' => "/$path");
    }
    $orderby = SessionVar::getSessionVar($search_id, 'download-order');
    if ($orderby) {
      $sql .= " ORDER BY " . $orderby;
    }
    // Disable columns on request
    $disabledCols = SessionVar::getSessionVar($search_id, 'disabled-columns');
    if ($disabledCols) {
      $dcols = explode(';', $disabledCols);
      foreach ($dcols AS $dc) {
          foreach($headers AS $hk => $hv) {
            $pattern = explode(':', $hk);
            if ($pattern[0] == $dc) {
              unset ($headers[$hk]);
            }
          }
      }
    }
    // Change the text file headers on request
    $changedHeaders = SessionVar::getSessionVar($search_id, 'changed-headers');
    if ($changedHeaders) {
      $cheaders = explode(';', $changedHeaders);
      foreach ($cheaders AS $ch) {
          foreach($headers AS $hk => $hv) {
            $pattern = explode(':', $hk);
            $h = explode('=', $ch);
            if ($pattern[0] == $h[0]) {
              $headers[$hk] = $h[1];
            }
          }
      }
    }
    // Rewrite columns on request, conver the session variable (i.e. <column1>=<callback1>;) into an associated array (i.e. 'column1' => 'callback1')
    $rewriteCols = SessionVar::getSessionVar($search_id, 'rewrite-columns');
    $rewriteCallback = array();
    if ($rewriteCols) {
      $rwcols = explode(';', $rewriteCols);
      foreach ($rwcols AS $rwc) {
        $rewrite = explode('=', $rwc);
        if (count($rewrite) == 2 && function_exists($rewrite[1]) ) {
          $rewriteCallback[$rewrite[0]] = $rewrite[1];
        }
      }
    }
  
    // Create result
    $result = chado_query($sql);
    $sid = session_id();
    $file = 'download.csv';
    $dir = 'sites/default/files/tripal/chado_search/' . $sid;
    if (!file_exists($dir)) {
      mkdir ($dir, 0777);
    }
    $path = $dir . "/" . $file;
    $handle = fopen($path, 'w');
    // If there is a custom function call, pass in $handle and $result for it to modify output
    $custom_function = isset($_POST['custom_function_call']) ? $_POST['custom_function_call'] : NULL;
    if ($custom_function) {
      $custom_function($handle, $result, $sql);
    } else {
      fwrite($handle, "#,");
      $col = 0;
      foreach ($headers AS $k => $v) {
        fwrite($handle, $v);
        $col ++;
        if ($col < count($headers)) {
          fwrite($handle, ",");
        } else {
          fwrite($handle, "\n");
        }
      }
      $counter = 1;
      while ($row = $result->fetchObject()) {
        fwrite($handle, "\"$counter\",");
        $col = 0;
        foreach ($headers AS $k => $v) {
          $value = $row->$k;
          if (key_exists($k, $rewriteCallback)) {
            $rwfunc = $rewriteCallback[$k];
            $value = $rwfunc($value);
          }
          fwrite($handle, '"' . str_replace('"', '""', $value) . '"');
          $col ++;
          if ($col < count($headers)) {
            fwrite($handle, ",");
          } else {
            fwrite($handle, "\n");
          }
        }
        $counter ++;
      }
    }
    fclose($handle);
    chmod($path, 0777);
    $url = "/sites/default/files/tripal/chado_search/$sid/$file";
    return array ('path' => $url);
  }
}