<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_mapped_sequence_by_genome_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('genome')
      ->title('Genome Group')
      ->column('genome')
      ->table('chado_search_mapped_sequence')
      ->labelWidth(120)
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('mapped_sequence_by_genome_by_name')
      ->startWidget('genome')
      ->endWidget('reset')
  );
  
  return $form;
}

// Submit the form
function chado_search_mapped_sequence_by_genome_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_mapped_sequence}";
  // Add conditions
  $where [] = Sql::selectFilter('genome', $form_state, 'genome');
  //$groupby = 'marker_feature_id:chado_search_mapped_sequence_by_genome';
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_mapped_sequence_by_genome_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_mapped_sequence_by_genome_table_definition () {
  $headers = array(
          'genome:s' => 'Genome Group',
      'featuremap:s:chado_search_mapped_sequence_by_genome_link_featuremap:featuremap_id' => 'Map',
      'linkage_group:s' => 'Linkage Group',
      'locus_start:s' => 'Locus Start',
          'locus_stop:s' => 'Locus Stop',
          'locus_name:s' => 'Locus',
          'marker_name:s:chado_search_mapped_sequence_by_genome_link_feature:marker_feature_id' => 'Marker',
      'sequence_name:s:chado_search_mapped_sequence_by_genome_link_feature:sequence_feature_id' => 'Sequence',
  );
  return $headers;
}

// Define call back to link the feature to its  node for the result table
function chado_search_mapped_sequence_by_genome_link_feature ($feature_id) {
  return chado_search_link_entity('feature', $feature_id);
}

// Define call back to link the featuremap to its  node for the result table
function chado_search_mapped_sequence_by_genome_link_featuremap ($featuremap_id) {
  return chado_search_link_entity('featuremap', $featuremap_id);
}

/*************************************************************
 * AJAX callbacks
*/
// Downloading file ajax callback
function chado_search_mapped_sequence_by_genome_download_fasta_definition () {
  return 'marker_feature_id';
}
