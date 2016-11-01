<?php
// Create 'snp_genotype_search' MView
function chado_search_create_snp_genotype_search_mview() {
  $view_name = 'chado_search_snp_genotype_search';
  chado_search_drop_mview($view_name);
  $schema =  array (
    'table' => $view_name,
    'fields' => array (
      'project_id' => array(
        'type' => 'int'
      ),
      'project_name' => array(
        'type' => 'varchar',
        'length' => '255'
      ),
      'pub_id' => array(
        'type' => 'int'
      ),
      'citation' => array(
        'type' => 'text'
      ),
      'organism_id' => array(
        'type' => 'int'
      ),
      'organism' => array(
        'type' => 'varchar',
        'length' => '510'
      ),
      'stock_id' => array(
        'type' => 'int'
      ),
      'stock_name' => array(
        'type' => 'varchar',
        'length' => '255'
      ), 
      'stock_uniquename' => array(
        'type' => 'text'
      ),
      'feature_id' => array(
        'type' => 'int'
      ),
      'feature_name' => array(
        'type' => 'varchar',
        'length' => '255'
      ),
      'feature_uniquename' => array(
        'type' => 'text'
      ),
      'filename' => array(
        'type' => 'text'
      )
    )
  );
  $sql = "
    SELECT
      P.project_id,
      P.name AS project_name,
      PUB.pub_id,
      PUB.uniquename AS citation,
      S.organism_id,
      (SELECT genus || ' ' || species FROM organism WHERE organism_id = S.organism_id) AS species,
      S.stock_id,
      S.name AS stock_name,
      S.uniquename AS stock_uniquename,
      F.feature_id,
      F.name AS feature_name,
      F.uniquename AS feature_uniquenaem,
      FL.value AS filename
    FROM project P
    LEFT JOIN project_pub PPUB ON PPUB.project_id = P.project_id
    LEFT JOIN pub PUB ON PPUB.pub_id = PUB.pub_id
    INNER JOIN feature_project FP ON P.project_id = FP.project_id
    INNER JOIN feature F ON F.feature_id = FP.feature_id
    INNER JOIN stock_project SP ON P.project_id = SP.project_id
    INNER JOIN stock S ON S.stock_id = SP.stock_id
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'project_type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) PTYPE ON PTYPE.project_id = P.project_id 
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'sub_type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) SUBTYPE ON SUBTYPE.project_id = P.project_id 
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'permission' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) PERM ON PERM.project_id = P.project_id 
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'filename' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) FL ON FL.project_id = P.project_id 
    WHERE 
      PTYPE.value = 'genotyping'
    AND
      SUBTYPE.value = 'SNP'
    AND
      PERM.value = 'public'
      ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}