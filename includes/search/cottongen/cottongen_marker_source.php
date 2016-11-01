<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_marker_source_form ($form) {

  $form->addTextFilter(
      Set::textFilter()
      ->id('src_uniquename')
      ->title('Source Sequence')
      ->labelWidth(160)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('src_type')
      ->title('Source Molecule Type')
      ->column('src_type')
      ->table('chado_search_marker_source')
      ->labelWidth(160)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('src_germplasm')
      ->title('Source Germplasm')
      ->column('stock_uniquename')
      ->table('chado_search_marker_source')
      ->labelWidth(160)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('marker_type')
      ->title('Marker Type')
      ->column('marker_type')
      ->table('chado_search_marker_source')
      ->labelWidth(160)
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('marker_source')
      ->startWidget('src_uniquename')
      ->endWidget('reset')
  );

  return $form;
}

// Submit the form
function chado_search_marker_source_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_marker_source}";
  // Add conditions
  $where [0] = Sql::textFilter('src_uniquename', $form_state, 'src_uniquename');
  $where [1] = Sql::selectFilter('src_type', $form_state, 'src_type');
  $where [2] = Sql::selectFilter('src_germplasm', $form_state, 'stock_uniquename');
  $where [3] = Sql::selectFilter('marker_type', $form_state, 'marker_type');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_marker_source_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_marker_source_table_definition () {
  $headers = array(      
      'marker_uniquename:s:chado_search_marker_source_link_feature:marker_feature_id' => 'Marker Name',
      'marker_type:s' => 'Type',
      'src_uniquename:s:chado_search_marker_source_link_feature:src_feature_id' => 'Source Sequence',
      'src_type:s' => 'Source Molecule Type',
      'library_name:s:chado_search_marker_source_link_library:library_id' => 'DNA Library',
      'stock_uniquename:s:chado_search_marker_source_link_stock:stock_id' => 'Source Germplasm',
          'organism:s:chado_search_marker_source_link_organism:organism_id' => 'Source Species',
  );
  return $headers;
}

/*************************************************************
 * Build the search result table
*/

// Define call back to link the featuremap to its  node for result table
function chado_search_marker_source_link_feature ($feature_id) {
  $nid = chado_get_nid_from_id('feature', $feature_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the organism to its  node for result table
function chado_search_marker_source_link_organism ($organism_id) {
  $nid = chado_get_nid_from_id('organism', $organism_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the featuremap to its  node for result table
function chado_search_marker_source_link_library ($library_id) {
  $nid = chado_get_nid_from_id('library', $library_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the organism to its  node for result table
function chado_search_marker_source_link_stock ($stock_id) {
  $nid = chado_get_nid_from_id('stock', $stock_id);
  return chado_search_link_node ($nid);
}