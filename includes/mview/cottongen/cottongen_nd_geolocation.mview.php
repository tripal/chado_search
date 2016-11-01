<?php
// Create 'germplasm_search' MView
function chado_search_create_nd_geolocation_mview() {
  $view_name = 'chado_search_nd_geolocation';
  chado_search_drop_mview($view_name);
  $schema = array(
  'table' => $view_name,
  'fields' => array(
    'nd_geolocation_id' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'nd_geolocation_nid' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'description' => array(
      'type' => 'varchar',
      'length' => '255',
      'not null' => FALSE,
    ),
    'latitude' => array(
      'type' => 'numeric',
      'not null' => FALSE,
    ),
    'longitude' => array(
      'type' => 'numeric',
      'not null' => FALSE,
    ),
    'geodetic_datum' => array(
      'type' => 'varchar',
      'length' => '32',
      'not null' => FALSE,
    ),
    'altitude' => array(
      'type' => 'numeric',
      'not null' => FALSE,
    ),
    'project_id' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'project_nid' => array(
      'type' => 'int',
      'not null' => FALSE,
    ), 
    'project_name' => array(
      'type' => 'varchar',
      'length' => '255',
      'not null' => FALSE
    ),
    'project_type' => array(
      'type' => 'varchar',
      'length' => '1024',
      'not null' => FALSE
    ),
    'country' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'state' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'region' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'type' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'year' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
  ),
);
  $sql = "
SELECT 
  L.nd_geolocation_id,
  (SELECT nid FROM chado_nd_geolocation WHERE nd_geolocation_id = L.nd_geolocation_id),
  description,
  latitude,
  longitude,
  geodetic_datum,
  altitude,
  PROJ.project_id,
  (SELECT nid FROM chado_project WHERE project_id = PROJ.project_id),
  PROJ.name AS project_name,
  PROJ.type AS project_type,
  (SELECT string_agg(distinct value, '; ') FROM nd_geolocationprop NDP WHERE NDP.nd_geolocation_id = L.nd_geolocation_id AND type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'country' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')))  AS country,
  (SELECT string_agg(distinct value, '; ') FROM nd_geolocationprop NDP WHERE NDP.nd_geolocation_id = L.nd_geolocation_id AND type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'state' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')))  AS state,
  (SELECT string_agg(distinct value, '; ') FROM nd_geolocationprop NDP WHERE NDP.nd_geolocation_id = L.nd_geolocation_id AND type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'region' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')))  AS state,
  (SELECT string_agg(distinct value, '; ') FROM nd_geolocationprop NDP WHERE NDP.nd_geolocation_id = L.nd_geolocation_id AND type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')))  AS type,
  (SELECT string_agg(distinct value, '; ') FROM nd_geolocationprop NDP WHERE NDP.nd_geolocation_id = L.nd_geolocation_id AND type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'data_year' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')))  AS year
FROM nd_geolocation L
LEFT JOIN 
(SELECT DISTINCT 
   P.project_id, 
   P.name, 
   (SELECT name FROM cvterm WHERE cvterm_id = NE.type_id) AS type, 
   NE.nd_geolocation_id 
 FROM project P 
 INNER JOIN nd_experiment_project NP ON NP.project_id = P.project_id 
 INNER JOIN nd_experiment NE ON NE.nd_experiment_id = NP.nd_experiment_id
) PROJ ON PROJ.nd_geolocation_id = L.nd_geolocation_id
WHERE L.nd_geolocation_id <> 0
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
