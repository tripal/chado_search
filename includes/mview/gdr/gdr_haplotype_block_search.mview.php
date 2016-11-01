<?php
// Create 'gene_search' MView
function chado_search_create_haplotype_block_search_mview() {
  $view_name = 'chado_search_haplotype_block_search';
  chado_search_drop_mview($view_name);
  $schema =  array (
    'table' => $view_name,
    'fields' => array (
      'hb_feature_id' => array (
        'type' => 'int'
      ),
      'haplotype_block' => array (
        'type' => 'text'
      ),
      'organism_id' => array (
        'type' => 'int'
      ),
      'organism' => array (
        'type' => 'varchar',
        'length' => '510'
      ),
      'analysis_id' => array (
        'type' => 'int'
      ),
      'genome' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'landmark' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'fmin' => array (
        'type' => 'int',
      ),
      'fmax' => array (
        'type' => 'int'
      )
    )
  );
  $sql =
  "SELECT 
    HB.feature_id AS hb_feature_id,
    HB.uniquename AS haplotype_block,
    HB.organism_id,
    (SELECT genus || ' ' || species FROM organism O WHERE O.organism_id = HB.organism_id) AS organism,
    GENOME.analysis_id,
    GENOME.name AS genome,
    (SELECT uniquename FROM feature WHERE feature_id = FL.srcfeature_id) AS landmark,
    FL.fmin,
    FL.fmax
  FROM feature HB
  LEFT JOIN featureloc FL ON FL.feature_id = HB.feature_id
  LEFT JOIN (SELECT AF.feature_id, A.analysis_id, A.name FROM analysis A INNER JOIN analysisprop AP ON A.analysis_id = AP.analysis_id INNER JOIN analysisfeature AF ON AF.analysis_id = A.analysis_id WHERE AP.value = 'whole_genome') GENOME ON GENOME.feature_id = FL.srcfeature_id
  WHERE HB.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'haplotype_block' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence'))";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}