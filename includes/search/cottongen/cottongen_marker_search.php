<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_marker_search_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('nearby_marker_tabs')
      ->items(array('/find/markers' => 'Advanced Marker Search', '/find/marker/source' => 'Marker Source', '/find/snp_markers' => 'SNP Marker Search', '/find/nearby_markers' => 'Nearby Markers', '/find/qtl_nearby_markers' => 'QTL Nearby Markers'))
  );
  // Search by Name
  $form->addTextFilter(
      Set::textFilter()
      ->id('marker_uniquename')
      ->title('Marker Name')
      ->labelWidth(120)
      ->newLine()
  );
  $form->addFile(
      Set::file()
      ->id('marker_uniquename_file')
      ->title("File Upload")
      ->description("Provide marker names in a file. Separate each name by a new line.")
      ->labelWidth(120)
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('marker_search_by_name')
      ->title("Search by Name")
      ->startWidget('marker_uniquename')
      ->endWidget('marker_uniquename_file')
  );
  
  // Restricted by Features
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('marker_type')
      ->title('Marker Type')
      ->column('marker_type')
      ->labelWidth(120)
      ->newLine()
      ->table('chado_search_marker_search')
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('map_genome')
      ->title('Marker Mapped in Genome')
      ->column('map_genome')
      ->table('chado_search_marker_search')
      ->labelWidth(220)
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Marker Developed from Species')
      ->column('organism')
      ->table('chado_search_marker_search')
      ->labelWidth(260)
  );
  
  $form->addFieldset(
      Set::fieldset()
      ->id('marker_search_by_features')
      ->title("Restrict by Features")
      ->startWidget('marker_type')
      ->endWidget('organism')
  );  
  
  // Restricted by Location
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('location')
      ->title('G. raimondii (D5) Genome Location')
      ->column('landmark')
      ->table('chado_search_marker_search')
      ->optGroupByPattern(array('JGI' => '_JGI_', 'BGI' => 'BGI|Chr'))
      ->labelWidth(260)
  );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('fmin')
      ->title("between")
      ->id2('fmax')
      ->title2("and")
      ->labelWidth2(50)
      ->size(10)
  );
  $form->addMarkup(
      Set::markup()
      ->id('location_unit')
      ->text("<strong>bp</strong>")
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('map_name')
      ->title('Map')
      ->column('map_name')
      ->table('chado_search_marker_search')
      ->labelWidth(120)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('chr_number')
      ->title('Chr Number')
      ->column('chr_number')
      ->table('chado_search_marker_search')
      ->labelWidth(120)
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('marker_search_by_location')
      ->title("Restrict by Location")
      ->startWidget('location')
      ->endWidget('chr_number')
  );
  
  $form->addDynamicTextFields(
      Set::dynamicTextFields()
      ->id('location')
      ->callback('chado_search_marker_search_ajax_location')      
      ->targetIds(array('fmin', 'fmax'))
  );
  
  $form->addSubmit();
  $form->addReset();
  //$desc = "Search for markers by entering name, marker type, species, or its map or genomic location in the field below";
  $form->addFieldset(
      Set::fieldset()
      ->id('marker_search_fields')
      ->startWidget('marker_uniquename')
      ->endWidget('reset')
  );
  return $form;
}

