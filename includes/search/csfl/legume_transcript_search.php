<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_transcript_search_form ($form) {
  $form->addSelectFilter(
      Set::selectFilter()->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_transcript_search')
      ->labelWidth(160)
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('feature_name')
      ->title('Name')
      ->labelWidth(160)
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('transcript_search_by_name')
      ->title("Search by Name")
      ->startWidget('organism')
      ->endWidget('feature_name')
  );
  
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('analysis')
      ->title('Analysis')
      ->column('analysis')
      ->table('chado_search_transcript_search')
      ->multiple( TRUE)
      ->columnNaturalSort(TRUE)
      ->cache(TRUE)
      ->labelWidth(160)
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('transcript_search_by_assembly')
      ->title("Search by Assembly")
      ->startWidget('analysis')
      ->endWidget('analysis')
  );
  
  $form->addTextFilter(
      Set::textFilter()
      ->id('go_term')
      ->title('GO Term')
      ->newLine()
      ->labelWidth(160)
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('homology')
      ->title('BLAST Description')
      ->newLine()
      ->labelWidth(160)
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('kegg')
      ->title('KEGG Description')
      ->newLine()
      ->labelWidth(160)
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('interpro')
      ->title('INTERPRO Description')
      ->newLine()
      ->labelWidth(160)
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('transcript_search_by_function')
      ->title("Search by Putative Function")
      ->startWidget('go_term')
      ->endWidget('interpro')
  );
  
  $form->addSubmit();
  $form->addReset();
  
  $form->addFieldset(
      Set::fieldset()
      ->id('transcript_search_fields')
      ->startWidget('organism')
      ->endWidget('reset')
  );
  
  return $form;
}

// Submit the form
function chado_search_transcript_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_transcript_search}";
  // Add conditions
  $where [] = Sql::textFilterOnMultipleColumns('feature_name', $form_state, array('uniquename', 'name'));
  $where [] = Sql::selectFilter('analysis', $form_state, 'analysis');
  $where [] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [] = Sql::textFilter('go_term', $form_state, 'go_term');
  $where [] = Sql::textFilter('homology', $form_state, 'blast_value');
  $where [] = Sql::textFilter('kegg', $form_state, 'kegg_value');
  $where [] = Sql::textFilter('interpro', $form_state, 'interpro_value');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_transcript_search_table_definition')
    ->fastaDownload(TRUE)
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_transcript_search_table_definition () {
  $headers = array(      
    'name:s:chado_search_transcript_search_link_feature:feature_id' => 'Name',
    'organism:s' => 'Organism',
    'seqlen:s' => 'Length',
    'feature_type:s' => 'Type',
    'go_term:s' => 'GO Term',
    'blast_value:s' => 'BLAST',
    'kegg_value:s' => 'KEGG',
    'interpro_value:s' => 'INTERPRO'
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_transcript_search_link_feature ($feature_id) {
  return chado_search_link_entity('feature', $feature_id);
}

