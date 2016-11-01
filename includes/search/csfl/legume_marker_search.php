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
      ->table('chado_search_marker_search')
      ->labelWidth(120)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('mapped_organism')
      ->title('Marker Mapped in Species')
      ->column('mapped_organism')
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
      ->id('map_name')
      ->title('Map')
      ->column('map_name')
      ->table('chado_search_marker_search')
      ->labelWidth(120)
      ->newLine()
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
      ->size(10)
      ->labelWidth(65)
      ->labelWidth2(50)
  );
  $form->addMarkup(
      Set::markup()
      ->id('linkage_group_unit')
      ->text("<strong>cM</strong>")
  );
  $form->addFieldset(
      Set::fieldset()
      ->id('marker_search_by_location')
      ->title("Restrict by Location")
      ->startWidget('map_name')
      ->endWidget('linkage_group_unit')
   );
  
  $form->addSubmit();
  $form->addReset();
  
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
  $where [0] = Sql::textFilterOnMultipleColumns('marker_uniquename', $form_state, array('marker_uniquename', 'alias'));
  $where [1] = Sql::file('marker_uniquename_file', 'marker_uniquename');
  $where [2] = Sql::selectFilter('marker_type', $form_state, 'marker_type');
  $where [3] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [4] = Sql::selectFilter('mapped_organism', $form_state, 'mapped_organism');
  $where [5] = Sql::selectFilter('map_name', $form_state, 'map_name');
  $where [6] = Sql::selectFilter('linkage_group', $form_state, 'lg_uniquename');
  $where [7] = Sql::betweenFilter('start', 'stop', $form_state, 'start', 'start');
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_marker_search_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_marker_search_table_definition () {
  $headers = array(      
      'marker_uniquename:s:chado_search_marker_search_link_feature:marker_feature_id' => 'Name',
      'alias:s' => 'Alias',
      'marker_type:s' => 'Type',
      'organism:s:chado_search_marker_search_link_organism:organism_id' => 'Species',
      'map_name:s:chado_search_marker_search_link_featuremap:featuremap_id' => 'Map',
      'lg_uniquename:s' => 'Linkage Group',
      'start:s' => 'Start',
      'stop:s' => 'Stop'
  );
  return $headers;
}

// Define call back to link the featuremap to its  node for result table
function chado_search_marker_search_link_feature ($feature_id) {
  $nid = chado_get_nid_from_id('feature', $feature_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the featuremap to its  node for result table
function chado_search_marker_search_link_organism ($organism_id) {
  $nid = chado_get_nid_from_id('organism', $organism_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the featuremap to its  node for result table
function chado_search_marker_search_link_featuremap ($featuremap_id) {
  $nid = chado_get_nid_from_id('featuremap', $featuremap_id);
  return chado_search_link_node ($nid);
}

// User defined: Populating the linkage group for selected map
function chado_search_marker_search_ajax_linkage_group ($val) {
  $sql = "SELECT distinct lg_uniquename FROM {chado_search_marker_search} WHERE map_name = :map_name ORDER BY lg_uniquename";
  return chado_search_bind_dynamic_select(array(':map_name' => $val), 'lg_uniquename', $sql);
}
