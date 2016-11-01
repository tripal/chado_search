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
    if ($path == NULL) {
      $path = "search/$search_id/fasta";
    } else {
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
              $.ajax({
                url: link,
                data: '$dpost',
                dataType: 'json',
                type: 'POST',
                success: function(data){
                  window.location = data.path;
                  $('.chado_search-$search_id-waiting-box').hide();
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
    $result = chado_query($sql);
    $sid = session_id();
    $file = 'sequence.fasta.gz';
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
          (SELECT genus || ' ' || species FROM {organism} O WHERE O.organism_id = F.organism_id) AS org FROM {feature} F";
    $where = " WHERE feature_id IN (";
    while ($row = $result->fetchObject()) {
      $where .= $row->$column . ",";
    }
    $where = rtrim($where, ",") . ")";
    $result = chado_query($fsql . $where);
    while ($feature = $result->fetchObject()) {
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
    }
    gzclose($handle);
    chmod($path, 0777);
    $url = "/sites/default/files/tripal/chado_search/$sid/$file";
    return array ('path' => $url);
  }
}