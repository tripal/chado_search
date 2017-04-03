<?php
// Create 'sequence_search' MView
function chado_search_create_sequence_search_mview() {
  $view_name = 'chado_search_sequence_search';
  chado_search_drop_mview ( $view_name );
  $schema = array (
    'table' => $view_name,
    'fields' => array (
      'feature_id' => array (
        'type' => 'int' 
      ),
      'name' => array (
        'type' => 'varchar',
        'length' => '255' 
      ),
      'uniquename' => array (
        'type' => 'text' 
      ),
      'feature_type' => array (
        'type' => 'varchar',
        'length' => '1025' 
      ),
      'organism_id' => array (
        'type' => 'int' 
      ),
      'organism' => array (
        'type' => 'varchar',
        'length' => '510' 
      ),
      'genus' => array (
        'type' => 'varchar',
        'length' => 255
      ),
      'species' => array (
        'type' => 'varchar',
        'length' => 255
      ),
      'analysis_id' => array (
        'type' => 'int' 
      ),
      'analysis_name' => array (
        'type' => 'varchar',
        'length' => '255' 
      ),
      'srcfeature_id' => array (
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
      ) 
    ) 
  );
  $sql = "SELECT DISTINCT
      F.feature_id,
      F.name,
      F.uniquename,
      FV.name AS feature_type,
      O.organism_id,
      O.genus || ' ' || O.species AS organism,
      O.genus,
      O.species,
      ANA.analysis_id,
      ANA.name AS analysis_name,
      LOC.srcfeature_id,
      LOC.name AS landmark,
      (fmin + 1),
      fmax,
      LOC.name || ':' || (fmin + 1) || '..' || fmax AS location
      FROM analysisfeature AF
      LEFT JOIN
      ((SELECT 
                GENE.feature_id,
                LMARK.srcfeature_id,
                LMARK.fmin,
                LMARK.fmax,
                LMARK.name
                FROM Feature GENE
                INNER JOIN featureloc GMATLOC ON GMATLOC.srcfeature_id = GENE.feature_id
                INNER JOIN (
                  SELECT 
                    LMATLOC.feature_id, 
                    LMATLOC.srcfeature_id, 
                    LMATLOC.fmin, 
                    LMATLOC.fmax,
                    CHR.name
                  FROM Feature CHR
                  INNER JOIN featureloc LMATLOC ON LMATLOC.srcfeature_id = CHR.feature_id
                  WHERE (SELECT type_id FROM feature F WHERE F.feature_id = LMATLOC.feature_id) = (SELECT cvterm_id FROM cvterm WHERE name = 'match' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
                  AND CHR.type_id IN (SELECT cvterm_id FROM cvterm WHERE name IN ('chromosome', 'supercontig') AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
                  ) LMARK ON LMARK.feature_id = GMATLOC.feature_id
                  WHERE GENE.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'gene' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))) 
            UNION
                (SELECT FL.feature_id, srcfeature_id, fmin, fmax, F.name FROM featureloc FL
                  INNER JOIN feature F ON F.feature_id = FL.srcfeature_id
                 WHERE F.type_id IN (SELECT cvterm_id FROM cvterm WHERE name IN ('chromosome', 'supercontig') AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
                )
            ) LOC ON LOC.feature_id = AF.feature_id
      INNER JOIN
      (SELECT A.analysis_id, A.name FROM analysis A
      LEFT JOIN analysisprop AP ON AP.analysis_id = A.analysis_id
      WHERE AP.value IN ('reftrans', 'unigene', 'whole_genome', 'tripal_analysis_unigene', 'bulk_data', 'ncbi_data', 'transcriptome', 'other_transcripts')
      AND A.name != 'GDR Gene Database'
      ) ANA ON ANA.analysis_id = AF.analysis_id
      INNER JOIN feature F ON F.feature_id = AF.feature_id
      INNER JOIN organism O ON F.organism_id = O.organism_id
      INNER JOIN cvterm FV ON FV.cvterm_id = F.type_id
      WHERE F.type_id IN (SELECT cvterm_id FROM cvterm WHERE name IN ('gene', 'mRNA', 'contig', 'R_motif', 'R_group', 'EST') AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))";
  tripal_add_mview ( $view_name, 'chado_search', $schema, $sql, '' );
}