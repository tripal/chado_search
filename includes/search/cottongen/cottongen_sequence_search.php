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
      'location:s:chado_search_sequence_search_link_jbrowse:srcfeature_id,location:analysis' => 'Location'
  );
  return $headers;
}

function chado_search_sequence_search_link_jbrowse ($paras) {
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
// User defined: Populating the landmark for selected organism
function chado_search_sequence_search_ajax_location ($val) {
  $sql = "SELECT distinct landmark FROM {chado_search_sequence_search} WHERE analysis_name IN (:analysis_name) ORDER BY landmark";
  return chado_search_bind_dynamic_select(array(':analysis_name' => $val), 'landmark', $sql);
 }
