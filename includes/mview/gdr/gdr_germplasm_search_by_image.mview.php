<?php
// Create 'germplasm_search_by_image' MView
function chado_search_create_germplasm_search_by_image_mview() {
  $view_name = 'chado_search_germplasm_search_by_image';
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
      'genus' => array (
        'type' => 'varchar',
        'length' => 255
      ),
      'species' => array (
        'type' => 'varchar',
        'length' => 255
      ),
      'eimage_id' => array (
        'type' => 'int'
      ),
      'image_uri' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'image_type' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'legend' => array (
        'type' => 'text'
      ),
      'dataset' => array (
        'type' => 'text'
      ),
      'project_id' => array (
        'type' => 'int'
      ),
      'comment' => array (
        'type' => 'text'
      ),
      'alias' => array (
        'type' => 'text'
      )
    )
  );
  $sql = "
SELECT 
S.stock_id,
S.name,
S.uniquename,
S.organism_id,
O.genus || ' ' || o.species AS organism,
O.genus,
O.species,
E.eimage_id,
E.image_uri,
E.eimage_type,
LEGEND.value AS legend,  
DATASET.value AS dataset,
CAST(PROJECT.value AS numeric) AS project_id,
COMMENT.value AS comment,
ALIAS.value AS alias

FROM stock_image SI

INNER JOIN stock S ON S.stock_id = SI.stock_id

INNER JOIN organism O ON O.organism_id = S.organism_id

INNER JOIN eimage E ON SI.eimage_id = E.eimage_id

LEFT JOIN eimageprop LEGEND ON LEGEND.eimage_id = E.eimage_id AND LEGEND.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'legend' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))

LEFT JOIN eimageprop DATASET ON DATASET.eimage_id = E.eimage_id AND DATASET.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'dataset_name' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))

LEFT JOIN eimageprop PROJECT ON PROJECT.eimage_id = E.eimage_id AND PROJECT.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'project_id' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))

LEFT JOIN eimageprop COMMENT ON COMMENT.eimage_id = E.eimage_id AND COMMENT.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'comments' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))

    LEFT JOIN (
      SELECT stock_id, value 
      FROM stockprop
      WHERE type_id = (
        SELECT cvterm_id 
        FROM cvterm 
        WHERE name = 'alias'
        AND cv_id = (
          SELECT cv_id 
          FROM cv
          WHERE name = 'MAIN'
        )
      )
    ) ALIAS ON ALIAS.stock_id = S.stock_id
      WHERE (DATASET.value is null or DATASET.value !~ '(_PRV_|_BPS_|Cherry_NO)')
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
