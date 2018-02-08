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
  	  ->only(array('Lens culinaris', 'Pisum sativum', 'Cicer arietinum', 'Vicia faba'))
      ->newLine()
      ->labelWidth(120)
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('feature_type')
      ->title('Type')
      ->column('feature_type')
      ->table('chado_search_sequence_search')
      ->multiple(TRUE)
      ->labelWidth(120)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('analysis')
      ->title('Source')
      ->column('analysis_name')
      ->table('chado_search_sequence_search')
      ->multiple( TRUE)
      ->labelWidth(120)
      ->newLine()
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('location')
      ->title('Location')
      ->dependOnId('analysis')
      ->callback('chado_search_sequence_search_ajax_location')
      ->labelWidth(120)
      );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('fmin')
      ->title("between")
      ->id2('fmax')
      ->title2("and")
      ->size(15)
      ->labelWidth2(50)
      ->newLine()
      );
  $form->addTextFilter(
      Set::textFilter()
      ->id('feature_name')
      ->title('Sequence Name')
      ->labelWidth(120)
  );
  $form->addFile(
      Set::file()
      ->id('feature_name_file')
      ->labelWidth(1)
      ->newLine()
  );
  $form->addSubmit();
  $form->addReset();
  $desc =
  'Search for sequences by entering names in the field below. 
      Alternatively, you may upload a file of names. You may also filter results by sequence type and the sequence source. 
      To select multiple options click while holding the "ctrl" key. The results can be downloaded in FASTA or CSV tabular format.
     <b>| ' . l('Short video tutorial', 'https://youtu.be/_rhSRY7x8c4', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', '/UserManual') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
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
  $where [] = Sql::textFilterOnMultipleColumns('feature_name', $form_state, array('uniquename', 'name'));
  $where [] = Sql::selectFilter('feature_type', $form_state, 'feature_type');
  $where [] = Sql::selectFilter('analysis', $form_state, 'analysis_name');
  $where [] = Sql::fileOnMultipleColumns('feature_name_file', array('uniquename', 'name'));
  $where [] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [] = Sql::selectFilter('location', $form_state, 'landmark');
  $where [] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
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
      'name:s:chado_search_sequence_search_link_feature:feature_id' => 'Name',
      'uniquename:s' => 'Uniquename',
      'feature_type:s' => 'Type',
      'organism:s' => 'Organism',
      'analysis_name:s:chado_search_sequence_search_link_analysis:analysis_id' => 'Source',
      'location:s:chado_search_sequence_search_link_jbrowse:srcfeature_id,location,analysis_name' => 'Location'
  );
  return $headers;
}
// Define call back to link the sequence to its node for result table
function chado_search_sequence_search_link_analysis ($analysis_id) {
  return chado_search_link_entity('analysis', $analysis_id);
}
// Define call back to link the featuremap to its  node for result table
function chado_search_sequence_search_link_feature ($feature_id) {
  return chado_search_link_entity('feature', $feature_id);
}
// Define call back to link the location to GBrowse
function chado_search_sequence_search_link_jbrowse ($paras) {
    $srcfeature_id = $paras [0];
    $loc = $paras[1];
    $sql =
    "SELECT value
    FROM {feature} F
    INNER JOIN {analysisfeature} AF ON F.feature_id = AF.feature_id
    INNER JOIN {analysis} A ON A.analysis_id = AF.analysis_id
    INNER JOIN {analysisprop} AP ON AP.analysis_id = A.analysis_id
    INNER JOIN {cvterm} V ON V.cvterm_id = AP.type_id
    WHERE
    V.name = 'JBrowse URL' AND
    F.feature_id = :srcfeature_id";
    $jbrowse = $srcfeature_id ? chado_query($sql, array('srcfeature_id' => $srcfeature_id))->fetchField() : NULL;
    if ($jbrowse) {
        return chado_search_link_url ($jbrowse . $loc);
    }
    else {
        return NULL;
    }
}
/*************************************************************
 * AJAX callbacks
*/
// User defined: Populating the landmark for selected organism
function chado_search_sequence_search_ajax_location ($val) {
  $sql = "SELECT distinct landmark, char_length(landmark) AS length FROM {chado_search_sequence_search} WHERE analysis_name IN (:analysis) ORDER BY length, landmark";
  return chado_search_bind_dynamic_select(array(':analysis' => $val), 'landmark', $sql);
}
