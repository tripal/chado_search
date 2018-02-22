<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_snp_marker_search_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('nearby_marker_tabs')
      ->items(array('/search/markers' => 'Marker Search', '/search/snp_markers' => 'SNP Marker Search', '/search/nearby_markers' => 'Search Nearby Markers'))
  );

  // Search by Name
  $form->addTextFilter(
      Set::textFilter()
      ->id('snp_uniquename')
      ->title('SNP Name')
      ->labelWidth(120)
  );
  $form->addFile(
      Set::file()
      ->id('feature_name_file_inline')
      ->labelWidth(1)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('array_name')
      ->column('array_name')
      ->table('chado_search_snp_marker_search')
      ->cache(TRUE)
      ->title('Array Name')
      ->labelWidth(120)
      ->newLine()
  );
  // Restricted by Location
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('genome')
      ->title('Genome')
      ->column('genome')
      ->table('chado_search_snp_marker_search')
      ->disable(array('Malus x domestica Whole Genome v1.0 Assembly & Annotation'))
      ->cache(TRUE)
      ->newLine()
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('location')
      ->title('Chr/Scaffold')
      ->dependOnId('genome')
      ->callback('chado_search_snp_marker_search_ajax_location')
      ->labelWidth(120)
  );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('fmin')
      ->title("between")
      ->id2('fmax')
      ->title2("and")
      ->labelWidth2(50)
      ->size(10)
  );
  $form->addMarkup(
      Set::markup()
      ->id('location_unit')
      ->text("<strong>bp</strong>")
      ->newLine()
  );
  $form->addSubmit();
  $form->addReset();
  $desc =
  'Search for SNP markers in GDR.
     <b>| ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('top_level')
      ->startWidget('snp_uniquename')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_snp_marker_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_snp_marker_search_base_query();
  // Add conditions
  $where [] = Sql::textFilterOnMultipleColumns('snp_uniquename', $form_state, array('snp_uniquename', 'alias'));
  $where [] = Sql::fileOnMultipleColumns('feature_name_file_inline', array('snp_uniquename', 'alias'));
  $where [] = Sql::selectFilter('array_name', $form_state, 'array_name');
  $where [] = Sql::selectFilter('genome', $form_state, 'genome');
  $where [] = Sql::selectFilter('location', $form_state, 'landmark');
  $where [] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_snp_marker_search_table_definition')
    ->rewriteCols('alias=chado_search_snp_marker_search_rewrite_column_alias')
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_snp_marker_search_base_query() {
  $query = "SELECT * FROM {chado_search_snp_marker_search}";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_snp_marker_search_table_definition () {
  $headers = array(      
      'snp_name:s:chado_search_link_feature:snp_feature_id' => 'Name',
      'array_name:s' => 'SNP Array Name',
      'array_id:s' => 'SNP Array ID',
      'alias:s' => 'Alias',
      'allele:s' => 'Allele',
      'location:s:chado_search_link_jbrowse:landmark_feature_id,location' => 'Location',
      'flanking_sequence:s' => 'Flanking Sequence'
  );
  return $headers;
}

function chado_search_snp_marker_search_rewrite_column_alias ($val) {
  return str_replace(':::' , '. ', $val);
}

/*************************************************************
 * AJAX callbacks
*/
// User defined: Populating the landmark for selected organism
function chado_search_snp_marker_search_ajax_location ($val) {
  $sql = "SELECT distinct landmark FROM {chado_search_snp_marker_search} WHERE genome = :genome ORDER BY landmark";
  return chado_search_bind_dynamic_select(array(':genome' => $val), 'landmark', $sql);
}
