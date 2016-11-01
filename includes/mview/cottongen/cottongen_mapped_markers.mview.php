<?php
// Create 'marker_search' MView
function chado_search_create_mapped_markers_mview() {
  $view_name = 'chado_search_mapped_markers';
  chado_search_drop_mview($view_name);
  $schema = array (
    'table' => $view_name,
    'fields' => array (
      'marker_feature_id' => array (
        'type' => 'int'
      ),
      'marker_uniquename' => array (
        'type' => 'text'
      ),
      'locus_uniquename' => array (
        'type' => 'text'
      ),
      'chr_number' => array (
        'type' => 'text'
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
      'lg_uniquename' => array (
        'type' => 'text'
      ),
      'marker_type' => array (
        'type' => 'text'
      ),
      'start' => array (
        'type' => 'float'
      ),
      'stop' => array (
        'type' => 'float'
      )
    )
  );
  $sql =
  "SELECT DISTINCT
      MARKER.feature_id AS marker_feature_id,
      MARKER.uniquename AS marker_uniquename,
      MAP.locus_uniquename,
      Chr_number.value AS chr_number,
      SEQUENCEOF.feature_id AS seq_feature_id,
      SEQUENCEOF.seq_name AS seq_name,
      O.organism_id,
      O.genus || ' ' || O.species AS organism,
      FM.featuremap_id,
      FM.name AS map_name,
      LG.uniquename AS lg_uniquename,
      MTYPE.value AS marker_type,
      cast(START.value as real) AS start,
      cast(STOP.value as real) AS stop
      FROM feature MARKER
      INNER JOIN organism O ON O.organism_id = MARKER.organism_id
      INNER JOIN
      (SELECT
      object_id,
      featuremap_id,
      map_feature_id,
      featurepos_id,
      LOCUS.uniquename AS locus_uniquename
      FROM feature LOCUS
      INNER JOIN feature_relationship FR ON FR.subject_id = LOCUS.feature_id
      INNER JOIN featurepos FS ON FS.feature_id = LOCUS.feature_id
      WHERE LOCUS.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'marker_locus' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))
      AND FR.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'instance_of' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'relationship'))
      ) MAP ON MAP.object_id = MARKER.feature_id
      LEFT JOIN (SELECT feature_id, value FROM featureprop FP WHERE FP.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'chr_number')) Chr_number ON Chr_number.feature_id = MAP.map_feature_id
      LEFT JOIN (SELECT FR.object_id, V.name, seq.name AS seq_name, seq.feature_id, V2.name AS seq_type FROM feature seq INNER JOIN feature_relationship FR ON seq.feature_id = FR.subject_id INNER JOIN cvterm V ON cvterm_id = FR.type_id INNER JOIN cvterm V2 on V2.cvterm_id = seq.type_id WHERE FR.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'sequence_of')) SEQUENCEOF ON SEQUENCEOF.object_id = MARKER.feature_id
      INNER JOIN featuremap FM ON FM.featuremap_id = MAP.featuremap_id
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
      WHERE MARKER.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'genetic_marker' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}