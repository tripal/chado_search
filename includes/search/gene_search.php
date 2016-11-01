<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_gene_search_form ($form) {
  $form->addTextFilter(
      Set::textFilter()
      ->id('feature_name')
      ->title('Gene/Feature Name')
      ->labelWidth(160)
  );
  $form->addMarkup(
      Set::markup()
      ->id('feature_name_example')
      ->text('(e.g. adh)')
  );
  $form->addFile(
      Set::file()
      ->id('feature_name_file')
      ->title("File Upload")
      ->description("Provide sequence names in a file. Separate each name by a new line.")
      ->labelWidth(160)
      ->newLine()
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('gene_search_by_name')
      ->title("Search by Name")
      ->startWidget('feature_name')
      ->endWidget('feature_name_file')
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('analysis')
      ->title('Source')
      ->column('analysis')
      ->table('chado_search_gene_search')
      ->multiple(TRUE)
      ->columnNaturalSort(TRUE)
      ->cache(TRUE)
      ->labelWidth(160)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('feature_type')
      ->title('Type')
      ->column('feature_type')
      ->table('chado_search_gene_search')
      ->multiple(TRUE)
      ->cache(TRUE)
      ->labelWidth(160)
      ->newLine()
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('gene_search_by_assembly')
      ->title("Search by Assembly")
      ->startWidget('analysis')
      ->endWidget('feature_type')
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('go_term')
      ->title('GO Term')
      ->labelWidth(160)
  );
  $form->addMarkup(
      Set::markup()
      ->id('go_term_example')
      ->text('(e.g. GTP binding, fatty acid)')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('homology')
      ->title('BLAST Description')
      ->labelWidth(160)
  );
  $form->addMarkup(
      Set::markup()
      ->id('homology_example')
      ->text('(i.e. words of blasted sequences. e.g. fatty acid)')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('kegg')
      ->title('KEGG Description')
      ->labelWidth(160)
  );
  $form->addMarkup(
      Set::markup()
      ->id('kegg_example')
      ->text('(e.g. EC:1.14.19, fatty acid)')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('interpro')
      ->title('INTERPRO Description')
      ->labelWidth(160)
  );
  $form->addMarkup(
      Set::markup()
      ->id('interpro_example')
      ->text('(e.g. family, pfam, pir, panther, fatty acid)')
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('gene_search_by_function')
      ->title("Search by Putative Function")
      ->startWidget('go_term')
      ->endWidget('interpro_example')
  );
  
  $form->addSubmit();
  $form->addReset();
  $form->addFieldset(
      Set::fieldset()
      ->id('gene_search_fields')
      ->startWidget('feature_name')
      ->endWidget('reset')
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
  $where [2] = Sql::fileOnMultipleColumns('feature_name_file', array('uniquename', 'name'));
  $where [3] = Sql::textFilter('go_term', $form_state, 'go_term');
  $where [4] = Sql::textFilter('homology', $form_state, 'blast_value');
  $where [5] = Sql::textFilter('kegg', $form_state, 'kegg_value');
  $where [6] = Sql::textFilter('interpro', $form_state, 'interpro_value');
  $where [7] = Sql::selectFilter('feature_type', $form_state, 'feature_type');
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
function chado_search_gene_search_link_feature ($feature_id) {
  $nid = chado_get_nid_from_id('feature', $feature_id);
  return chado_search_link_node ($nid);
}

