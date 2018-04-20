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
      'allele' => array(
        'type' => 'text'
      ),
      'genotype' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
    )
  );
  $sql = "
    SELECT
      P.project_id,
      P.name AS project_name,
      S.organism_id,
      (SELECT genus || ' ' || species FROM organism WHERE organism_id = S.organism_id) AS species,
      S.stock_id,
      S.name AS stock_name,
      S.uniquename AS stock_uniquename,
      F.feature_id,
      F.name AS feature_name,
      F.uniquename AS feature_uniquenaem,
  --- Select Allele
      (SELECT value FROM featureprop WHERE feature_id = F.feature_id AND type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'SNP' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))) AS allele,
  --- Select genotype
      (SELECT description FROM genotype WHERE genotype_id = GC.genotype_id) AS genotype
    FROM genotype_call GC
    INNER JOIN project P ON P.project_id = GC.project_id
    INNER JOIN feature F ON F.feature_id = GC.feature_id
    INNER JOIN stock S ON S.stock_id = GC.stock_id
  --- Get project type
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'project_type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) PTYPE ON PTYPE.project_id = P.project_id
  --- Get project sub_type      
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'sub_type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) SUBTYPE ON SUBTYPE.project_id = P.project_id
  --- Get permission
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'permission' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) PERM ON PERM.project_id = P.project_id
--- Restrict to SNP genotyping public projects
    WHERE 
      PTYPE.value = 'genotyping'
    AND
      SUBTYPE.value = 'SNP'
    AND
      PERM.value = 'public'
      ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
  chado_search_create_snp_genotype_location_mview();
}

function chado_search_create_snp_genotype_location_mview() {
  $view_name = 'chado_search_snp_genotype_location';
  chado_search_drop_mview($view_name);
  $schema =  array (
    'table' => $view_name,
    'fields' => array (
      'feature_id' => array(
        'type' => 'int'
      ),
      'genome' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'landmark_feature_id' => array (
        'type' => 'int'
      ),
      'landmark' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'fmin' => array (
        'type' => 'int'
      ),
      'fmax' => array (
        'type' => 'int'
      ),
      'location' => array (
        'type' => 'varchar',
        'length' => '510'
      ),
    )
  );
  $sql = "
    SELECT
      DISTINCT
      GC.feature_id,
  --- Select genome name
        (
         SELECT name FROM analysis A
         WHERE
           (
             (SELECT value FROM analysisprop
              WHERE analysis_id = A.analysis_id
              AND type_id =
                  (SELECT cvterm_id FROM cvterm WHERE name = 'Analysis Type')
             ) = 'whole_genome'
           )
         AND
           (
             (SELECT analysis_id FROM analysisfeature AF
              WHERE AF.feature_id = LOC.srcfeature_id
             ) = A.analysis_id
           )
        ) AS genome,
        LOC.srcfeature_id AS landmark_feature_id,
        LOC.name AS landmark,
        LOC.fmin,
        LOC.fmax,
        LOC.name || ':' || (fmin + 1) || '..' || fmax AS location
    FROM genotype_call GC
    INNER JOIN project P ON P.project_id = GC.project_id
  --- Get project type
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'project_type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) PTYPE ON PTYPE.project_id = P.project_id
  --- Get project sub_type
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'sub_type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) SUBTYPE ON SUBTYPE.project_id = P.project_id
  --- Get permission
    INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'permission' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) PERM ON PERM.project_id = P.project_id
  --- Get genome location
      INNER JOIN
        (SELECT
           max(FL.feature_id) AS feature_id,
           max(srcfeature_id) AS srcfeature_id,
           max(F.name) AS name,
           max(F.uniquename) AS uniquename,
           max(fmin) AS fmin,
           max(fmax) AS fmax
        FROM featureloc FL
        INNER JOIN feature F ON F.feature_id = FL.srcfeature_id
        INNER JOIN feature F2 ON F2.feature_id = FL.feature_id
        WHERE
        --- Alignments to the 'chromosome' or 'supercontig'
          (F.type_id IN (SELECT cvterm_id FROM cvterm WHERE name IN ('chromosome', 'supercontig') AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
        --- Alignments to the 'contig' for M x domestica
           OR (F.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'contig' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
                AND F.organism_id = (SELECT organism_id FROM organism WHERE genus = 'Malus' AND species = 'x domestica'))
          )
      AND F2.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'genetic_marker' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      GROUP BY (FL.feature_id, srcfeature_id, F.name, F.uniquename, fmin, fmax)
      ) LOC ON LOC.feature_id = GC.feature_id
 --- Restrict to SNP genotyping public projects
    WHERE
      PTYPE.value = 'genotyping'
    AND
      SUBTYPE.value = 'SNP'
    AND
      PERM.value = 'public'
      ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}