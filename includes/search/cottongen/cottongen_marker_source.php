<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_marker_source_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('marker_source_tabs')
      ->items(array('/find/markers' => 'Advanced Marker Search', '/find/marker/source' => 'Marker Source', '/find/snp_markers' => 'SNP Marker Search', '/find/nearby_markers' => 'Nearby Markers', '/find/qtl_nearby_markers' => 'QTL Nearby Markers'))
      );
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
      ->cache(TRUE)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('src_germplasm')
      ->title('Source Germplasm')
      ->column('stock_uniquename')
      ->table('chado_search_marker_source')
      ->labelWidth(160)
      ->cache(TRUE)
      ->newLine()
  );
  // Search by Name
  $form->addTextFilter(
      Set::textFilter()
      ->id('marker_uniquename')
      ->title('Marker Name')
      ->labelWidth(160)
      ->newLine()
  );
  $form->addFile(
      Set::file()
      ->id('marker_uniquename_file')
      ->title("File Upload")
      ->description("Provide marker names in a file. Separate each name by a new line.")
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
  $where [] = Sql::textFilter('src_uniquename', $form_state, 'src_uniquename');
  $where [] = Sql::selectFilter('src_type', $form_state, 'src_type');
  $where [] = Sql::selectFilter('src_germplasm', $form_state, 'stock_uniquename');
  $where [] = Sql::textFilterOnMultipleColumns('marker_uniquename', $form_state, array('marker_uniquename', 'alias'));
  $where [] = Sql::fileOnMultipleColumns('marker_uniquename_file', array('marker_uniquename', 'alias'), FALSE, TRUE);
  $where [] = Sql::selectFilter('marker_type', $form_state, 'marker_type');
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
      'marker_uniquename:s:chado_search_link_feature:marker_feature_id' => 'Marker Name',
      'alias:s' => 'Alias',
      'marker_type:s' => 'Type',
      'src_uniquename:s:chado_search_link_feature:src_feature_id' => 'Source Sequence',
      'src_type:s' => 'Source Molecule Type',
      'library_name:s:chado_search_link_library:library_id' => 'DNA Library',
      'stock_uniquename:s:chado_search_link_stock:stock_id' => 'Source Germplasm',
      'organism:s:chado_search_link_organism:organism_id' => 'Source Species',
  );
  return $headers;
}
