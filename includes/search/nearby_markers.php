<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_nearby_markers_form ($form) {  
  $form->addTabs(
      Set::tab()
      ->id('nearby_marker_tabs')
      ->items(array('/find/markers' => 'Advanced Marker Search', '/find/nearby_markers' => 'Search Nearby Markers'))
  );
  // Basic
  $form->addLabeledFilter(
      Set::labeledFilter()->id('nearby_marker_locus')
      ->title('Locus')
  );
  $form->addLabeledFilter(
      Set::labeledFilter()
      ->id('nearby_marker_distance')
      ->title('Distance')
  );
  $form->addMarkup(
      Set::markup()->id('nearby_marker_unit')
      ->text("<strong>cM</strong>")
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('nearby_markers_fieldset')
      ->startWidget('nearby_marker_locus')
      ->endWidget('reset')
  );
  return $form;
}
// Validate the form
function chado_search_nearby_markers_form_validate ($form, &$form_state) {
  $locus = $form_state['values']['nearby_marker_locus'];
  if (!$locus) {
    form_set_error('', t('Locus name is required.'));
  }
  $distance = $form_state['values']['nearby_marker_distance'];
  if (!is_numeric($distance)) {
    form_set_error('', t('Please input a number for the distance.'));
  }
}
// Submit the form
function chado_search_nearby_markers_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_nearby_markers_base_query();
  // Add conditions
  $sql .= " AND " . Sql::LabeledFilter('nearby_marker_locus', $form_state, 'F.name');
  $distance = $form_state['values']['nearby_marker_distance'];
  $sql = "
      SELECT * FROM (" . $sql . ") A 
      INNER JOIN (
      SELECT map_feature_id, feature_id AS nearby_feature_id, (select name FROM {feature} where feature_id = FP.feature_id) AS nearby_marker , round(cast(START.value as numeric), 2) AS nearby_start 
      FROM {featurepos} FP 
      INNER JOIN (SELECT featurepos_id, value FROM {featureposprop} FPP WHERE type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'start' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'MAIN'))) START ON START.featurepos_id = FP.featurepos_id
      ) B ON A.map_feature_id = B.map_feature_id
      WHERE A.feature_id <> B.nearby_feature_id AND abs(B.nearby_start - A.start) <= $distance";

  Set::result()
    ->sql($sql)
    ->tableDefinitionCallback('chado_search_nearby_markers_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_nearby_markers_base_query() {
  $query = "
      SELECT 
      featuremap_id,
      (SELECT name FROM {featuremap} WHERE featuremap_id = FP.featuremap_id) AS featuremap,
      FP.feature_id,
      F.name AS locus,
      FP.map_feature_id,
      (SELECT name FROM {feature} WHERE feature_id = FP.map_feature_id) AS linkage_group,
      round(cast(START.value as numeric), 2) AS start
      FROM {featurepos} FP
      INNER JOIN (SELECT featurepos_id, value FROM {featureposprop} FPP WHERE type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'start' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'MAIN'))) START ON START.featurepos_id = FP.featurepos_id
      INNER JOIN {feature} F ON F.feature_id = FP.feature_id
      WHERE F.type_id = (SELECT cvterm_id FROM {cvterm} V WHERE name = 'marker_locus' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'sequence'))";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_nearby_markers_table_definition () {
  $headers = array(      
    'locus:u:chado_search_nearby_markers_link_feature:feature_id' => 'Locus',
    'featuremap:s:chado_search_nearby_markers_link_featuremap:featuremap_id' => 'Map',
    'linkage_group:s' => 'Linkage Group',
    'start:s' => 'Position',
    'nearby_marker:s:chado_search_nearby_markers_link_feature:nearby_feature_id' => 'Neighbor',
    'nearby_start:s' => 'Position'
  );
  return $headers;
}

// Define call back to link the feature to its  node for the result table
function chado_search_nearby_markers_link_feature ($feature_id) {
  // Convert the feature_id of marker_locus to genetic_marker
  $fid = chado_query("SELECT object_id FROM {feature_relationship} FR WHERE subject_id = $feature_id AND type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'instance_of' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'relationship'))")->fetchField();
  return chado_search_link_entity('feature', $fid);

}

// Define call back to link the featuremap to its  node for the result table
function chado_search_nearby_markers_link_featuremap ($featuremap_id) {
  return chado_search_link_entity('featuremap', $featuremap_id);

}
