<?php
// Create 'marker_search' MView
function chado_search_create_marker_search_mview() {
  $view_name = 'chado_search_marker_search';
  chado_search_drop_mview ( $view_name );
  $schema = array (
    'table' => $view_name,
    'fields' => array (
      'marker_feature_id' => array (
        'type' => 'int'
      ),
      'marker_uniquename' => array (
        'type' => 'text'
      ),
      'marker_name' => array(
        'type' => 'varchar',
        'length' => '255'
      ),
      'seq_feature_id' => array (
        'type' => 'int'
      ),
      'seq_uniquename' => array (
        'type' => 'text'
      ),
      'organism_id' => array (
        'type' => 'int'
      ),
      'organism' => array (
        'type' => 'varchar',
        'length' => '510'
      ),
      'featuremap_id' => array (
        'type' => 'int'
      ),
      'map_name' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'mapped_organism_id' => array (
        'type' => 'int'
      ),
      'mapped_organism' => array (
        'type' => 'varchar',
        'length' => '510'
      ),
      'lg_uniquename' => array (
        'type'=> 'text'
      ),
      'marker_type' => array (
        'type' => 'text'
      ),
      'start' => array (
        'type' => 'float'
      ),
      'stop' => array (
        'type' => 'float'
      ),
      'landmark_feature_id' => array (
        'type' => 'int'
      ),
      'landmark' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'landmark_organism' => array (
        'type' => 'varchar',
        'length' => '510'
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
      'synonym' => array (
        'type' => 'text'
      )
    )
  );
  $sql = "SELECT DISTINCT
      MARKER.feature_id AS marker_feature_id,
      MARKER.uniquename AS marker_uniquename,
       MARKER.name AS marker_name,
      SEQUENCEOF.feature_id AS seq_feature_id,
      SEQUENCEOF.seq_name AS seq_name,
      O.organism_id,
      O.genus || ' ' || O.species AS organism,
      FM.featuremap_id,
      FM.name AS map_name,
      STK.organism_id,
      STK.mapped_organism,
      LG.uniquename AS lg_uniquename,
      UPPER(replace(MTYPE.value, '_', ' ')) AS marker_type,
      cast(START.value as real) AS start,
      cast(STOP.value as real) AS stop,
      LOC.srcfeature_id AS landmark_feature_id,
      LOC.uniquename AS landmark,
      (SELECT genus || ' ' || species FROM organism WHERE organism_id = (SELECT organism_id FROM feature WHERE feature_id = LOC.srcfeature_id)) AS landmark_organism,
      LOC.fmin,
      LOC.fmax,
      LOC.name || ':' || (fmin + 1) || '..' || fmax AS location,
      ALIAS.value AS alias,
      SYNONYM.value AS synonym
      FROM feature MARKER
      INNER JOIN organism O ON O.organism_id = MARKER.organism_id
      LEFT JOIN
      (SELECT
      object_id,
      featuremap_id,
      map_feature_id,
      featurepos_id
      FROM feature LOCUS
      INNER JOIN feature_relationship FR ON FR.subject_id = LOCUS.feature_id
      INNER JOIN featurepos FS ON FS.feature_id = LOCUS.feature_id
      WHERE LOCUS.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'marker_locus' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      AND FR.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'instance_of' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'relationship'))
      ) MAP ON MAP.object_id = MARKER.feature_id
      LEFT JOIN (SELECT FR.object_id, V.name, seq.name AS seq_name, seq.feature_id, V2.name AS seq_type FROM feature seq INNER JOIN feature_relationship FR ON seq.feature_id = FR.subject_id INNER JOIN cvterm V ON cvterm_id = FR.type_id INNER JOIN cvterm V2 on V2.cvterm_id = seq.type_id WHERE FR.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'sequence_of')) SEQUENCEOF ON SEQUENCEOF.object_id = MARKER.feature_id
      LEFT JOIN featuremap FM ON FM.featuremap_id = MAP.featuremap_id
      LEFT JOIN
      (SELECT featuremap_id, genus || ' ' || species AS mapped_organism, O.organism_id FROM featuremap_stock FMS
      INNER JOIN stock S ON S.stock_id = FMS.stock_id
      INNER JOIN organism O ON S.organism_id = O.organism_id
      ) STK ON STK.featuremap_id = MAP.featuremap_id
      LEFT JOIN feature LG ON LG.feature_id = MAP.map_feature_id
      LEFT JOIN
      (SELECT feature_id, value
      FROM featureprop FP
      WHERE FP.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'marker_type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))
      ) MTYPE ON MTYPE.feature_id = MARKER.feature_id
      LEFT JOIN (SELECT featurepos_id, value
      FROM featureposprop
      WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'start' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))
      ) START ON START.featurepos_id = MAP.featurepos_id
      LEFT JOIN (SELECT featurepos_id, value
      FROM featureposprop
      WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'stop' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))
      ) STOP ON STOP.featurepos_id = MAP.featurepos_id
      LEFT JOIN
      (SELECT
      max(MK.feature_id) AS feature_id,
      max(SRC.feature_id) AS srcfeature_id,
      max(SRC.name) AS name,
      max(SRC.uniquename) AS uniquename,
      max(SRCLOC.fmin) AS fmin,
      max(SRCLOC.fmax)AS fmax
      FROM feature MK
      INNER JOIN featureloc MATCHLOC ON MATCHLOC.srcfeature_id = MK.feature_id
      INNER JOIN featureloc SRCLOC ON MATCHLOC.feature_id = SRCLOC.feature_id
      INNER JOIN feature SRC ON SRC.feature_id  = SRCLOC.srcfeature_id
      WHERE
      MK.type_id =
      (SELECT cvterm_id FROM cvterm WHERE name = 'genetic_marker'
      AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      AND
      SRC.type_id IN
      (SELECT cvterm_id FROM cvterm WHERE name IN ('chromosome', 'supercontig')
      AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      GROUP BY (MK.feature_id, SRC.feature_id, SRC.name, SRCLOC.fmin, SRCLOC.fmax)
      UNION
      SELECT
         max(FL.feature_id) AS feature_id,
         max(srcfeature_id) AS srcfeature_id,
         max(F.name) AS name,
         max(F.uniquename) AS uniquename,
         max(fmin) AS fmin,
         max(fmax) AS fmax
      FROM featureloc FL
      INNER JOIN feature F ON F.feature_id = FL.srcfeature_id
      INNER JOIN feature F2 ON F2.feature_id = FL.feature_id
      WHERE F.type_id IN (SELECT cvterm_id FROM cvterm WHERE name IN ('chromosome', 'supercontig') AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      AND F2.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'genetic_marker' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      GROUP BY (FL.feature_id, srcfeature_id, F.name, F.uniquename, fmin, fmax)
      UNION
      SELECT
         max(FL.feature_id) AS feature_id,
         max(srcfeature_id) AS srcfeature_id,
         max(F.name) AS name,
         max(F.uniquename) AS uniquename,
         max(fmin) AS fmin,
         max(fmax) AS fmax
      FROM featureloc FL
      INNER JOIN feature F ON F.feature_id = FL.srcfeature_id
      INNER JOIN feature F2 ON F2.feature_id = FL.feature_id
      WHERE F.type_id IN (SELECT cvterm_id FROM cvterm WHERE name = 'contig' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      AND F2.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'genetic_marker' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      AND F.organism_id = (SELECT organism_id FROM organism WHERE genus = 'Malus' AND species = 'x domestica')
      GROUP BY (FL.feature_id, srcfeature_id, F.name, F.uniquename, fmin, fmax)
      ) LOC ON LOC.feature_id = MARKER.feature_id
      LEFT JOIN
      (SELECT feature_id, string_agg(value, '; ') AS value FROM featureprop FP
      WHERE FP.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'alias' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))
      GROUP BY feature_id
      ) ALIAS ON ALIAS.feature_id = MARKER.feature_id
      LEFT JOIN
      (SELECT feature_id, string_agg(distinct name, ';') AS value FROM synonym S
       INNER JOIN feature_synonym FS ON S.synonym_id = FS.synonym_id
       GROUP BY feature_id
      ) SYNONYM  ON SYNONYM.feature_id = MARKER.feature_id
      WHERE MARKER.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'genetic_marker' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))";
  tripal_add_mview ( $view_name, 'chado_search', $schema, $sql, '' );
}