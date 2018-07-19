<?php
// Create 'germplasm_search' MView
function chado_search_create_featuremap_mview() {
  $view_name = 'chado_search_featuremap';
  chado_search_drop_mview($view_name);
  $schema = array (
  'table' => $view_name,
  'fields' => array (
    'featuremap_id' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'featuremap' => array(
      'type' => 'varchar',
      'length' => 256,
      'not null' => FALSE,
    ),
    'organism' => array(
      'type' => 'varchar',
      'length' => 512,
      'not null' => FALSE,
    ),
    'genus' => array(
      'type' => 'varchar',
      'length' => 256,
      'not null' => FALSE,
    ),
    'species' => array(
      'type' => 'varchar',
      'length' => 256,
      'not null' => FALSE,
    ),
    'organism_id' => array(
      'type' => 'int',
    ),
    'pop_stock_id' => array(
      'type' => 'int',
    ),      
    'maternal_stock_id' => array(
      'type' => 'int',
    ),
    'maternal_stock_uniquename' => array(
      'type' => 'varchar',
      'length' => '255',
    ),
    'paternal_stock_id' => array(
      'type' => 'int',
    ),
    'paternal_stock_uniquename' => array(
      'type' => 'varchar',
      'length' => '255',
    ),
    'genome' => array(
      'type' => 'text',
    ),     
    'pop_size' => array(
      'type' => 'text',
    ),
    'pop_type' => array(
      'type' => 'text',
    ),
    'num_of_lg' => array(
      'type' => 'int',
    ),
    'num_of_loci' => array(
      'type' => 'int',
    ),
  ),
);
  $sql = "
SELECT 
  FM.featuremap_id, 
  FM.name AS featuremap_name,
  ORG.organism, 
  ORG.genus,
  ORG.species,
  ORG.organism_id,
  STK.stock_id            AS pop_stock_id, 
  MATP.stock_id   AS maternal_stock_id,
  MATP.uniquename AS maternal_stock_uniquename,
  PATP.stock_id   AS paternal_stock_id,
  PATP.uniquename AS paternal_stock_uniquename,
  (SELECT first(value) 
   FROM featuremapprop FMprop
     INNER JOIN cvterm CVT ON FMprop.type_id = CVT.cvterm_id
   WHERE CVT.name = 'genome_group' AND FMprop.featuremap_id = FM.featuremap_id
  ) AS genome,
  (SELECT first(value) 
   FROM stockprop Sprop
     INNER JOIN cvterm CVT ON Sprop.type_id = CVT.cvterm_id
   WHERE CVT.name = 'population_size' AND Sprop.stock_id = STK.stock_id
  ) AS pop_size,
  (SELECT value 
   FROM featuremapprop FMprop
     INNER JOIN cvterm CVT ON FMprop.type_id = CVT.cvterm_id
   WHERE CVT.name = 'population_type' AND FMprop.featuremap_id = FM.featuremap_id
  ) AS pop_type,
  (SELECT count (distinct F.uniquename) FROM featurepos FPos INNER JOIN feature F ON F.feature_id = FPos.map_feature_id 
   WHERE F.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'linkage_group')
   AND Fpos.featuremap_id = FM.featuremap_id
   ) AS num_of_lg,
  (SELECT count (F.uniquename) FROM featurepos FPos INNER JOIN feature F ON F.feature_id = FPos.feature_id 
   WHERE F.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'marker_locus' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))
   AND  Fpos.featuremap_id = FM.featuremap_id
   ) AS num_of_loci 
FROM featuremap FM
LEFT JOIN (
 SELECT 
      FO.featuremap_id, 
      O.genus || ' ' || O.species AS organism,
      O.genus,
      O.species,
      O.organism_id
 FROM featuremap_organism FO 
 INNER JOIN organism O ON FO.organism_id = O.organism_id
) ORG ON ORG.featuremap_id = FM.featuremap_id
LEFT JOIN (
 SELECT S.stock_id, S.uniquename, FMS.featuremap_id FROM stock S 
 INNER JOIN featuremap_stock FMS ON S.stock_id = FMS.stock_id
) STK ON STK.featuremap_id = FM.featuremap_id
LEFT JOIN (
 SELECT MAT.stock_id, MAT.uniquename, MSR.object_id FROM stock MAT 
 INNER JOIN stock_relationship MSR ON MAT.stock_id = MSR.subject_id
 WHERE MSR.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'is_a_maternal_parent_of')
) MATP ON MATP.object_id = STK.stock_id
LEFT JOIN (
 SELECT PAT.stock_id, PAT.uniquename, PSR.object_id FROM stock PAT 
 INNER JOIN stock_relationship PSR ON PAT.stock_id = PSR.subject_id
 WHERE PSR.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'is_a_paternal_parent_of')
) PATP ON PATP.object_id = STK.stock_id 
ORDER BY FM.featuremap_id
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
