<?php
// Create 'germplasm_search_by_pedigree' MView
function chado_search_create_germplasm_search_by_pedigree_mview() {
  $view_name = 'chado_search_germplasm_search_by_pedigree';
  chado_search_drop_mview($view_name);
  $schema = array (
    'table'=> $view_name,
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
      'pedigree' => array (
        'type' => 'text'
      )
    )
  );
  $sql = "
    SELECT DISTINCT
      S.stock_id,
      S.name,
      S.uniquename,
      PEDIGREE.value AS pedigree
    FROM stock S
    INNER JOIN (
      SELECT stock_id, value 
      FROM stockprop
      WHERE type_id = (
        SELECT cvterm_id 
        FROM cvterm 
        WHERE name = 'pedigree'
        AND cv_id = (
          SELECT cv_id 
          FROM cv
          WHERE name = 'MAIN'
        )
      )
    ) PEDIGREE ON PEDIGREE.stock_id = S.stock_id
    WHERE S.type_id <> (SELECT cvterm_id FROM cvterm WHERE name = 'sample' AND cv_id =(SELECT cv_id FROM cv WHERE name = 'MAIN'))
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
