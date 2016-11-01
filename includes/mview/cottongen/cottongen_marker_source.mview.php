<?php
// Create 'marker_source' MView
function chado_search_create_marker_source_mview() {
  $view_name = 'chado_search_marker_source';
  chado_search_drop_mview ( $view_name );
  $schema = array (
    'table' => $view_name,
    'fields' => array (
      'marker_feature_id' => array (
        'type' => 'int' 
      ),
      'marker_uniquename' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'marker_type' => array (
        'type' => 'text'
      ),
      'src_feature_id' => array (
        'type' => 'int' 
      ),
      'src_uniquename' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'src_type' => array (
        'type' => 'text'
      ),
      'library_id' => array (
        'type' => 'int'
      ),
      'library_name' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'stock_id' => array (
        'type' => 'int'
      ),
      'stock_uniquename' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'organism_id' => array (
        'type' => 'int'
      ),
      'organism' => array (
        'type' => 'varchar',
        'length' => '510' 
      ),
    ) 
  );
  $sql = "SELECT
    F.feature_id,
    F.uniquename AS marker,
    MTYPE.value AS marker_type,
    SEQF.feature_id AS seq_feature_id,
    SEQF.uniquename AS seq_uniquename,
    SRC.value AS src_type,
    SLIB.library_id,
    SLIB.name AS library,
    STK.stock_id,
    STK.uniquename AS stock,
    O.organism_id,
    concat(O.genus, ' ', O.species) AS organism
    FROM feature F
    LEFT JOIN (SELECT feature_id, value 
           FROM featureprop FP
           WHERE type_id = (SELECT cvterm_id 
                            FROM cvterm 
                            WHERE name = 'marker_type' 
                            AND cv_id = (SELECT cv_id 
                                         FROM cv 
                                         WHERE name = 'MAIN'))) 
          MTYPE ON MTYPE.feature_id = F.feature_id
    LEFT JOIN (SELECT feature_id, uniquename, object_id 
            FROM feature SEQ 
            INNER JOIN feature_relationship FR ON SEQ.feature_id = FR.subject_id 
            WHERE SEQ.type_id = (SELECT cvterm_id 
                                 FROM cvterm 
                                 WHERE name = 'sequence_feature' 
                                 AND cv_id = (SELECT cv_id 
                                              FROM cv 
                                              WHERE name = 'sequence'))) 
           SEQF ON SEQF.object_id = F.feature_id
    INNER JOIN organism O ON O.organism_id = F.organism_id
    INNER JOIN (SELECT feature_id, value 
           FROM featureprop FP
           WHERE type_id = (SELECT cvterm_id 
                            FROM cvterm 
                            WHERE name = 'source' 
                            AND cv_id = (SELECT cv_id 
                                         FROM cv 
                                         WHERE name = 'MAIN'))) 
          SRC ON SRC.feature_id = F.feature_id
    LEFT JOIN (SELECT S.stock_id, name, uniquename, feature_id 
            FROM stock S
      INNER JOIN feature_stock FS ON S.stock_id = FS.stock_id)
       STK ON F.feature_id = STK.feature_id
    LEFT JOIN (SELECT stock_id, L.library_id, L.name, L.uniquename 
           FROM library L
       INNER JOIN library_stock LS ON L.library_id = LS.library_id)
      SLIB ON STK.stock_id = SLIB.stock_id
    WHERE type_id = (SELECT cvterm_id 
                 FROM cvterm 
                 WHERE name = 'genetic_marker' 
                 AND cv_id = (SELECT cv_id 
                              FROM cv 
                              WHERE name = 'sequence'))";
  tripal_add_mview ( $view_name, 'chado_search', $schema, $sql, '' );
}