<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_germplasm_search_by_country_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('germplasm_search_tabs')
      ->items(array('/find/germplasm' => 'Name', '/find/germplasm/collection' => 'Collection', '/find/germplasm/pedigree' => 'Pedigree', '/find/germplasm/country' => 'Country', '/find/germplasm/image' => 'Image'))
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('country')
      ->title('Country')
      ->column('country')
      ->table('chado_search_germplasm_search_by_country')
      ->multiple(TRUE)
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('germplasm_search_by_country')
      ->startWidget('country')
      ->endWidget('reset')
      ->description("Search germplasm by country.")
  );
  return $form;
}

// Submit the form
function chado_search_germplasm_search_by_country_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_germplasm_search_by_country}";
  // Add conditions
  $where [0] = Sql::selectFilter('country', $form_state, 'country');

  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_germplasm_search_by_country_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_germplasm_search_by_country_table_definition () {
  $headers = array(      
    'uniquename:s:chado_search_germplasm_search_by_country_link_stock:stock_id' => 'Germplasm',
    'organism:s:chado_search_germplasm_search_by_country_link_organism:organism_id' => 'Species',
    'stock_type:s' => 'Stock Type',
    'country:s' => 'Country',
    'state:s' => 'State/Province'
  );
  return $headers;
}
// Define call back to link the stoc
function chado_search_germplasm_search_by_country_link_stock ($stock_id) {
  $nid = chado_get_nid_from_id('stock', $stock_id);
  return chado_search_link_node ($nid);
}
// Define call back to link organism
function chado_search_germplasm_search_by_country_link_organism ($organism_id) {
  $nid = chado_get_nid_from_id('organism', $organism_id);
  return chado_search_link_node ($nid);
}
