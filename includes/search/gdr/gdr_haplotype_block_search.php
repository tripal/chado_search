<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_haplotype_block_search_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_haplotype_block_search')
      ->cache(TRUE)
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('hb_uniquename')
      ->title('Name')
      ->newLine()
  );
  //$form->addMarkup('marker_example', "(e.g. Hi04e04, CPPCT016, UFFxa16H07)");  
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('genome')
      ->title('Genome')
      ->column('genome')
      ->table('chado_search_haplotype_block_search')
      ->newLine()
  );
   $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('location')
      ->title('Chr/scaffold')
      ->dependOnId('genome')
      ->callback('chado_search_haplotype_block_search_ajax_location')
  );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('fmin')
      ->title("between")
      ->id2('fmax')
      ->title2("and")
      ->size(10)
      ->labelWidth2(50)
  );
  $form->addMarkup(
      Set::markup()
      ->id('location_unit')
      ->text("<strong>bp</strong>")
  );
  //$form->addFieldset('restrict_by_location', 'Restrict by Location', 'genome', 'location_unit');
  
  $form->addSubmit();
  $form->addReset();
  $desc =
  'Search for haplotype blocks by entering species, name, species and/or their anchored genomic 
      location in the fields below. Go to <a href=/legacy/bt_search_haplotype/by_haplotype>haplotype 
      search pages</a> to search haplotype data by variety names or specific set of haplotype.
     <b>| ' . l('Short video tutorial', 'https://www.youtube.com/watch?v=XyEkfp_Lsno', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', 'tutorial/haplotype_block_search') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('haplotype_block_search')
      ->startWidget('organism')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_haplotype_block_search_form_submit ($form, &$form_state) {
  // Add conditions
  $where [] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [] = Sql::textFilter ('hb_uniquename', $form_state, 'haplotype_block');
  $where [] = Sql::selectFilter('genome', $form_state, 'genome');
  $where [] = Sql::selectFilter('location', $form_state, 'landmark');
  $where [] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
  
  $sql = "SELECT hb_feature_id, haplotype_block, landmark || ':' || fmin || '..' || fmax AS location FROM {chado_search_haplotype_block_search}";
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_haplotype_block_search_table_definition')
    ->execute($form, $form_state);
}


/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_haplotype_block_search_table_definition () {
  $headers = array(
      'haplotype_block:s:chado_search_link_feature:hb_feature_id' => 'Haplotype Block',
      'location:s' => 'Location',
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_haplotype_block_search_link_feature ($feature_id) {
  return chado_search_link_entity('feature', $feature_id);
}


/*************************************************************
 * AJAX callbacks
*/
// User defined: Populating the landmark for selected organism
function chado_search_haplotype_block_search_ajax_location ($val) {
  $sql = "SELECT distinct landmark, CASE WHEN regexp_replace(landmark, E'\\\D','','g') = '' THEN 999999 ELSE regexp_replace(landmark, E'\\\D','','g')::numeric END AS lnumber FROM {chado_search_haplotype_block_search} WHERE genome = :genome ORDER BY lnumber";
  return chado_search_bind_dynamic_select(array(':genome' => $val), 'landmark', $sql);
}
