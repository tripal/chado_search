<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_mapped_sequence_by_map_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('featuremap')
      ->title('Map Name')
      ->column('featuremap')
      ->table('chado_search_mapped_sequence')
  );
  
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('mapped_sequence_by_map_by_name')
      ->startWidget('featuremap')
      ->endWidget('reset')
  );
  
  return $form;
}

// Submit the form
function chado_search_mapped_sequence_by_map_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_mapped_sequence}";
  // Add conditions
  $where [] = Sql::selectFilter('featuremap', $form_state, 'featuremap');
  //$groupby = 'marker_feature_id:chado_search_mapped_sequence_by_map';
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_mapped_sequence_by_map_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_mapped_sequence_by_map_table_definition () {
  $headers = array(      
      'featuremap:s:chado_search_link_featuremap:featuremap_id' => 'Map',
      'linkage_group:s' => 'Linkage Group',
      'locus_start:s' => 'Locus Start',
      'locus_stop:s' => 'Locus Stop',
      'locus_name:s' => 'Locus',
      'marker_name:s:chado_search_link_feature:marker_feature_id' => 'Marker',
      'sequence_name:s:chado_search_link_feature:sequence_feature_id' => 'Sequence',
          
  );
  return $headers;
}

/*************************************************************
 * AJAX callbacks
*/
// Downloading file ajax callback
function chado_search_mapped_sequence_by_map_download_fasta_definition () {
  return 'marker_feature_id';
}
