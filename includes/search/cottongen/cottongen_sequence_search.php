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
      ->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_sequence_search')
      ->multiple(TRUE)
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
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('analysis')
      ->title('Source')
      ->column('analysis_name')
      ->table('chado_search_sequence_search')
      ->multiple(TRUE)
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
  $desc = "Search for sequences by entering names in the field below. Alternatively, you may upload a file of names. You may also filter results by sequence type and the sequence source. To select multiple options click while holding the \"ctrl\" key. The results can be downloaded in FASTA or CSV tabular format.";
  $form->addFieldset(
      Set::fieldset()
      ->id('sequence_search')
      ->startWidget('organism')
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
  $where [4] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [5] = Sql::selectFilter('location', $form_state, 'landmark');
  $where [6] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
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
      'location:s:chado_search_link_jbrowse:srcfeature_id,location' => 'Location'
  );
  return $headers;
}

/*************************************************************
 * AJAX callbacks
*/
// User defined: Populating the landmark for selected organism
function chado_search_sequence_search_ajax_location ($val) {
  $sql = "SELECT distinct landmark FROM {chado_search_sequence_search} WHERE analysis_name IN (:analysis_name) ORDER BY landmark";
  return chado_search_bind_dynamic_select(array(':analysis_name' => $val), 'landmark', $sql);
 }
