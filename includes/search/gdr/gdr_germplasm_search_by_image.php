<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_germplasm_search_by_image_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('genus')
      ->title('Genus')
      ->column('genus')
      ->table('chado_search_germplasm_search_by_image')
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('species')
      ->title('Species')
      ->dependOnId('genus')
      ->callback('chado_search_germplasm_search_by_image_ajax_organism')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('stock_uniquename')
      ->title('Name')
  );
  $form->addMarkup(
      Set::markup()
      ->id('stock_uniquename_example')
      ->text('(e.g. wsu)')
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
      ->text('(e.g. golden, fuji)')
  );
  $form->addSubmit();
  $form->addReset();
  $desc =
  'Search germplasm by name or alias. Wild card (*) can be used to match any word.
     <b>| ' . l('Short video tutorial', 'https://www.youtube.com/watch?v=1LDE_f_lqbE', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', 'tutorial/germplasm_search') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('germplasm_search_by_image')
      ->startWidget('genus')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_germplasm_search_by_image_form_submit ($form, &$form_state) {
  // Get base sql
  $icons = url('sites/default/files/bulk_data/www.rosaceae.org/gdr_photo/germplasm/icon/icon-');
  $imgs = url('sites/default/files/bulk_data/www.rosaceae.org/gdr_photo/germplasm/image/');
  $sql = "SELECT *, '<a href=\"' || '$imgs' || image_uri || '\" target=_blank><img src=\"' || '$icons' || image_uri || '\"></a>' AS image FROM {chado_search_germplasm_search_by_image}";
  // Add conditions
 $where [0] = Sql::textFilterOnMultipleColumns('stock_uniquename', $form_state, array('uniquename', 'alias'), FALSE, 'stock_id:chado_search_germplasm_search');
  if ($form_state['values']['stock_uniquename_op'] != 'exactly') {
    $where [0] = str_replace('*', '%', $where[0]);
  }
  $where [1] = Sql::textFilter('legend', $form_state, 'legend');
  $where [2] = Sql::selectFilter('genus', $form_state, 'genus');
  $where [3] = Sql::selectFilter('species', $form_state, 'organism');
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

function chado_search_germplasm_search_by_image_ajax_organism ($val) {
  $sql = "SELECT organism FROM {chado_search_germplasm_search_by_image} WHERE genus = :genus GROUP BY organism ORDER BY organism";
  return chado_search_bind_dynamic_select(array(':genus' => $val), 'organism', $sql);
}
