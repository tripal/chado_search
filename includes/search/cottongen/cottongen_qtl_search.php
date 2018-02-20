<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_qtl_search_form ($form) {
  $form->addTextFilter(
      Set::textFilter()
      ->id('trait_name')
      ->title('Trait Name')
      ->labelWidth(130)
  );
  $form->addMarkup(
      Set::markup()
      ->id('trait_name_example')
      ->text('(e.g. seed index, trichome density)')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('published_symbol')
      ->title('Published Symbol')
      ->labelWidth(130)
  );
  $form->addMarkup(
      Set::markup()
      ->id('published_symbol_example')
      ->text('(e.g. FM, 2.5%Lf)')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('qtl_label')
      ->title('QTL Label')
      ->labelWidth(130)
  );
  $form->addMarkup(
      Set::markup()
      ->id('qtl_label_example')
      ->text('(e.g.  qSL2.5, qFEL)')
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('qtl_search')
      ->startWidget('trait_name')
      ->endWidget('reset')
      ->description("Search QTLs by any combination of species, trait category, trait name, published symbol or label.")
  );
  return $form;
}

// Submit the form
function chado_search_qtl_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_qtl_search_base_query();
  // Add conditions
  $where[0] = Sql::textFilter('trait_name', $form_state, 'trait');
  $where[1] = Sql::textFilter('published_symbol', $form_state, 'symbol');
  $where[2] = Sql::textFilter('qtl_label', $form_state, 'qtl');
  $where[100] ="type = 'QTL'";
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_qtl_search_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_qtl_search_base_query() {
  $query = 
    "SELECT * FROM {chado_search_qtl_search}";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_qtl_search_table_definition () {
  $headers = array(      
    'qtl:s:chado_search_link_feature:feature_id' => 'Label',
    'trait:s' => 'Trait Name',
    'symbol:s' => 'Published Symbol',
    'map:s:chado_search_link_featuremap:featuremap_id' => 'Map'
  );
  return $headers;
}

// Define the download table
function chado_search_qtl_search_download_definition () {
  $headers = array(
      'type' => 'Type',
      'qtl' => 'Label',
      'symbol' => 'Published Symbol',
      'trait' => 'Trait Name',
      'category' => 'Trait Catogory',
      'study' => 'Study',
      'population' => 'Population',
      'col_marker_uniquename' => 'Colocalizing marker',
      'neighbor_marker_uniquename' => 'Neighboring marker',
      'map' => 'Map',
      'ad_ratio' => 'AD ratio',
      'r2' => 'R2',      
      'organism' => 'Species',
      'reference' => 'Publication'
  );
  return $headers;
}
