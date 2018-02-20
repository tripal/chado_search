<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_germplasm_search_by_pedigree_form ($form) {
  $form->addTabs(
  Set::tab()
      ->id('germplasm_search_tabs')
      ->items(array('/find/germplasm' => 'Name', '/find/germplasm/collection' => 'Collection', '/find/germplasm/pedigree' => 'Pedigree', '/find/germplasm/country' => 'Country', '/find/germplasm/image' => 'Image'))
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('pedigree')
      ->title('Pedigree')
  );
  $form->addMarkup(
      Set::markup()
      ->id('pedigree_example')
      ->text('(e.g. D*90)')
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('germplasm_search_by_pedigree')
      ->startWidget('pedigree')
      ->endWidget('reset')
      ->description("Search germplasm by pedigree. Wild card (*) can be used to match any word.")
  );
  return $form;
}

// Submit the form
function chado_search_germplasm_search_by_pedigree_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_germplasm_search_by_pedigree}";
  // Add conditions
  $where [0] = Sql::textFilter('pedigree', $form_state, 'pedigree', FALSE);
  if ($form_state['values']['pedigree_op'] != 'exactly') {
    $where [0] = str_replace('*', '%', $where[0]);
  }
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_germplasm_search_by_pedigree_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_germplasm_search_by_pedigree_table_definition () {
  $headers = array(      
    'uniquename:s:chado_search_link_stock:stock_id' => 'Germplasm',
    'pedigree:s' => 'Pedigree'
  );
  return $headers;
}
