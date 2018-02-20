<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_sequence_search_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('genus')
      ->title('Genus')
      ->column('genus')
      ->disable(array('cybrid'))
      ->table('chado_search_sequence_search')
      ->cache(TRUE)
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('species')
      ->title('Species')
      ->dependOnId('genus')
      ->callback('chado_search_sequence_search_ajax_organism')
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('feature_type')
      ->title('Type')
      ->column('feature_type')
      ->table('chado_search_sequence_search')
      ->multiple(TRUE)
      ->newLine()
      ->cache(TRUE)
  );
  $icon = '/' . drupal_get_path('module', 'chado_search') . '/theme/images/question.gif';
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('analysis')
      ->title('Dataset <a href="/sequence_dataset_description"><img src="' . $icon . '"></a>')
      ->column('analysis_name')
      ->table('chado_search_sequence_search')
      ->multiple(TRUE)
      ->optGroupByPattern(array('Genbank Genes' => 'NCBI', 'Predicted Genes' => 'Genome|genome', 'Unigene' => 'Unigene|unigene', 'RefTrans' => 'RefTrans'))
      ->cache(TRUE)
      ->newLine()
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('location')
      ->title('Location')
      ->dependOnId('analysis')
      ->callback('chado_search_sequence_search_ajax_location')
  );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('fmin')
      ->title("between")
      ->id2('fmax')
      ->title2("and")
      ->labelWidth2(50)
      ->size(15)
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('feature_name')
      ->title('Name')
      ->newLine()
  );
  $form->addFile(
      Set::file()
      ->id('feature_name_file')
      ->title("File Upload")
      ->description("Provide sequence names in a file. Separate each name by a new line.")
      ->newLine()
  );
  $form->addSubmit();
  $form->addReset();
  $desc =
  'Search for sequences by entering names in the field below. Alternatively, you may upload a file of names. 
      You may also filter results by sequence type and the sequence source. To select multiple options click while 
      holding the "ctrl" key. The results can be downloaded in FASTA or CSV tabular format.
     <b>| ' . l('Short video tutorial', 'https://www.youtube.com/watch?v=i0IuE1qQn0s', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', 'tutorial/sequence_search') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('sequence_search')
      ->startWidget('genus')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_sequence_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_sequence_search}";
  // Add conditions
  $where [0] = Sql::textFilterOnMultipleColumns('feature_name', $form_state, array('uniquename', 'name'));
  $where [1] = Sql::selectFilter('feature_type', $form_state, 'feature_type');
  $where [2] = Sql::selectFilter('analysis', $form_state, 'analysis_name');
  $where [3] = Sql::fileOnMultipleColumns('feature_name_file', array('uniquename', 'name'));
  $where [4] = Sql::selectFilter('genus', $form_state, 'genus');
  $where [5] = Sql::selectFilter('species', $form_state, 'organism');
  $where [6] = Sql::selectFilter('location', $form_state, 'landmark');
  $where [7] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_sequence_search_table_definition')
    ->fastaDownload(TRUE)
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_sequence_search_table_definition () {
  $headers = array(      
      'name:s:chado_search_link_feature:feature_id' => 'Name',
      'uniquename:s' => 'Uniquename',
      'feature_type:s' => 'Type',
      'organism:s' => 'Organism',
      'analysis_name:s:chado_search_link_analysis:analysis_id' => 'Source',
      'location:s:chado_search_link_jbrowse:srcfeature_id,location' => 'Location',
  );
  return $headers;
}

/*************************************************************
 * AJAX callbacks
*/
// User defined: Populating the landmark for selected organism
function chado_search_sequence_search_ajax_location ($value) {
  $sql = "SELECT distinct landmark FROM {chado_search_sequence_search} WHERE analysis_name IN (:analysis) ORDER BY landmark";
  return chado_search_bind_dynamic_select(array(':analysis' => $value), 'landmark', $sql);
}


function chado_search_sequence_search_ajax_organism ($val) {
  $sql = "SELECT organism FROM {chado_search_sequence_search} WHERE genus = :genus GROUP BY organism ORDER BY organism";
  return chado_search_bind_dynamic_select(array(':genus' => $val), 'organism', $sql);
}
