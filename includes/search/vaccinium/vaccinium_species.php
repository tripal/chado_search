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
      ->id('genus')
      ->title('Genus')
      ->column('genus')
      ->table('chado_search_species')
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('species')
      ->title('Species')
      ->dependOnId('genus')
      ->callback('chado_search_species_ajax_species')
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('species_summary')
      ->startWidget('genus')
      ->endWidget('reset')
  );
  return $form;
}

// Submit the form
function chado_search_species_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_species}";
  $where [0] = Sql::selectFilter('genus', $form_state, 'genus');
  $where [1] = Sql::selectFilter('species', $form_state, 'species');
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
      'organism:s:chado_search_link_organism:organism_id' => 'Species',
      'num_germplasm:s' => 'Num Germplasm',
      'num_sequences:s' => 'Num Sequences',
      'num_libraries:s' => 'Num Libraries'
  );
  return $headers;
}

/*************************************************************
 * AJAX callbacks
 */
// User defined: Populating the landmark for selected organism
function chado_search_species_ajax_species ($val) {
  $sql = "SELECT distinct species FROM {chado_search_species} WHERE genus = :genus ORDER BY species";
  return chado_search_bind_dynamic_select(array(':genus' => $val), 'species', $sql);
}
