<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_qtl_search_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('type')
      ->title('Type')
      ->column('type')
      ->table('chado_search_qtl_search')
      ->multiple(TRUE)
      ->labelWidth(130)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('species')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_qtl_search')
      ->multiple(TRUE)
      ->labelWidth(130)
      ->newLine()
  );
/*   $form->addSelectFilter(
      Set::selectFilter()
      ->id('trait_category')
      ->title('Trait Category')
      ->column('category')
      ->table('chado_search_qtl_search')
      ->multiple(TRUE)
      ->labelWidth(130)
      ->newLine()
  ); */
  $form->addTextFilter(
      Set::textFilter()
      ->id('trait_name')
      ->title('Trait Name')
      ->labelWidth(130)
  );
  $form->addMarkup(
      Set::markup()
      ->id('trait_name_example')
      ->text('(e.g. self-incompatibility, chilling requirement or fruit weight)')
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
      ->text('(e.g. Pm1,Ls1, PPV-D or Skc)')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('qtl_label')
      ->title('QTL/MTL Label')
      ->labelWidth(130)
  );
  $form->addMarkup(
      Set::markup()
      ->id('qtl_label_example')
      ->text('(e.g. qFLWS.DE-chD10-2, qFBR.FD-chF7, qLFSZ.DE-chE15-9)')
      ->newLine()
  );
  $form->addSubmit();
  $form->addReset();
  $desc =
  'Search QTLs and/or MTLs (Mendelian Trait Loci) by any combination of species, trait category, trait name, published symbol or label.
     <b>| ' . l('Short video tutorial', 'https://www.youtube.com/watch?v=Cu42oH_PXvc', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', 'tutorial/QTL_search') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('qtl_search')
      ->startWidget('type')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_qtl_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_qtl_search_base_query();
  // Add conditions
  $where[0] = Sql::selectFilter('type', $form_state, 'type');
  $where[1] = Sql::selectFilter('species', $form_state, 'organism');
  //$where[2] = Sql::selectFilter('trait_category', $form_state, 'category');
  $where[3] = Sql::textFilter('trait_name', $form_state, 'trait');
  $where[4] = Sql::textFilter('published_symbol', $form_state, 'symbol');
  $where[5] = Sql::textFilter('qtl_label', $form_state, 'qtl');
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
    'type:s' => 'Type',
    'qtl:s:chado_search_qtl_search_link_qtl:feature_id' => 'Label',
    'trait:s' => 'Trait Name',
    'symbol:s' => 'Published Symbol',
    'map:s:chado_search_qtl_search_link_map:featuremap_id' => 'Map',
    'organism:s:chado_search_qtl_search_link_organism:organism_id' => 'Species'
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
// Define call back to link feature
function chado_search_qtl_search_link_qtl ($feature_id) {
  return chado_search_link_entity('feature', $feature_id);
}
// Define call back to link organism
function chado_search_qtl_search_link_organism ($organism_id) {
  return chado_search_link_entity('organism', $organism_id);
}
// Define call back to link featuremap
function chado_search_qtl_search_link_map ($featuremap_id) {
  return chado_search_link_entity('featuremap', $featuremap_id);
}
