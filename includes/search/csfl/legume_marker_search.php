<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_marker_search_form ($form) {
  // Search by Name
  $form->addTextFilter(
      Set::textFilter()
      ->id('marker_uniquename')
      ->title('Marker Name')
      ->labelWidth(120)
      );
  $form->addMarkup(
      Set::markup()
      ->id('marker_example')
      ->text("(e.g. Hi04e04, CPPCT016, UFFxa16H07)")
      );
  $form->addFile(
      Set::file()
      ->id('feature_name_file_inline')
      ->labelWidth(1)
      ->newLine()
      );
  // Restricted by Features
  $icon = '/' . drupal_get_path('module', 'chado_search') . '/theme/images/question.gif';
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('marker_type')
      ->title('Marker Type <a href="/marker_type"><img src="' . $icon . '"></a>')
      ->column('marker_type')
      ->table('chado_search_marker_search')
      ->cache(TRUE)
      ->labelWidth(120)
      ->newLine()
      );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('mapped_organism')
      ->title('Marker Mapped in Species')
      ->column('mapped_organism')
      ->table('chado_search_marker_search')
      ->multiple(TRUE)
      ->labelWidth(220)
      ->cache(TRUE)
      );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Marker Developed from Species')
      ->column('organism')
      ->table('chado_search_marker_search')
      ->multiple(TRUE)
      ->cache(TRUE)
      ->disable(array('N/A N/A'))
      ->labelWidth(260)
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
      ->cache(TRUE)
      );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('linkage_group')
      ->title('Linkage Group')
      ->dependOnId('map_name')
      ->callback('chado_search_marker_search_ajax_linkage_group')
      ->labelWidth(120)
      );
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('start')
      ->title("between")
      ->id2('stop')
      ->title2("and")
      ->labelWidth2(50)
      ->size(10)
      );
  $form->addMarkup(
      Set::markup()
      ->id('linkage_group_unit')
      ->text("<strong>cM</strong>")
      );
  $form->addSubmit();
  $form->addReset();
  $desc =
  'Search for markers. Users can perform combinatorial queries using categories
such as name, type, species, anchored position in genome and/or genetic map position.
     <b>| ' . l('Short video tutorial', 'https://youtu.be/dSnCoMo2CLA', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', '/UserManual') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
     $form->addFieldset(
         Set::fieldset()
         ->id('top_level')
         ->startWidget('marker_uniquename')
         ->endWidget('reset')
         ->description($desc)
         );
     return $form;
}

// Submit the form
function chado_search_marker_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_marker_search_base_query();
  // Add conditions
  $where [] = Sql::textFilterOnMultipleColumns('marker_uniquename', $form_state, array('marker_uniquename', 'marker_name', 'alias', 'synonym'));
  $where [] = Sql::fileOnMultipleColumns('feature_name_file_inline', array('marker_uniquename', 'marker_name', 'alias', 'synonym'));
  $where [] = Sql::selectFilter('marker_type', $form_state, 'marker_type');
  $where [] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [] = Sql::selectFilter('mapped_organism', $form_state, 'mapped_organism');
  $where [] = Sql::selectFilter('map_name', $form_state, 'map_name');
  $where [] = Sql::selectFilter('linkage_group', $form_state, 'lg_uniquename');
  $where [] = Sql::betweenFilter('start', 'stop', $form_state, 'start', 'start', TRUE);
  Set::result()
  ->sql($sql)
  ->where($where)
  ->tableDefinitionCallback('chado_search_marker_search_table_definition')
  ->customDownload(array('disable_default' => TRUE, 'chado_search_marker_search_custom_download' => 'Table'))
  ->execute($form, $form_state);
}

/*************************************************************
 * SQL
 */
// Define query for the base table. Do not include the WHERE clause
function chado_search_marker_search_base_query() {
  $query = "SELECT * FROM {chado_search_marker_search}";
  return $query;
}

/*************************************************************
 * Build the search result table
 */
