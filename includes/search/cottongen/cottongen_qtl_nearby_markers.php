<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_qtl_nearby_markers_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('nearby_marker_tabs')
      ->items(array('/find/markers' => 'Advanced Marker Search', '/find/snp_markers' => 'SNP Marker Search', '/find/nearby_markers' => 'Nearby Markers', '/find/qtl_nearby_markers' => 'QTL Nearby Markers'))
  );
  $form->addLabeledFilter(
      Set::labeledFilter()
      ->id('nearby_qtl')
      ->title('QTL')
      ->newLine()
  );
  $form->addLabeledFilter(
      Set::labeledFilter()
      ->id('qtl_nearby_marker_distance')
      ->title('Distance')
  );
  $form->addMarkup(
      Set::markup()
      ->id('qtl_nearby_marker_unit')
      ->text("<strong>cM</strong>")
  );
  
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('qtl_nearby_markers_fieldset')
      ->startWidget('nearby_qtl')
      ->endWidget('reset')
      ->description("Wild card (*) can be used to match any QTL label.")
  );
  return $form;
}
// Validate the form
function chado_search_qtl_nearby_markers_form_validate ($form, &$form_state) {
  $locus = $form_state['values']['nearby_qtl'];
  if (!$locus) {
    form_set_error('', t('QTL is required.'));
  }
  $distance = $form_state['values']['qtl_nearby_marker_distance'];
  if (!is_numeric($distance)) {
    form_set_error('', t('Please input a number for the distance.'));
  }
}
// Submit the form
function chado_search_qtl_nearby_markers_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_qtl_nearby_markers_base_query();
  // Add conditions
  $locus = $form_state['values']['nearby_qtl'];
  $locus = str_replace("'", "''", $locus); // escape the single quote
  $sql .= " AND F.uniquename like '" . str_replace('*', '%', $locus) . "'";
  $distance = $form_state['values']['qtl_nearby_marker_distance'];
  $sql = "
      SELECT * FROM (" . $sql . ") A 
      INNER JOIN (
        SELECT 
          featuremap_id,
          map_feature_id, 
          feature_id AS nearby_feature_id, 
          (SELECT uniquename FROM feature where feature_id = FP.feature_id) AS nearby_marker , 
          round(cast(START.value as numeric), 2) AS nearby_start 
        FROM featurepos FP 
        INNER JOIN (
          SELECT featurepos_id, value 
          FROM featureposprop FPP 
          WHERE type_id = (
            SELECT cvterm_id FROM cvterm WHERE name = 'start' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))
        ) START ON START.featurepos_id = FP.featurepos_id
        WHERE (SELECT type_id FROM feature where feature_id = FP.feature_id) <> (SELECT cvterm_id FROM cvterm WHERE name = 'QTL' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence')) 
      ) B ON A.map_feature_id = B.map_feature_id
      WHERE A.feature_id <> B.nearby_feature_id AND (abs(B.nearby_start - A.start) <= $distance OR abs(B.nearby_start - A.stop) <= $distance)";

  Set::result()
    ->sql($sql)
    ->tableDefinitionCallback('chado_search_qtl_nearby_markers_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_qtl_nearby_markers_base_query() {
  $query = "
      SELECT 
      featuremap_id,
      (SELECT name FROM {featuremap} WHERE featuremap_id = FP.featuremap_id) AS featuremap,
      FP.feature_id,
      F.uniquename AS locus,
      FP.map_feature_id,
      (SELECT name FROM {feature} WHERE feature_id = FP.map_feature_id) AS linkage_group,
      round(cast(START.value as numeric), 2) AS start,
      round(cast(STOP.value as numeric), 2) AS stop
      FROM {featurepos} FP
      INNER JOIN (SELECT featurepos_id, value FROM {featureposprop} FPP WHERE type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'start' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'MAIN'))) START ON START.featurepos_id = FP.featurepos_id
      INNER JOIN feature F ON F.feature_id = FP.feature_id
      INNER JOIN (SELECT featurepos_id, value FROM {featureposprop} FPP WHERE type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'stop' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'MAIN'))) STOP ON STOP.featurepos_id = FP.featurepos_id
      WHERE F.type_id = (SELECT cvterm_id FROM {cvterm} V WHERE name = 'QTL' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'sequence'))";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_qtl_nearby_markers_table_definition () {
  $headers = array(      
    'locus:s:chado_search_link_feature:feature_id' => 'QTL',
    'featuremap:s:chado_search_link_featuremap:featuremap_id' => 'Map',
    'linkage_group:s' => 'Linkage Group',
    'start:s' => 'Start',
    'stop:s' => 'Stop',
    'nearby_marker:s:chado_search_link_genetic_marker:nearby_feature_id' => 'Neighbor',
    'nearby_start:s' => 'Position'
  );
  return $headers;
}

/*************************************************************
 * AJAX callbacks
*/
// Downloading file ajax callback
function chado_search_qtl_nearby_markers_download_fasta_definition () {
  return 'marker_feature_id';
}
