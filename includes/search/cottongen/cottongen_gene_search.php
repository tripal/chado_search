<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_gene_search_form ($form) {
  //drupal_set_message('Gene Search is currently under maintenance and is unavailable at this moment.');
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_gene_search')
      ->labelWidth(163)
      ->cache(TRUE)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('analysis')
      ->title('Dataset')
      ->column('analysis')
      ->table('chado_search_gene_search')
      ->multiple(TRUE)
      ->optGroupByPattern(array('Curated Genes' => 'NCBI', 'Predicted Genes' => 'Genome|genome', 'Unigene' => 'Unigene', 'RefTrans' => 'RefTrans'))
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
     <b>| ' . l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('gene_search_fields')
      ->startWidget('organism')
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
  $where [] = Sql::textFilterOnMultipleColumns('feature_name', $form_state, array('uniquename', 'name'));
  $where [] = Sql::selectFilter('analysis', $form_state, 'analysis');
  $where [] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [] = Sql::fileOnMultipleColumns('feature_name_file_inline', array('uniquename', 'name'));
  $where [] = Sql::selectFilter('location', $form_state, 'landmark');
  $where [] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
  $where [] = Sql::textFilterOnMultipleColumns('keyword', $form_state, array('go_term', 'blast_value', 'kegg_value', 'interpro_value', 'gb_keyword'));

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
    'location:s:chado_search_gene_search_link_gbrowse:srcfeature_id,location,analysis' => 'Location',
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_gene_search_link_feature ($feature_id) {
  return chado_search_link_entity('feature', $feature_id);
}

// Define call back to link the location to GDR GBrowse
function chado_search_gene_search_link_gbrowse ($paras) {
  $srcfeature_id = $paras [0];
  $loc = preg_replace("/ +/", "", $paras [1]);
  $ncbi = preg_match('/NCBI /', $paras[2]);
  $sql =
  "SELECT A.name
    FROM {feature} F
    INNER JOIN {analysisfeature} AF ON F.feature_id = AF.feature_id
    INNER JOIN {analysis} A ON A.analysis_id = AF.analysis_id
    INNER JOIN {analysisprop} AP ON AP.analysis_id = A.analysis_id
    INNER JOIN {cvterm} V ON V.cvterm_id = AP.type_id
    WHERE
    V.name = 'Analysis Type' AND
    AP.value = 'whole_genome' AND
    F.feature_id = :srcfeature_id";
  $genome = chado_query($sql, array('srcfeature_id' => $srcfeature_id))->fetchField();
  $url = "";
  if($genome == 'Gossypium arboreum (A2) Genome BGI Assembly v2.0 & Annotation v1.0') {
    $ver = $ncbi ? 'v1.1' : 'v1.0';
    $url = "https://www.cottongen.org/jbrowse/index.html?data=data%2FGa_BGI_CGP&loc=$loc&tracks=DNA%2CPredicted_mRNA";
  }
  else if ($genome == 'Gossypium barbadense (AD2) Genome HAU-SGI Assembly v1.0 & Annotation v1.0') {
    $url = "https://www.cottongen.org/jbrowse/index.html?data=data%2FGb_HAU_NBI&loc=$loc&tracks=DNA%2CPredicted_mRNA%2Cgene";
  }
  else if ($genome == 'Gossypium hirsutum (AD1) Genome CGP-BGI Assembly v1.0 & Annotation v1.0') {
    $url = "https://www.cottongen.org/jbrowse/index.html?data=data%2FGh_BGI_CGP&loc=$loc&tracks=DNA%2CPredicted_mRNA";
  }
  else if ($genome == 'Gossypium hirsutum (AD1) Genome NAU-NBI Assembly v1.1 & Annotation v1.1') {
    $url = "https://www.cottongen.org/jbrowse/index.html?data=data%2FGh_NAU_NBI&loc=$loc&tracks=DNA%2CPredicted_mRNA%2Cgene";
  }
  else if ($genome == 'Gossypium raimondii (D5) Draft Genome BGI-CGP v1.0 Assembly & Annotation') {
    $url = "https://www.cottongen.org/jbrowse/index.html?data=data%2FGr_BGI_CGP&loc=$loc&tracks=DNA%2CPredicted_mRNA";
  }
  else if($genome == 'Gossypium raimondii (D5) genome JGI assembly v2.0 (annot v2.1)') {
    $url = "https://www.cottongen.org/jbrowse/index.html?data=data%2FGr_JGI_221&loc=$loc&tracks=DNA%2CTranscripts%2Cgene";
  }
  else if($genome == 'Gossypium hirsutum (AD1) Genome - Texas Interim release Tx-JGI v1.1') {
    $loc = str_replace('Tx_JGIv1.1_', '', $loc);
    $url = "https://www.cottongen.org/jbrowse/index.html?data=data%2FGh_Tx_JGIv1.1&loc=$loc&tracks=DNA%2CTranscripts%2Cgene";
  }
  return chado_search_link_url ($url);
}

/*************************************************************
 * AJAX callbacks
 */
function chado_search_gene_search_ajax_location ($val) {
  $sql = "SELECT distinct landmark FROM {chado_search_gene_search} WHERE analysis IN (:analysis) ORDER BY landmark";
  return chado_search_bind_dynamic_select(array(':analysis' => $val), 'landmark', $sql);
}

function chado_search_gene_search_ajax_organism ($val) {
  $sql = "SELECT organism FROM {chado_search_gene_search} WHERE genus = :genus GROUP BY organism ORDER BY organism";
  return chado_search_bind_dynamic_select(array(':genus' => $val), 'organism', $sql);
}
