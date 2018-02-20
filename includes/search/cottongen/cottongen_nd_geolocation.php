<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_nd_geolocation_form ($form) {
  $form->addSubmit();
  return $form;
}

// Submit the form
function chado_search_nd_geolocation_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_nd_geolocation}";
  Set::result()
    ->sql($sql)
    ->tableDefinitionCallback('chado_search_nd_geolocation_table_definition')
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_nd_geolocation_table_definition () {
  $headers = array(
    'description:s:chado_search_link_nd_geolocation:nd_geolocation_id' => 'Environment',
    'project_name:s:chado_search_link_project:project_id' => 'Associated Project',
    'project_type:s' => 'Project Type',
    'latitude:s' => 'Latitude',
    'longitude:s' => 'Longitude',
    'altitude:s' => 'Altitude',
    'country:s' => 'Country',
    'region:s' => 'Region',
    'type:s' => 'Type',
    'year:s' => 'Year'
  );
  return $headers;
}