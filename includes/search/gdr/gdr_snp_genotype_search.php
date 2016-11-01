<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_snp_genotype_search_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('snp_genotype_tabs')
      ->items(array('/search/ssr_genotype' => 'SSR Genotype', '/search/snp_genotype' => 'SNP Genotype'))
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('project_name')
      ->title('Dataset')
      ->column('project_name')
      ->table('chado_search_snp_genotype_search')
      ->cache(TRUE)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_snp_genotype_search')
      ->multiple(TRUE)
      ->cache(TRUE)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('stock_uniquename')
      ->title('Germplasm Name')
      ->column('stock_uniquename')
      ->table('chado_search_snp_genotype_search')
      ->cache(TRUE)
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('feature_uniquename')
      ->title('SNP')
  );
  $form->addSubmit();
  $form->addReset();
  $desc = "Search SNP Genotype is a page where users can search for the SNP genotyope 
      dataset based on the germplasm and SNP markers used in the dataset. Click the next tab 
      to search for SSR Genotype. To search for SNP genotype data only for cultivars and 
      breeding selections please visit the <a href=\"/legacy/bt_search_genotype/by_variety\">
      'Search Genotyping Data'</a> page in the <a href=\"/legacy/breeders_toolbox\">Breeders Toolbox</a>. ".
     " | <b>" . l('Short video tutorial', 'https://www.youtube.com/watch?v=ARZGxKz5mRo', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', 'tutorial/search_diversity') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('snp_genotype_search')
      ->startWidget('project_name')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_snp_genotype_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_snp_genotype_search_base_query();
  
  // Add conditions
  $where [0] = Sql::selectFilter('project_name', $form_state, 'project_name');
  $where [1] = Sql::selectFilter('stock_uniquename', $form_state, 'stock_uniquename');
  $where [2] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [3] = Sql::textFilter('feature_uniquename', $form_state, 'feature_uniquename');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_snp_genotype_search_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_snp_genotype_search_base_query() {
  //$query = "SELECT feature_id, feature_uniquename, project_name, filename FROM chado_search_snp_genotype_search CSDS";
  $query = "SELECT DISTINCT project_id, project_name, filename, pub_id, citation FROM {chado_search_snp_genotype_search} CSDS";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_snp_genotype_search_table_definition () {
  $headers = array(
/*   'feature_uniquename:s:chado_search_snp_genotype_search_link_feature:feature_id' => 'SNP', */
      'project_name:s:chado_search_ssr_genotype_search_link_project:project_id' => 'Dataset',
      'filename:s:chado_search_snp_genotype_search_link_file:filename' => 'File',
      'citation:s:chado_search_snp_genotype_search_link_pub:pub_id' => 'Publication'
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_snp_genotype_search_link_feature ($feature_id) {
  $nid = chado_get_nid_from_id('feature', $feature_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the featuremap to its  node for result table
function chado_search_ssr_genotype_search_link_project ($project_id) {
  $nid = chado_get_nid_from_id('project', $project_id);
  return chado_search_link_node ($nid);
}

function chado_search_snp_genotype_search_link_file ($filename) {
  return '/bulk_data/www.rosaceae.org/genotype_snp/' . $filename;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_snp_genotype_search_link_pub ($pub_id) {
  $nid = chado_get_nid_from_id('pub', $pub_id);
  return chado_search_link_node ($nid);
}
