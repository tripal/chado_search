<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_species_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('genome')
      ->title('Genome Group')
      ->column('genome')
      ->table('chado_search_species')
      ->labelWidth(120)
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('species')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_species')
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('species_summary')
      ->startWidget('genome')
      ->endWidget('reset')
  );
  return $form;
}

// Submit the form
function chado_search_species_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_species}";
  $where [0] = Sql::selectFilter('genome', $form_state, 'genome');
  $where [1] = Sql::selectFilter('species', $form_state, 'organism');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_species_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_species_table_definition () {
  $headers = array(
      'organism:s:chado_search_species_link_organism:organism_id' => 'Species',
      'genome:s' => 'Genome Group',
      'haploid_chromosome_number:s' => 'Haploid Chromosome Number',
      'geographic_origin:s' => 'Geographic Origin',
      'num_germplasm:s' => 'Num Germplasm',
      'num_sequences:s' => 'Num Sequences',
      'num_libraries:s' => 'Num Libraries'
  );
  return $headers;
}

// Define call back to link the species to its  node for the result table
function chado_search_species_link_organism ($organism_id) {
  $nid = chado_get_nid_from_id('organism', $organism_id);
  return chado_search_link_node ($nid);
}