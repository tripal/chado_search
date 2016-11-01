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
    'organism' => array (
      'type' => 'varchar',
      'length' => '510'
    ),
    'genome' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'haploid_chromosome_number' => array(
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
    'organism_nid' => array(
      'type' => 'int',
      'not null' => FALSE
    ),  
  )
);
  $sql = "
SELECT * FROM
(SELECT 
  O.organism_id, 
  O.genus || ' ' || O.species,
  (SELECT OP.value
   FROM organismprop OP 
     INNER JOIN cvterm CVT_OP on CVT_OP.cvterm_id = OP.type_id
   WHERE CVT_OP.name = 'genome_group' AND OP.organism_id = O.organism_id) as genome,
  (SELECT OP.value
   FROM organismprop OP 
     INNER JOIN cvterm CVT_OP on CVT_OP.cvterm_id = OP.type_id
   WHERE CVT_OP.name = 'haploid_chromosome_number' AND OP.organism_id = O.organism_id) as haploid_chromosome_number,
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
   WHERE L.organism_id = O.organism_id) as num_libraries,
  CS.nid as organism_nid
FROM organism O
  INNER JOIN chado_organism CS on O.organism_id = CS.organism_id
ORDER BY O.genus, O.species
) AS t1
WHERE NOT genome IS NULL
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
