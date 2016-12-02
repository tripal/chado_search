<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_germplasm_search_form ($form) {
  //$form->addTabs('germplasm_search_tabs', array('/find/germplasms' => 'Name', '/find/germplasms/collection' => 'Collection', '/find/germplasms/pedigree' => 'Pedigree', '/find/germplasms/country' => 'Country'));
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_germplasm_search')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('stock_uniquename')
      ->title('Name')
  );
  $form->addFile(
      Set::file()
      ->id('stock_uniquename_file')
      ->title("File Upload")
      ->description("Provide germplasm names in a file. Separate each name by a new line.")
  );
  $form->addCustomOutput (
      Set::customOutput()
      ->id('custom_output')
      ->options(chado_search_germplasm_search_table_definition())
      );
  $form->addSubmit();
  $form->addReset();
  $desc =
  'Search germplasm by name or alias. Wild card (*) can be used to match any word.
     <b>| ' . l('Short video tutorial', 'https://youtu.be/oqiuSI99mMg', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', '/UserManual') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('germplasm_search')
      ->startWidget('organism')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_germplasm_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_germplasm_search_base_query();
  // Add conditions
  $where [0] = Sql::textFilterOnMultipleColumns('stock_uniquename', $form_state, array('uniquename', 'alias'), FALSE, 'stock_id:chado_search_germplasm_search');
  if ($form_state['values']['stock_uniquename_op'] != 'exactly') {
    $where [0] = str_replace('*', '%', $where[0]);
  }
  $where [1] = Sql::fileOnMultipleColumns('stock_uniquename_file', array('uniquename', 'alias'), FALSE, FALSE, 'stock_id:chado_search_germplasm_search');
  $where [1] = str_replace('*', '%', $where[1]);
  $where [2] = Sql::selectFilter('organism', $form_state, 'organism');
  $groupby = "stock_id:chado_search_germplasm_search";
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_germplasm_search_table_definition')
    ->groupby($groupby)
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_germplasm_search_base_query() {
  $query = 
    "SELECT 
       stock_id,
       string_agg(
         distinct (
           CASE 
             WHEN urlprefix <> '' AND urlprefix IS NOT NULL AND strpos (accession, 'PI') = 1
             THEN
               '<a href=\"' || urlprefix || accession || '\">' || accession || '</a>&nbsp;&nbsp; '
             ELSE
               ''
             END
         ), ''
       )
      as links,
      string_agg(
         distinct (
           CASE 
             WHEN urlprefix <> '' AND urlprefix IS NOT NULL AND strpos (accession, 'PI') = 1
             THEN
               accession || '. '
             ELSE
               ''
             END
         ), ''
       )
      as links_for_download,
      * 
     FROM {chado_search_germplasm_search}";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_germplasm_search_table_definition () {
  $headers = array(      
    'uniquename:s:chado_search_germplasm_search_link_stock:stock_id' => 'Germplasm',
    'organism:s:chado_search_germplasm_search_link_organism:organism_id' => 'Species',
    'alias:s' => 'Aliases'
  );
  return $headers;
}
// Define call back to link organism
function chado_search_germplasm_search_link_organism ($organism_id) {
  $nid = chado_get_nid_from_id('organism', $organism_id);
  return chado_search_link_node ($nid);
}
// Define call back to link the stoc
function chado_search_germplasm_search_link_stock ($stock_id) {
  $nid = chado_get_nid_from_id('stock', $stock_id);
  return chado_search_link_node ($nid);
}