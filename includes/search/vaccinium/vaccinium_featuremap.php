<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_featuremap_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_featuremap')
      ->multiple(TRUE)
  );
  $form->addSubmit();
  $form->addReset();
  return $form;
}

// Submit the form
function chado_search_featuremap_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_featuremap}";
  $where = array();
  $where [0] = Sql::selectFilter('organism', $form_state, 'organism');
  if (!$form_state['values']['organism']) {
    $genus = key_exists('genus', $_GET) ? check_plain($_GET['genus']) : NULL;
    $species = key_exists('species', $_GET) ? check_plain($_GET['species']) : NULL;
    $organism = key_exists('organism', $_GET) ? check_plain($_GET['organism']) : NULL;
    if ($genus) {
      $where [1] = "genus = '$genus'";
    }
    if ($species) {
      $where [2] = "species = '$species'";
    }
    if ($organism) {
      $where [3] = "organism = '$organism'";
    }
  }
  $groupby = "featuremap,maternal_stock_uniquename,paternal_stock_uniquename,pop_size,pop_type,organism:chado_search_featuremap";
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_featuremap_table_definition')
    ->groupby($groupby)
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_featuremap_table_definition () {
  $headers = array(
      'featuremap:s:chado_search_link_featuremap:featuremap_id' => 'Map Name',
      'maternal_stock_uniquename:s:chado_search_link_stock:maternal_stock_id' => 'Maternal Parent',
      'paternal_stock_uniquename:s:chado_search_link_stock:paternal_stock_id' => 'Paternal Parent',
      'pop_size:s' => 'Pop Size',
      'pop_type:s' => 'Pop Type',
      'organism:s:chado_search_link_organism:organism_id' => 'Species',
  );
  return $headers;
}