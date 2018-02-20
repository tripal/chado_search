<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_germplasm_search_by_image_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('germplasm_search_tabs')
      ->items(array('/find/germplasm' => 'Name', '/find/germplasm/collection' => 'Collection', '/find/germplasm/pedigree' => 'Pedigree', '/find/germplasm/country' => 'Country', '/find/germplasm/image' => 'Image'))
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('stock_uniquename')
      ->title('Name')
  );
  $form->addMarkup(
      Set::markup()
      ->id('stock_uniquename_example')
      ->text('(e.g. tm1)')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('legend')
      ->title('Legend')
  );
  $form->addMarkup(
      Set::markup()
      ->id('legend_example')
      ->text('(e.g. flower, boll open, A2-, GB-)')
  );
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('germplasm_search_by_image')
      ->startWidget('stock_uniquename')
      ->endWidget('reset')
      ->description("Search germplasm with images. Wild card (*) can be used to match the germplasm name.")
  );
  return $form;
}

// Submit the form
function chado_search_germplasm_search_by_image_form_submit ($form, &$form_state) {
  // Get base sql
  $icons = url('sites/default/files/bulk_data/www.cottongen.org/cotton_photo/germplasm/icon/icon-');
  $imgs = url('sites/default/files/bulk_data/www.cottongen.org/cotton_photo/germplasm/image/');
  $sql = "SELECT *, '<a href=' || '$imgs' || image_uri || ' target=_blank><img src=' || '$icons' || image_uri || '></a>' AS image FROM {chado_search_germplasm_search_by_image}";
  // Add conditions
 $where [0] = Sql::textFilterOnMultipleColumns('stock_uniquename', $form_state, array('uniquename', 'alias'), FALSE, 'stock_id:chado_search_germplasm_search');
  if ($form_state['values']['stock_uniquename_op'] != 'exactly') {
    $where [0] = str_replace('*', '%', $where[0]);
  }
  $where [1] = Sql::textFilter('legend', $form_state, 'legend');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_germplasm_search_by_image_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_germplasm_search_by_image_table_definition () {
  $headers = array(      
    'uniquename:s:chado_search_link_stock:stock_id' => 'Germplasm',
    'organism:s:chado_search_link_organism:organism_id' => 'Species',
    'legend:s' => 'Legend',
    'image' => 'Image'
  );
  return $headers;
}
function chado_search_germplasm_search_by_image_download_definition () {
  $headers = array(
    'uniquename' => 'Germplasm',
    'organism' => 'Species',
    'legend' => 'Legend',
    'image_uri' => 'Image'
  );
  return $headers;
}