<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_snp_marker_search_form ($form) {

  // Search by Name
  $form->addTextFilter(
      Set::textFilter()
      ->id('snp_uniquename')
      ->title('SNP Name')
      ->labelWidth(120)
  );
  $form->addFile(
      Set::file()
      ->id('feature_name_file_inline')
      ->labelWidth(1)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('array_name')
      ->column('array_name')
      ->table('chado_search_snp_marker_search')
      ->cache(TRUE)
      ->title('Array Name')
      ->labelWidth(120)
      ->newLine()
  );
  // Restricted by Location
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('genome')
      ->title('Genome')
      ->column('genome')
      ->table('chado_search_snp_marker_search')
      ->cache(TRUE)
      ->newLine()
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('location')
      ->title('Chr/Scaffold')
      ->dependOnId('genome')
      ->callback('chado_search_snp_marker_search_ajax_location')
      ->labelWidth(120)
  );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('fmin')
      ->title("between")
      ->id2('fmax')
      ->title2("and")
      ->labelWidth2(50)
      ->size(10)
      );
  $form->addMarkup(
      Set::markup()
      ->id('location_unit')
      ->text("<strong>bp</strong>")
      ->newLine()
      );
  $form->addSubmit();
  $form->addReset();
  $desc =
  'Search for SNP markers in GDR.
     <b>| ' . l('Short video tutorial', 'https://www.youtube.com/watch?v=oqiuSI99mMg', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', 'tutorial/marker_search') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('top_level')
      ->startWidget('snp_uniquename')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_snp_marker_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_snp_marker_search_base_query();
  // Add conditions
  $where [0] = Sql::textFilterOnMultipleColumns('snp_uniquename', $form_state, array('snp_uniquename', 'alias'));
  $where [1] = Sql::fileOnMultipleColumns('feature_name_file_inline', array('snp_uniquename', 'alias'));
  $where [2] = Sql::selectFilter('array_name', $form_state, 'array_name');
  $where [5] = Sql::selectFilter('genome', $form_state, 'genome');
  $where [6] = Sql::selectFilter('location', $form_state, 'landmark');
  $where [7] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_snp_marker_search_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_snp_marker_search_base_query() {
  $query = "SELECT * FROM {chado_search_snp_marker_search}";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_snp_marker_search_table_definition () {
  $headers = array(      
      'snp_name:s:chado_search_snp_marker_search_link_feature:snp_feature_id' => 'Name',
      'array_name:s' => 'SNP Array Name',
      'array_id:s' => 'SNP Array ID',
      'alias:s' => 'Alias',
      'allele:s' => 'Allele',
      'location:s:chado_search_snp_marker_search_link_gbrowse:landmark_feature_id,location' => 'Location',
      'flanking_sequence:s' => 'Flanking Sequence'
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_snp_marker_search_link_feature ($feature_id) {
  $nid = chado_get_nid_from_id('feature', $feature_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the location to GDR GBrowse
function chado_search_snp_marker_search_link_gbrowse ($paras) {
  $srcfeature_id = $paras [0];
  $loc = preg_replace("/ +/", "", $paras [1]);
  $sql = 
    "SELECT A.name
    FROM {feature} F
    INNER JOIN {analysisfeature} AF ON F.feature_id = AF.feature_id
    INNER JOIN {analysis} A ON A.analysis_id = AF.analysis_id
    INNER JOIN {analysisprop} AP ON AP.analysis_id = A.analysis_id
    INNER JOIN {cvterm} V ON V.cvterm_id = AP.type_id
    WHERE
    V.name = 'Analysis Type' AND
    AP.value = 'whole_genome' AND
    F.feature_id = :srcfeature_id";
  $genome = chado_query($sql, array('srcfeature_id' => $srcfeature_id))->fetchField();
  $url = "";
  if($genome == 'Fragaria vesca Whole Genome v1.0 (build 8) Assembly & Annotation') {
    $url = "http://www.rosaceae.org/gb/gbrowse/fragaria_vesca_v1.0-lg?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  else if ($genome == 'Fragaria vesca Whole Genome v1.1 Assembly & Annotation') {
    $url = "http://www.rosaceae.org/gb/gbrowse/fragaria_vesca_v1.1-lg?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  else if ($genome == 'Prunus persica Whole Genome v1.0 Assembly & Annotation') {
    $url = "http://www.rosaceae.org/gb/gbrowse/prunus_persica?name=$loc&enable=NCBI%20Sequence%20Alignments";
  } 
  else if ($genome == 'Prunus persica Whole Genome Assembly v2.0 & Annotation v2.1 (v2.0.a1)') {
    $url = "http://www.rosaceae.org/gb/gbrowse/prunus_persica_v2.0.a1?name=$loc&enable=NCBI%20Sequence%20Alignments";  
  } 
  else if ($genome == 'Malus x domestica Whole Genome v1.0p Assembly & Annotation') {
      $url = "http://www.rosaceae.org/gb/gbrowse/malus_x_domestica_v1.0-primary?name=$loc&enable=NCBI%20Sequence%20Alignments";
  } 
  else if($genome == 'Malus x domestica Whole Genome v1.0 Assembly & Annotation') {
      $url = "http://www.rosaceae.org/gb/gbrowse/malus_x_domestica?name=$loc&enable=NCBI%20Sequence%20Alignments";
  } 
  else if ($genome == 'Pyrus communis Genome v1.0 Draft Assembly & Annotation') {
    $url = "http://www.rosaceae.org/gb/gbrowse/pyrus_communis_v1.0?name=$loc&enable=NCBI%20Sequence%20Alignments";
  } 
  else if ($genome == 'Rubus occidentalis Whole Genome Assembly v1.0 & Annotation v1') {
    $url = "http://www.rosaceae.org/gb/gbrowse/rubus_occidentalis_v1.0.a1?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  return chado_search_link_url ($url);
}
/*************************************************************
 * AJAX callbacks
*/
// User defined: Populating the landmark for selected organism
function chado_search_snp_marker_search_ajax_location ($val) {
  $sql = "SELECT distinct landmark, CASE WHEN regexp_replace(landmark, E'\\\D','','g') = '' THEN 999999 ELSE regexp_replace(landmark, E'\\\D','','g')::numeric END AS lnumber FROM {chado_search_snp_marker_search} WHERE genome = :genome ORDER BY lnumber";
  return chado_search_bind_dynamic_select(array(':genome' => $val), 'genome', $sql);
}