// Submit the form
function chado_search_marker_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_marker_search}";
  // Add conditions
  $where [] = Sql::textFilterOnMultipleColumns('marker_uniquename', $form_state, array('marker_uniquename', 'alias'));
  $where [] = Sql::file('marker_uniquename_file', 'marker_uniquename');
  $where [] = Sql::selectFilter('marker_type', $form_state, 'marker_type');
  $where [] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [] = Sql::selectFilter('map_genome', $form_state, 'map_genome');
  $where [] = Sql::selectFilter('location', $form_state, 'landmark');
  $where [] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
  $where [] = Sql::selectFilter('map_name', $form_state, 'map_name');
  $where [] = Sql::selectFilter('chr_number', $form_state, 'chr_number');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_marker_search_table_definition')
    ->fastaDownload(TRUE)
    ->rewriteCols('landmark=chado_search_marker_search_rewrite_landmark;organism=chado_search_marker_search_rewrite_organism')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_marker_search_table_definition () {
  $headers = array(      
      'marker_uniquename:s:chado_search_link_feature:marker_feature_id' => 'Name',
      'alias:s' => 'Alias',
      'marker_type:s' => 'Type',
      'organism:s:chado_search_link_organism:organism_id' => 'Species',
      'map_name:s:chado_search_link_featuremap:featuremap_id' => 'Map',
      'lg_uniquename:s:chado_search_marker_search_link_cmap:featuremap_id' => 'Linkage Group',
      'start:s' => 'Genetic Location',
      'landmark:s:chado_search_marker_search_link_landmark:landmark' => 'Genome Sequence Name',
      'location:s:chado_search_link_jbrowse:landmark_feature_id,location' => 'Genomic Location'
  );
  return $headers;
}

/*************************************************************
 * Build the search result table
*/
// Rewrite landmark
function chado_search_marker_search_rewrite_landmark ($value) {
  $value = preg_replace('/^Chr\d+_JGI_v2\.0|^scaffold_\d+_JGI_v2\.0/', 'D5_JGI_v2.0',$value);
  $value = preg_replace('/^Chr\d+$|^scaffold\d+-BGI-CGP_v1\.0/', 'D5_BGI_v1.0',$value);
  return $value;
}

// Rewrite species
function chado_search_marker_search_rewrite_organism ($value) {
  return preg_replace('/^Gossypium /', '',$value);
}

// Define call back to link the sequence_feature to its  node for result table
function chado_search_marker_search_link_cmap ($featuremap_id) {
  $sql = 
  "SELECT DB.urlprefix, X.accession 
    FROM {featuremap} M
       INNER JOIN {featuremap_dbxref} FD ON FD.featuremap_id = M.featuremap_id
       INNER JOIN {dbxref} X ON FD.dbxref_id = X.dbxref_id
    INNER JOIN {db} ON db.db_id = X.db_id WHERE M.featuremap_id = :featuremap_id";
  $obj = chado_query($sql, array(':featuremap_id' => $featuremap_id))->fetchObject();
  if ($obj && $obj->urlprefix && $obj->accession) {
    return $obj->urlprefix . $obj->accession;
  } else {
    return NULL;
  }
}

// Define call back to link the landmark
function chado_search_marker_search_link_landmark ($landmark) {
  if (preg_match('/^Chr\d+_JGI_v2\.0|^scaffold_\d+_JGI_v2\.0/', $landmark)) {
    return '/species/Gossypium_raimondii/jgi_genome_221';
  } else if (preg_match('/^Chr\d+$|^scaffold\d+-BGI-CGP_v1\.0/', $landmark)) {
    return '/species/Gossypium_raimondii/bgi-cgp_genome_v1.0';
  } else {
    return NULL;
  }
}

/*************************************************************
 * AJAX callbacks
*/
// Downloading file ajax callback
function chado_search_marker_search_download_fasta_definition () {
  return 'marker_feature_id';
}
// User defined: Populating the landmark for selected organism
function chado_search_marker_search_ajax_location ($val, $id) {
  $sql = "SELECT '1' As fmin, seqlen AS fmax FROM {feature} WHERE feature_id = (SELECT first(landmark_feature_id) AS landmark FROM {chado_search_marker_search} WHERE landmark = :landmark)";
  return chado_search_bind_dynamic_textfields(array(':landmark' => $val), $id, $sql);
}
// User defined: Populating the linkage group for selected map
function chado_search_marker_search_ajax_linkage_group ($val) {
  $sql = "SELECT distinct lg_uniquename FROM {chado_search_marker_search} WHERE map_name = :map_name ORDER BY lg_uniquename";
  return chado_search_bind_dynamic_select(array(':map_name' => $val), 'lg_uniquename', $sql);
}
