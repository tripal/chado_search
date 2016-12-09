<?php

namespace ChadoSearch\result;

use ChadoSearch\SessionVar;

require_once 'Source.php';

class Fasta extends Source {
  
  public function __construct($search_id, $path) {
    $js = $this->jsFasta($search_id, $path);
    $this->src = $js; 
  }
  
  private function jsFasta($search_id, $path) {
    $progress_path = '';
    if ($path == NULL) {
      $path = "search/$search_id/fasta";
      $progress_path = "search/$search_id/download/progress";
    } else {
      $progress_path = "$path/download/progress";
      $path = $path . "/fasta";
    }
    $dpost = "form_build_id=" . $_POST['form_build_id'];
    global $base_url;
    $js =
      "<script type=\"text/javascript\">
          (function ($) {
            function " . $search_id . "_fasta () {
              var link = '$base_url';
              link += '/$path';
              $('.chado_search-$search_id-waiting-box').show();
              var check_progress = setInterval(function(){
                // Check the progress
                $.ajax({
                  url: '$base_url' + '/' + '$progress_path',
                  dataType: 'json',
                  success: function(data){
                    $('#chado_search-$search_id-waiting-box-progress').show();
                    $('#chado_search-$search_id-waiting-box-progress').text(data.progress + ' %');
                  }
                });
              }, 2000);
              $.ajax({
                url: link,
                data: '$dpost',
                dataType: 'json',
                type: 'POST',
                success: function(data){
                  window.location = data.path;
                  $('.chado_search-$search_id-waiting-box').hide();
                  $('#chado_search-$search_id-waiting-box-progress').text('0 %');
                  $('#chado_search-$search_id-waiting-box-progress').hide();
                  clearInterval(check_progress);
                }
              });
            }
            window." . $search_id . "_fasta = " . $search_id . "_fasta;
          })(jQuery);
        </script>";
    $js .=
      "<div id=\"$search_id-fasta-download\" class=\"chado_search-download-links\">
          <a href=\"javascript:void(0)\" onClick=\"" . $search_id . "_fasta();return false;\">
            Fasta
          </a>
        </div>";
    $js .=
      "<div id=\"$search_id-download-separator\" class=\"chado_search-download-separator\">
          |
        </div>";
    return $js;
  }
  
  public static function createFasta ($search_id, $path, $column = 'feature_id') {
    ini_set('max_execution_time', 6000);
    $sql = SessionVar::getSessionVar($search_id, 'sql');
    if (!$sql) {
      return array('path' => "/$path");
    }
    $customFasta = SessionVar::getSessionVar($search_id, 'custom-fasta-download');
    if ($customFasta) {
      $sql = $customFasta($sql);
    }
    $sid = session_id();
    $file = $search_id . '_sequence.fasta.gz';
    $dir = 'sites/default/files/tripal/chado_search/' . $sid;
    if (!file_exists($dir)) {
      mkdir ($dir, 0777);
    }
    $path = $dir . "/" . $file;
    $handle = gzopen($path, 'w9');
    $fsql = "
        SELECT
          feature_id,
          name,
          uniquename,
          residues,
          (SELECT name FROM {cvterm} WHERE cvterm_id = type_id) AS type,
          (SELECT genus || ' ' || species FROM {organism} O WHERE O.organism_id = F.organism_id) AS org 
        FROM {feature} F
        WHERE feature_id IN (SELECT $column FROM ($sql) BASE)
        AND residues IS NOT NULL
        AND residues != ''";    
    $result = chado_query($fsql);
    $total_items = SessionVar::getSessionVar($search_id,'total-items');
    $progress_var = 'chado_search-'. session_id() . '-' . $search_id . '-download-progress';
    $progress = 0;
    $counter = 1;
    while ($feature = $result->fetchObject()) {
      $current = round ($counter / $total_items * 100);
      if ($current != $progress) {
        $progress = $current;
        variable_set($progress_var, "$counter processed. $progress");
      }
      // If the sequence type is genetic marker, we want to add more information to the ID
      if ($feature->type == 'genetic_marker') {
        $mksql =
        "SELECT SEQ.uniquename, SEQ.residues FROM {feature} MK
        INNER JOIN {feature_relationship} FR ON MK.feature_id = FR.object_id
        INNER JOIN {feature} SEQ ON SEQ.feature_id = FR.subject_id
        WHERE FR.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'sequence_of' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'sequence'))
        AND MK.feature_id = $feature->feature_id";
        $seq = chado_query($mksql)->fetchObject();
        $mtype = chado_query("SELECT value FROM {featureprop} WHERE feature_id = $feature->feature_id AND type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'marker_type' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'MAIN'))")->fetchField();
        fwrite($handle, ">" . $feature->uniquename . "|" . $seq->uniquename . "| " . $feature->org . " " . $mtype . "\n");
        fwrite($handle, wordwrap($seq->residues, 80, "\n", TRUE) . "\n");
      } else {
        // if feature uniquename != name, write both to the output
        if ($feature->uniquename  != $feature->name) {
          fwrite($handle, ">" . $feature->uniquename . " " . $feature->name . "\n");
        } else {
          fwrite($handle, ">" . $feature->uniquename . "\n");
        }
      }
      // Write sequences
      fwrite($handle, wordwrap($feature->residues, 80, "\n", TRUE) . "\n");
      $counter ++;
    }
    // If there is no sequence available
    if ($counter == 1) {
      fwrite($handle, "No sequence available.\n");
    }
    gzclose($handle);
    chmod($path, 0777);
    $url = "/sites/default/files/tripal/chado_search/$sid/$file";
    // Reset progress bar
    variable_del($progress_var);
    return array ('path' => $url);
  }
}