// Define the result table
function chado_search_marker_search_table_definition () {
  $headers = array(
    'marker_name:s:chado_search_marker_search_link_feature:marker_feature_id' => 'Name',
    'alias:s' => 'Alias',
    'marker_type:s' => 'Type',
    'organism:s' => 'Species',
    'map_name:s:chado_search_marker_search_link_featuremap:featuremap_id' => 'Map',
    'lg_uniquename:s' => 'Linkage Group',
    'start:s' => 'Position',
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_marker_search_link_feature ($feature_id) {
  return chado_search_link_entity('feature', $feature_id);

}

// Define call back to link the featuremap to its  node for result table
function chado_search_marker_search_link_featuremap ($featuremap_id) {
  return chado_search_link_entity('featuremap', $featuremap_id);

}

/*************************************************************
 * AJAX callbacks
 */
// Downloading file ajax callback
function chado_search_marker_search_download_fasta_definition () {
  return 'marker_feature_id';
}

// User defined: Populating the linkage group for selected map
function chado_search_marker_search_ajax_linkage_group ($val) {
  $sql = "SELECT distinct lg_uniquename FROM {chado_search_marker_search} WHERE map_name = :map_name ORDER BY lg_uniquename";
  return chado_search_bind_dynamic_select(array(':map_name' => $val), 'lg_uniquename', $sql);
}

function chado_search_marker_search_download_definition () {
  $headers = array(
    'marker_feature_id' => 'Feature_id',
    'marker_name' => 'Name',
    'alias' => 'Alias',
    'marker_type' => 'Type',
    'organism_id' => 'Organism_id',
    'organism' => 'Species',
    'map_name' => 'Map',
    'lg_uniquename' => 'Linkage Group',
    'start' => 'Start',
    'stop' => 'Stop',
    'location' => 'Location'
  );
  return $headers;
}
// Custom download for GDR
function chado_search_marker_search_custom_download ($handle, $result, $sql) {
  global $base_url;
  // Get max no of primers
  $primer_count = "
      SELECT count(*)
      FROM {feature} F
      INNER JOIN {feature_relationship} FR ON F.feature_id = FR.subject_id
      INNER JOIN {feature} P ON P.feature_id = FR.object_id
      WHERE
      F.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'genetic_marker' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'sequence'))
      AND
      P.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'primer' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'sequence'))
      AND
      FR.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'adjacent_to' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'relationship'))
      AND F.feature_id = Marker.marker_feature_id
      GROUP BY F.feature_id";
  $max_sql = "SELECT max(count) FROM (SELECT ($primer_count) AS count FROM ($sql) Marker) T";
  $max_no = chado_query($max_sql)->fetchField();
  // Write header
  fwrite($handle, "\"Name\",\"Alias\",\"Type\",\"Species\",\"Map\",\"Linkage Group\",\"Start\",\"Stop\",\"Location\",\"Citation\"");
  for ($i = 1; $i <= $max_no; $i ++) {
    fwrite($handle, ",\"Primer$i name\",\"Primer$i sequence\"");
  }
  fwrite($handle, "\n");
  // Get result with primers and feature/organism nid
  $sql_primers = "
      SELECT string_agg(P.name || '::' || P.residues, '||')
      FROM {feature} F
      INNER JOIN {feature_relationship} FR ON F.feature_id = FR.subject_id
      INNER JOIN {feature} P ON P.feature_id = FR.object_id
      WHERE
      F.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'genetic_marker' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'sequence'))
      AND
      P.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'primer' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'sequence'))
      AND
      FR.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'adjacent_to' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'relationship'))
      AND F.feature_id = Marker.marker_feature_id
      GROUP BY F.feature_id";
  $sql_citation = "
      SELECT string_agg(value, ';') AS citation
      FROM {pubprop} PP
      INNER JOIN {feature_pub} FP ON FP.pub_id = PP.pub_id
      WHERE PP.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'Citation' AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'tripal_pub'))
      AND FP.feature_id = marker_feature_id
      GROUP BY FP.feature_id
      ";
  $sql = "SELECT *, ($sql_primers) AS primers, ($sql_citation) AS citation, (SELECT nid FROM chado_feature WHERE feature_id = marker_feature_id) AS feature_nid, (SELECT nid FROM chado_organism WHERE organism_id = Marker.organism_id) AS organism_nid FROM ($sql) Marker";
  $result = chado_query($sql);
  // Write reults
  while ($obj = $result->fetchObject()) {
    fwrite($handle, "\"=HYPERLINK(\"\"$base_url/node/$obj->feature_nid\"\",\"\"$obj->marker_name\"\")\",\"$obj->alias\",\"$obj->marker_type\",\"=HYPERLINK(\"\"$base_url/node/$obj->organism_nid\"\",\"\"$obj->organism\"\")\",\"$obj->map_name\",\"$obj->lg_uniquename\",\"$obj->start\",\"$obj->stop\",\"$obj->location\",\"$obj->citation\"");
    $primers = explode('||', $obj->primers);
    foreach ($primers AS $primer) {
      $primer_info = explode('::', $primer);
      $pname = $primer_info[0];
      $pseq = $primer_info[1];
      fwrite($handle, ",\"$pname\",\"$pseq\"");
    }
    fwrite($handle, "\n");
  }
}
