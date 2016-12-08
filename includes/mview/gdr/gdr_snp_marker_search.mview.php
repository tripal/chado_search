<?php
// Create 'marker_search' MView
function chado_search_create_snp_marker_search_mview() {
  $view_name = 'chado_search_snp_marker_search';
  chado_search_drop_mview ( $view_name );
  $schema = array (
    'table' => $view_name,
    'fields' => array (
      'snp_feature_id' => array (
        'type' => 'int'
      ),
      'snp_uniquename' => array (
        'type' => 'text'
      ),
      'snp_name' => array(
        'type' => 'varchar',
        'length' => '255'
      ),
      'library_id' => array (
        'type' => 'int'
      ),
      'array_name' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'array_id' => array (
        'type' => 'varchar',
        'length' => '255'
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
      'alias' => array (
        'type' => 'text'
      ),
      'allele' => array (
        'type' => 'text'
      ),
      'flanking_sequence' => array (
        'type' => 'text'
      )
    )
  );
  $sql = "SELECT
  SNP.feature_id,
  SNP.uniquename AS snp_uniquename,
  SNP.name AS snp_name,
  L.library_id,
  L.name AS snp_array_name,
  ARR_ID.name AS array_id,  
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
  LOC.uniquename AS landmark,
  LOC.fmin,
  LOC.fmax,
  LOC.name || ':' || (fmin + 1) || '..' || fmax AS location,
  ALIAS.value AS alias,
  ALLELE.value AS allele,
  SNP.residues AS flanking_sequence
--- Base Table
FROM feature SNP
--- Get maker_type
INNER JOIN
  (SELECT DISTINCT feature_id, value FROM featureprop
   WHERE type_id =
    (SELECT cvterm_id FROM cvterm
     WHERE name = 'marker_type'
     AND cv_id =
       (SELECT cv_id FROM cv WHERE name = 'MAIN')
    )
  ) MTYPE ON SNP.feature_id = MTYPE.feature_id
--- Get SNP array name
LEFT JOIN feature_synonym FS ON SNP.feature_id = FS.feature_id
LEFT JOIN library_synonym LS ON FS.synonym_id = LS.synonym_id
LEFT JOIN library L ON L.library_id = LS.library_id
--- Get SNP array ID
LEFT JOIN
  (SELECT DISTINCT synonym_id, name FROM synonym WHERE type_id =
    (SELECT cvterm_id FROM cvterm WHERE name = 'SNP_chip' AND cv_id =
      (SELECT cv_id FROM cv WHERE name = 'MAIN')
    )
  ) ARR_ID ON ARR_ID.synonym_id = FS.synonym_id
--- Get aliases
LEFT JOIN
  (SELECT DISTINCT feature_id, value FROM featureprop WHERE type_id =
     (SELECT cvterm_id FROM cvterm
      WHERE name = 'alias'
      AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')
     )
  ) ALIAS ON ALIAS.feature_id = SNP.feature_id
--- Get allele
LEFT JOIN
  (SELECT DISTINCT feature_id, value FROM featureprop WHERE type_id =
     (SELECT cvterm_id FROM cvterm
      WHERE name = 'allele'
      AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence')
     )
  ) ALLELE ON ALLELE.feature_id = SNP.feature_id
--- Get genome location
      LEFT JOIN
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
      --- Alignments to the 'contig' for M x domestica (Disabled)
      ---   OR (F.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'contig' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      ---         AND F.organism_id = (SELECT organism_id FROM organism WHERE genus = 'Malus' AND species = 'x domestica'))
        )
      AND F2.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'genetic_marker' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      GROUP BY (FL.feature_id, srcfeature_id, F.name, F.uniquename, fmin, fmax)
      ) LOC ON LOC.feature_id = SNP.feature_id
--- Limit to genetic_marker and SNP
WHERE SNP.type_id =
  (SELECT cvterm_id FROM cvterm WHERE name = 'genetic_marker'
   AND cv_id =
     (SELECT cv_id FROM cv WHERE name = 'sequence')
  )
AND MTYPE.value IN ('SNP', 'SNP/Indel', 'Indel')";
  tripal_add_mview ( $view_name, 'chado_search', $schema, $sql, '' );
}