<?php
// Create 'germplasm_search' MView
function chado_search_create_species_mview() {
  $view_name = 'chado_search_species';
  chado_search_drop_mview($view_name);
  $schema = array(
  'table' => $view_name,
  'fields' => array(
    'organism_id' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'genus' => array(
      'type' => 'varchar',
      'length' => 255,
    ),
    'species' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
    ),
    'organism' => array(
      'type' => 'varchar',
      'length' => 510,
      'not null' => TRUE,
    ),
    'common_name' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => FALSE,
    ),
    'grin' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => FALSE,
    ),
    'haploid_chromosome_number' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'ploidy' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'geographic_origin' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'num_germplasm' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'num_sequences' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'num_libraries' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
  ),
  'indexes' => array(
    'species_summary_idx0' => array('organism_id'),
  ),
  'foreign keys' => array (
    'organism' => array (
      'table' => 'organism',
      'columns' => array(
        'organism_id' => 'organism_id',
      ),
    ),
  ),
);
  $sql = "
SELECT 
  organism_id, 
  genus,
  species,
  genus || ' ' || species AS organism,
  common_name,
  (SELECT accession FROM dbxref X INNER JOIN db ON db.db_id = X.db_id INNER JOIN organism_dbxref OD ON X.dbxref_id = OD.dbxref_id WHERE OD.organism_id = O.organism_id AND db.name = 'GRIN Taxonomy') AS grin,
  (SELECT OP.value
   FROM organismprop OP 
     INNER JOIN cvterm CVT_OP on CVT_OP.cvterm_id = OP.type_id
   WHERE CVT_OP.name = 'haploid_chromosome_number' AND OP.organism_id = O.organism_id) as haploid_chromosome_number,
  (SELECT OP.value
   FROM organismprop OP 
     INNER JOIN cvterm CVT_OP on CVT_OP.cvterm_id = OP.type_id
   WHERE CVT_OP.name = 'ploidy' AND OP.organism_id = O.organism_id) as ploidy,
  (SELECT OP.value
   FROM organismprop OP 
     INNER JOIN cvterm CVT_OP on CVT_OP.cvterm_id = OP.type_id
   WHERE CVT_OP.name = 'geographic_origin' AND OP.organism_id = O.organism_id) as geographic_origin,
  (SELECT count(*)
   FROM stock S
   WHERE S.organism_id = O.organism_id AND S.type_id <> (SELECT cvterm_id FROM cvterm WHERE name = 'sample' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) as num_germplasm,
  (SELECT count(*)
   FROM feature F
   WHERE F.organism_id = O.organism_id) as num_sequences,
  (SELECT count(*)
   FROM library L
   WHERE L.organism_id = O.organism_id) as num_libraries
FROM organism O
ORDER BY genus, species
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
