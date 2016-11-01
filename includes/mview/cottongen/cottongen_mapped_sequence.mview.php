<?php
// Create 'marker_search' MView
function chado_search_create_mapped_sequence_mview() {
  $view_name = 'chado_search_mapped_sequence';
  chado_search_drop_mview($view_name);
  $schema = array (
  'table' => $view_name,
  'fields' => array (
    'organism_id' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'featuremap_id' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'featuremap' => array(
      'type' => 'varchar',
      'length' => '255'
    ),
    'genome' => array(
      'type' => 'varchar',
      'length' => '8',
    ),
    'chr_name' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'chr_number' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'lg_feature_id' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'linkage_group' => array(
      'type' => 'varchar',
      'length' => '255',
    ),
    'lg_type' => array(
      'type' => 'varchar',
      'length' => '255',
    ),
    'locus_feature_id' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'locus_name' => array(
      'type' => 'varchar',
      'length' => '255',
    ),
    'locus_type' => array(
      'type' => 'varchar',
      'length' => '1024',
    ),
    'locus_marker_rel' => array(
      'type' => 'varchar',
      'length' => '1024',
    ),
    'locus_start' => array(
      'type' => 'float',
    ),
    'locus_stop' => array(
      'type' => 'float',
    ),
    'marker_feature_id' => array(
      'type' => 'int',
    ),
    'marker_name' => array(
      'type' => 'varchar',
      'length' => '255',
    ),
    'marker_type_id' => array(
      'type' => 'int',
    ),
    'marker_seq_rel' => array(
      'type' => 'varchar',
      'length' => '1024',
    ), 
    'sequence_feature_id' => array(
      'type' => 'int',
    ),
    'sequence_name' => array(
      'type' => 'varchar',
      'length' => '255',
    ),
    'sequence_type' => array(
      'type' => 'varchar',
      'length' => '1024',
    ),
    'sequence_accession' => array(
      'type' => 'varchar',
      'length' => '255',
    ),
  ),
);
  $sql =
  "SELECT 
O.organism_id,
FM.featuremap_id,
FM.name,
GENOME.value AS genome,
Chr_name.value AS chr_name,
Chr_number.value AS chr_number,
LG.feature_id AS lg_feature_id,
LG.name AS linkage_group,
LG_TYPE.name,
Marker_locus.feature_id AS locus_feature_id,
Marker_locus.uniquename AS locus_name,
ML_TYPE.name AS locus_type,
INSTANCEOF.name AS locus_marker_rel,
cast(start.value as float8) AS locus_start,
cast(stop.value as float8) AS locus_stop,
INSTANCEOF.feature_id AS maker_feature_id,
INSTANCEOF.marker_name,
MTYPE.marker_type_id AS marker_type_id,
SEQUENCEOF.name AS marker_seq_rel,
SEQUENCEOF.feature_id           AS sequence_feature_id,
SEQUENCEOF.seq_name                 AS sequence_name,
SEQUENCEOF.seq_type               AS sequence_type,
ACC.accession AS sequence_accession
FROM featuremap FM 
LEFT JOIN featurepos FP ON fm.featuremap_id = fp.featuremap_id 
LEFT JOIN feature Marker_locus ON FP.feature_id = Marker_locus.feature_id 
INNER JOIN cvterm V ON V.cvterm_id = Marker_locus.type_id
INNER JOIN Organism O ON Marker_locus.organism_id = O.organism_id
LEFT JOIN (SELECT featuremap_id, value FROM featuremapprop FMP INNER JOIN cvterm ON type_id = cvterm_id WHERE cvterm.name = 'genome_group') GENOME ON GENOME.featuremap_id = FM.featuremap_id
LEFT JOIN (SELECT feature_id, value FROM featureprop FP WHERE FP.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'chr_name')) Chr_name ON Chr_name.feature_id = FP.map_feature_id
LEFT JOIN (SELECT feature_id, value FROM featureprop FP WHERE FP.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'chr_number')) Chr_number ON Chr_number.feature_id = FP.map_feature_id
LEFT JOIN feature LG ON LG.feature_id = FP.map_feature_id
INNER JOIN cvterm LG_TYPE ON LG.type_id = LG_TYPE.cvterm_id
INNER JOIN cvterm ML_TYPE ON Marker_locus.type_id = ML_TYPE.cvterm_id
LEFT JOIN (SELECT FR.subject_id, V.name, genetic_marker.name AS marker_name, genetic_marker.feature_id, genetic_marker.type_id FROM feature genetic_marker INNER JOIN feature_relationship FR ON genetic_marker.feature_id = FR.object_id INNER JOIN cvterm V ON cvterm_id = FR.type_id WHERE FR.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'instance_of')) INSTANCEOF ON INSTANCEOF.subject_id = Marker_locus.feature_id
LEFT JOIN (SELECT featurepos_id, value 
                       FROM featureposprop 
                       WHERE type_id = (SELECT cvterm_id 
                                        FROM cvterm 
                                        WHERE name = 'start'
                                        AND cv_id = (SELECT cv_id FROM cv
                                                     WHERE name = 'MAIN'))
                       ) START ON START.featurepos_id = FP.featurepos_id
LEFT JOIN (SELECT featurepos_id, value 
                       FROM featureposprop 
                       WHERE type_id = (SELECT cvterm_id 
                                        FROM cvterm 
                                        WHERE name = 'stop'
                                        AND cv_id = (SELECT cv_id FROM cv
                                                     WHERE name = 'MAIN'))
                       ) STOP ON STOP.featurepos_id = FP.featurepos_id
             
LEFT JOIN (SELECT feature_id, value, marker_type_id FROM featureprop INNER JOIN search_marker_type ON marker_type_name = value where type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'marker_type')) MTYPE ON MTYPE.feature_id = INSTANCEOF.feature_id
LEFT JOIN (SELECT FR.object_id, V.name, seq.name AS seq_name, seq.feature_id, V2.name AS seq_type FROM feature seq INNER JOIN feature_relationship FR ON seq.feature_id = FR.subject_id INNER JOIN cvterm V ON cvterm_id = FR.type_id INNER JOIN cvterm V2 on V2.cvterm_id = seq.type_id WHERE FR.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'sequence_of')) SEQUENCEOF ON SEQUENCEOF.object_id = INSTANCEOF.feature_id
LEFT JOIN (SELECT FD.feature_id, accession FROM feature_dbxref FD INNER JOIN dbxref ON FD.dbxref_id = dbxref.dbxref_id) ACC ON ACC.feature_id = INSTANCEOF.feature_id
      ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}