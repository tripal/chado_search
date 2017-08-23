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
      ->optGroup(array('Any', 'Fragaria', 'Malus', 'Prunus', 'Pyrus', 'Rubus', 'Rosa'))
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
      ->optGroupByPattern(array('Curated Genes' => 'GDR Gene Database|NCBI', 'Predicted Genes' => 'Genome', 'Unigene' => 'Unigene', 'RefTrans' => 'RefTrans'))
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
    'blast_value' => 'BLAST',
    'interpro_value' => 'InterPro',
    'kegg_value' => 'KEGG',
    'go_term' => 'GO Term',
    'gb_keyword' => 'GenBank'
  );
  $form->addCustomOutput (
      Set::customOutput()
      ->id('custom_output')
      ->options($customizables)
      ->defaults(array('organism', 'feature_type'))
      ->replaceStarWithSelection()
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
  $where [] = Sql::textFilterOnMultipleColumns('feature_name', $form_state, array('uniquename', 'name'));
  $where [] = Sql::selectFilter('analysis', $form_state, 'analysis');
  $where [] = Sql::selectFilter('genus', $form_state, 'genus');
  $where [] = Sql::selectFilter('species', $form_state, 'organism');
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
    'name:s:chado_search_gene_search_link_feature:feature_id,name' => 'Name',
    'organism:s' => 'Organism',
    'feature_type:s' => 'Type',
    'analysis:s' => 'Source',
    'location:s:chado_search_gene_search_link_gbrowse:srcfeature_id,location,analysis' => 'Location',
/*     'blast_value:s' => 'BLAST',
    'interpro_value:s' => 'InterPro',
    'kegg_value:s' => 'KEGG',
    'go_term:s' => 'GO',
    'gb_keyword:s' => 'GenBank' */
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_gene_search_link_feature ($var) {
  $feature_id = $var[0];
  $name = $var[1];
  if ($feature_id) {
    return chado_search_link_entity('feature', $feature_id);
  }
  else {
    return '/feature/' . $name;
  }
}

// Define call back to link the location to GDR GBrowse
function chado_search_gene_search_link_gbrowse ($paras) {
  $srcfeature_id = $paras [0];
  $loc = preg_replace("/ +/", "", $paras [1]);
  if (!$srcfeature_id) {
    $srcfeature = explode(':', $loc);
    $srcfeature_id =
    chado_query(
        "SELECT feature_id FROM {feature} WHERE uniquename = :uniquename OR name = :uniquename",
        array(':uniquename' =>$srcfeature[0]))
        ->fetchField();
  }
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
  $genome = $srcfeature_id ? chado_query($sql, array('srcfeature_id' => $srcfeature_id))->fetchField() : NULL;
  $url = "";
  if($genome == 'Fragaria vesca Whole Genome v1.0 (build 8) Assembly & Annotation') {
    $ver = $ncbi ? 'v1.1' : 'v1.0';
    $url = "http://www.rosaceae.org/gb/gbrowse/fragaria_vesca_$ver-lg?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  else if ($genome == 'Fragaria vesca Whole Genome v1.1 Assembly & Annotation') {
    $url = "http://www.rosaceae.org/gb/gbrowse/fragaria_vesca_v1.1-lg?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  else if ($genome == 'Prunus persica Whole Genome v1.0 Assembly & Annotation') {
    $url = "http://www.rosaceae.org/gb/gbrowse/prunus_persica?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  else if ($genome == 'Prunus persica Whole Genome Assembly v2.0 & Annotation v2.1 (v2.0.a1)') {
    $url = "http://www.rosaceae.org/gb/gbrowse/prunus_persica_v2.0.a1?name=$loc&enable=NCBI%20Sequence%20Alignments";  
  }
  else if ($genome == 'Malus x domestica Whole Genome v1.0p Assembly & Annotation') {
      $url = "http://www.rosaceae.org/gb/gbrowse/malus_x_domestica_v1.0-primary?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  else if($genome == 'Malus x domestica Whole Genome v1.0 Assembly & Annotation') {
      $url = "http://www.rosaceae.org/gb/gbrowse/malus_x_domestica?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  else if ($genome == 'Pyrus communis Genome v1.0 Draft Assembly & Annotation') {
    $url = "http://www.rosaceae.org/gb/gbrowse/pyrus_communis_v1.0?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  else if ($genome == 'Rubus occidentalis Whole Genome Assembly v1.0 & Annotation v1') {
    $url = "http://www.rosaceae.org/gb/gbrowse/rubus_occidentalis_v1.0.a1?name=$loc&enable=NCBI%20Sequence%20Alignments";
  }
  else if ($genome == 'Prunus avium Whole Genome Assembly v1.0 & Annotation v1 (v1.0.a1)') {
    $url = "http://www.rosaceae.org/jbrowse/index.html?data=data/prunus/pavium_v1.0.a1&loc=$loc";
  }
  else if($genome == 'Malus x domestica GDDH13 v1.1  Whole Genome Assembly & Annotation') {
    $url = "http://www.rosaceae.org/jbrowse/index.html?data=data/malus/mdomestica_gddh13_v1.1&loc=$loc";
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
