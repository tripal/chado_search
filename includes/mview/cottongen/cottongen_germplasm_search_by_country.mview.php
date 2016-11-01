<?php
// Create 'germplasm_search_by_country' MView
function chado_search_create_germplasm_search_by_country_mview() {
  $view_name = 'chado_search_germplasm_search_by_country';
  chado_search_drop_mview($view_name);
  $schema = array (
    'table' => $view_name,
    'fields' => array (
      'stock_id' => array (
        'type' => 'int'
      ),
      'name' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'uniquename' => array (
        'type' => 'text'
      ),
      'organism_id' => array (
        'type' => 'int'
      ),
      'organism' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'stock_type' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'country' => array (
        'type' => 'text'
      ),
      'state' => array (
        'type' => 'text'
      )
    )
  );
  $sql = "
  SELECT DISTINCT
    S.stock_id,
    S.name,
    S.uniquename,
    S.organism_id,
    O.genus || ' ' || o.species AS organism,
    V.name AS stock_type,
    GEO.country,
    GEO.state
  FROM stock S
  INNER JOIN organism O ON O.organism_id = S.organism_id
  INNER JOIN cvterm V ON V.cvterm_id = S.type_id
  INNER JOIN
    (SELECT 
      stock_id,
      COUNTRY.value AS country,
      STATE.value AS state
     FROM nd_experiment NE
     LEFT JOIN nd_geolocation NG ON NG.nd_geolocation_id = NE.nd_geolocation_id
     LEFT JOIN 
       (SELECT nd_geolocation_id, value FROM nd_geolocationprop NGP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'country' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) COUNTRY ON COUNTRY.nd_geolocation_id = NG.nd_geolocation_id
     LEFT JOIN 
      (SELECT nd_geolocation_id, value FROM nd_geolocationprop NGP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'state' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) STATE ON STATE.nd_geolocation_id = NG.nd_geolocation_id
     INNER JOIN nd_experiment_stock NES ON NES.nd_experiment_id = NE.nd_experiment_id
     GROUP BY NES.stock_id, COUNTRY.value, STATE.value
    ) GEO ON S.stock_id = GEO.stock_id
  WHERE S.type_id <> (SELECT cvterm_id FROM cvterm WHERE name = 'sample' AND cv_id =(SELECT cv_id FROM cv WHERE name = 'MAIN'))
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
