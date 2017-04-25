<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_gene_search_form ($form) {  
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('genus')
      ->title('Genus')
      ->column('genus')
      ->table('chado_search_gene_search')
      ->cache(TRUE)
      ->labelWidth(163)
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('species')
      ->title('Species')
      ->dependOnId('genus')
      ->callback('chado_search_gene_search_ajax_organism')
      ->labelWidth(66)
      ->newLine()
  );
  $icon = '/' . drupal_get_path('module', 'chado_search') . '/theme/images/question.gif';
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('analysis')
      ->title('Dataset <a href="/sequence_dataset_description"><img src="' . $icon . '"></a>')
      ->column('analysis')
      ->table('chado_search_gene_search')
      ->multiple(TRUE)
      ->optGroupByPattern(array('Genbank Genes' => 'NCBI', 'Predicted Genes' => 'Genome|genome', 'Unigene' => 'Unigene|unigene', 'RefTrans' => 'RefTrans'))
      ->cache(TRUE)
      ->labelWidth(163)
      ->size(5)
      ->newLine()
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('location')
      ->title('Genome Location')
      ->dependOnId('analysis')
      ->callback('chado_search_gene_search_ajax_location')
      ->labelWidth(163)
  );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('fmin')
      ->title("between")
      ->id2('fmax')
      ->title2("and")
      ->size(15)
      ->labelWidth(70)
      ->labelWidth2(40)
      ->newLine()
  );
  //$form->addTextFilter('feature_name', 'Gene/Transcript Name', FALSE, 60);
  $form->addTextFilter(
      Set::textFilter()
      ->id('feature_name')
      ->title('Gene/Transcript Name')
      ->labelWidth(163)
  );
  //$form->addMarkup('feature_name_example', '(e.g. adh)');
  $form->addFile(
      Set::file()
      ->id('feature_name_file_inline')
      ->labelWidth(1)
      ->newLine()
  );
  
  $form->addTextFilter(
      Set::textFilter()
      ->id('keyword')
      ->title('Keyword')
      ->labelWidth(163)
  );
  $form->addMarkup(
      Set::markup()
      ->id('keyword_example')
      ->text('(eg. polygalacturonase, resistance, EC:1.4.1.3, cell cycle, ATP binding, zinc finger)')
      ->newLine()
  );
/*   $customizables = array(
    'organism' => 'Organism',
    'feature_type' => 'Type',
    'analysis' => 'Source',
    'location' => 'Location',
  );
  $form->addCustomOutput (
      Set::customOutput()
      ->id('custom_output')
      ->options($customizables)
      ->defaults(array('organism', 'feature_type'))
  ); */
  $form->addSubmit();
  $form->addReset();
  $desc = 
    'Search genes and transcripts by species, dataset, genome location, name and/or keyword. 
      For keyword, enter any protein name of homologs, KEGG term/EC number, GO term, or InterPro term.  
     <b>| ' . l('Short video tutorial', 'https://youtu.be/P-Rw8i9Iz5E', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', 'tutorial/gene_search') . ' | ' . 
    l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('gene_search_fields')
      ->startWidget('genus')
      ->endWidget('reset')
      ->description($desc)
  );
  
  return $form;
}

// Submit the form
function chado_search_gene_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_gene_search}";
  // Add conditions
  $where [0] = Sql::textFilterOnMultipleColumns('feature_name', $form_state, array('uniquename', 'name'));
  $where [1] = Sql::selectFilter('analysis', $form_state, 'analysis');
  $where [2] = Sql::selectFilter('genus', $form_state, 'genus');
  $where [3] = Sql::selectFilter('species', $form_state, 'organism');
  $where [4] = Sql::fileOnMultipleColumns('feature_name_file_inline', array('uniquename', 'name'));
  $where [5] = Sql::selectFilter('location', $form_state, 'landmark');
  $where [6] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
  $where [7] = Sql::textFilterOnMultipleColumns('keyword', $form_state, array('go_term', 'blast_value', 'kegg_value', 'interpro_value', 'gb_keyword'));

  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_gene_search_table_definition')
    ->fastaDownload(TRUE)
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_gene_search_table_definition () {
  $headers = array(
    'name:s:chado_search_gene_search_link_feature:feature_id' => 'Name',
    'organism:s' => 'Organism',
    'feature_type:s' => 'Type',
    'analysis:s' => 'Source',
    'location:s' => 'Location',
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_gene_search_link_feature ($feature_id) {
  $nid = chado_get_nid_from_id('feature', $feature_id);
  return chado_search_link_node ($nid);
}

/*************************************************************
 * AJAX callbacks
 */
function chado_search_gene_search_ajax_location ($val) {
  $sql = "SELECT distinct landmark FROM {chado_search_gene_search} WHERE analysis = :analysis ORDER BY landmark";
  return chado_search_bind_dynamic_select(array(':analysis' => $val), 'landmark', $sql);
}

function chado_search_gene_search_ajax_organism ($val) {
  $sql = "SELECT organism FROM {chado_search_gene_search} WHERE genus = :genus GROUP BY organism ORDER BY organism";
  return chado_search_bind_dynamic_select(array(':genus' => $val), 'organism', $sql);
}
