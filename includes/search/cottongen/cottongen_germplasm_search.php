<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_germplasm_search_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('germplasm_search_tabs')
      ->items(array('/find/germplasm' => 'Name', '/find/germplasm/collection' => 'Collection', '/find/germplasm/pedigree' => 'Pedigree', '/find/germplasm/country' => 'Country', '/find/germplasm/image' => 'Image'))
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('stock_uniquename')
      ->title('Name')
      ->newLine()
  );
  $form->addFile(
      Set::file()
      ->id('stock_uniquename_file')
      ->title("File Upload")
      ->description("Provide germplasm names in a file. Separate each name by a new line.")
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('germplasm_search')
      ->startWidget('stock_uniquename')
      ->endWidget('reset')
      ->description("Search germplasm by name or alias. Wild card (*) can be used to match any word.")
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
             WHEN urlprefix <> '' AND urlprefix IS NOT NULL AND strpos (version, 'PI') = 1
             THEN
               '<a href=\"' || urlprefix || accession || '\">' || version || '</a>&nbsp;&nbsp; '
             ELSE
               ''
             END
         ), ''
       )
      as links,
      string_agg(
         distinct (
           CASE 
             WHEN urlprefix <> '' AND urlprefix IS NOT NULL AND strpos (version, 'PI') = 1
             THEN
               version || '. '
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
    'genome:s' => 'Genome',
    'links:s' => 'GRIN_ID',
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
