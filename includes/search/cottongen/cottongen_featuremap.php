<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_featuremap_form ($form) {
  $form->addSubmit();
  $form->addReset();
  return $form;
}

// Submit the form
function chado_search_featuremap_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_featuremap}";
  $where = array();
  $genus = key_exists('genus', $_GET) ? check_plain($_GET['genus']) : NULL;
  $species = key_exists('species', $_GET) ? check_plain($_GET['species']) : NULL;
  $organism = key_exists('organism', $_GET) ? check_plain($_GET['organism']) : NULL;
  if ($genus) {
    $where [1] = "genus = '$genus'";
  }
  if ($species) {
    $where [2] = "species = '$species'";
  }

  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_featuremap_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_featuremap_table_definition () {
  $headers = array(
      'featuremap:s:chado_search_featuremap_link_featuremap:featuremap_id' => 'Map Name',
      'genome:s' => 'Genome Group',
      'maternal_stock_uniquename:s:chado_search_featuremap_link_parent:maternal_stock_id' => 'Maternal Parent',
      'paternal_stock_uniquename:s:chado_search_featuremap_link_parent:paternal_stock_id' => 'Paternal Parent',
      'pop_size:s' => 'Pop Size',
      'pop_type:s' => 'Pop Type',
      'num_of_lg:s' => 'Num LG',
      'num_of_loci:s' => 'Num Loci',
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for the result table
function chado_search_featuremap_link_featuremap ($featuremap_id) {
  return chado_search_link_entity('featuremap', $featuremap_id);
}

// Define call back to link the project to its  node for the result table
function chado_search_featuremap_link_parent ($stock_id) {
  return chado_search_link_entity('stock', $stock_id);
}
