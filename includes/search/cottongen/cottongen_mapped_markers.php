<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_mapped_markers_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('marker_type')
      ->title('Marker Type')
      ->column('marker_type')
      ->table('chado_search_mapped_markers')
      ->newLine()
  );
  $form->addTextareaFilter(
      Set::textAreaFilter()
      ->id('marker_uniquename')
      ->title('Marker Name')
      ->columns(80)
      ->newLine()
  );
  $form->addFile(
      Set::file()
      ->id('marker_uniquename_file')
      ->title("File Upload")
      ->description("Provide marker names separated by a new line.")
  );
  
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('mapped_markers_by_name')
      ->startWidget('marker_type')
      ->endWidget('reset')
  );  
  return $form;
}

// Submit the form
function chado_search_mapped_markers_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_mapped_markers}";
  // Add conditions
  $where [0] = Sql::textareaFilter('marker_uniquename', $form_state, 'marker_uniquename');
  $where [1] = Sql::file('marker_uniquename_file', 'marker_uniquename');
  $where [2] = Sql::selectFilter('marker_type', $form_state, 'marker_type');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_mapped_markers_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_mapped_markers_table_definition () {
  $headers = array(      
      'marker_uniquename:s:chado_search_mapped_markers_link_feature:marker_feature_id' => 'Name',
      'marker_type:s' => 'Type',
      'locus_uniquename:s' => 'Locus',
      'map_name:s:chado_search_mapped_markers_link_featuremap:featuremap_id' => 'Map',
          'lg_uniquename:s' => 'Linkage Group',
      'chr_number:s' => 'Chr number',
      'start:s' => 'Start',
      'stop:s' => 'Stop'
  );
  return $headers;
}

// Define call back to link the feature to its  node for the result table
function chado_search_mapped_markers_link_feature ($feature_id) {
  $nid = chado_get_nid_from_id('feature', $feature_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the featuremap to its  node for the result table
function chado_search_mapped_markers_link_featuremap ($featuremap_id) {
  $nid = chado_get_nid_from_id('featuremap', $featuremap_id);
  return chado_search_link_node ($nid);
}

// Downloading Definition if different from the Table Definition
function chado_search_mapped_markers_download_definition () {
  $headers = array(
      'marker_uniquename' => 'Name',
      'marker_type' => 'Type',
      'locus_uniquename' => 'Locus',
      'map_name' => 'Map',
          'lg_uniquename' => 'Linkage Group',
      'chr_number' => 'Chr number',
      'start' => 'Start',
      'stop' => 'Stop',
      'seq_uniquename' => 'Sequence'
  );
  return $headers;
}

/*************************************************************
 * AJAX callbacks
*/
// Downloading file ajax callback
function chado_search_mapped_markers_download_fasta_definition () {
  return 'marker_feature_id';
}