<?php
// Create 'germplasm_search_by_collection' MView
function chado_search_create_germplasm_search_by_collection_mview() {
  $view_name = 'chado_search_germplasm_search_by_collection';
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
        'length' => '510'
      ),
      'genome' => array (
        'type' => 'text'
      ),
      'db' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'accession' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'version' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'urlprefix' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'collection' => array (
        'type' => 'varchar',
        'length' => '255'
      )
    )
  );
  $sql = "
    SELECT DISTINCT
      S.stock_id,
      S.name,
      S.uniquename,
      O.organism_id,
      O.genus || ' ' || O.species AS organism,
      GENOME.value AS genome,
      DATA.name AS db,
      DATA.accession,
      DATA.version,
      DATA.urlprefix,
      COLLECTION.name AS collection
    FROM stock S
    INNER JOIN organism O ON S.organism_id = O.organism_id
    LEFT JOIN (
      SELECT organism_id, value 
      FROM organismprop OP
      WHERE type_id = (
        SELECT cvterm_id 
        FROM cvterm 
        WHERE name = 'genome_group'
        AND cv_id = (
          SELECT cv_id 
          FROM cv
          WHERE name = 'MAIN'
        )
      )
    ) GENOME ON GENOME.organism_id = O.organism_id
    LEFT JOIN (
      SELECT stock_id, accession, name, urlprefix, version
      FROM dbxref X
      INNER JOIN db ON X.db_id = db.db_id
      INNER JOIN stock_dbxref SX ON X.dbxref_id = SX.dbxref_id
    ) DATA ON DATA.stock_id = S.stock_id
    INNER JOIN (
      SELECT name, uniquename, stock_id 
      FROM stockcollection SC 
      INNER JOIN stockcollection_stock SCS ON SC.stockcollection_id = SCS.stockcollection_id
    ) COLLECTION ON COLLECTION.stock_id = S.stock_id
    WHERE S.type_id <> (SELECT cvterm_id FROM cvterm WHERE name = 'sample' AND cv_id =(SELECT cv_id FROM cv WHERE name = 'MAIN'))
    AND DATA.name = COLLECTION.uniquename
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
