<?php
// Create 'ssr_genotype_search' MView
function chado_search_create_ssr_genotype_search_mview() {
  $view_name = 'chado_search_ssr_genotype_search';
  chado_search_drop_mview($view_name);
  $schema = array (
    'table' => $view_name,
    'fields' => array (
      'feature_id' => array(
        'type' => 'int',
      ),
      'marker_uniquename' => array(
        'type' => 'text',
      ),
      'marker_organism_id' => array(
        'type' => 'int',
      ),
      'genotype_id' => array(
        'type' => 'int'
      ),
      'genotype' => array(
        'type' => 'text'
      ),
      'allele' => array(
        'type' => 'varchar',
        'length' => '255'
      ),
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
      'stock_id' => array(
        'type' => 'int'
      ),
      'stock_uniquename' => array(
        'type' => 'text'
      ),
      'organism_id' => array(
        'type' => 'int'
      ),
      'organism' => array(
        'type' => 'varchar',
        'length' => '255'
      )
    )
  );
  $sql = "
    SELECT
    DISTINCT
      F.feature_id,
      F.uniquename AS marker_uniquename,
    F.organism_id,
    G.genotype_id,
      G.uniquename AS genotype, 
      regexp_split_to_table(G.description, '\|') AS allele,
      P.project_id,
      P.name AS project,
      PUB.pub_id,
      PUB.uniquename AS citation,
      S.stock_id,
      S.uniquename AS stock_uniquename,
      O.organism_id,
      O.genus || ' ' || O.species AS organism
  FROM genotype G 
  INNER JOIN feature_genotype FG ON G.genotype_id = FG.genotype_id 
  INNER JOIN feature F ON FG.feature_id = F.feature_id
  INNER JOIN nd_experiment_genotype NEG ON G.genotype_id = NEG.genotype_id
  INNER JOIN nd_experiment_project NEP ON NEP.nd_experiment_id = NEG.nd_experiment_id
  INNER JOIN project P ON P.project_id = NEP.project_id
  LEFT JOIN project_pub PPUB ON PPUB.project_id = P.project_id
  LEFT JOIN pub PUB ON PPUB.pub_id = PUB.pub_id
  INNER JOIN nd_experiment_stock NDS ON NEG.nd_experiment_id = NDS.nd_experiment_id
  INNER JOIN stock EXP ON EXP.stock_id = NDS.stock_id
  INNER JOIN stock_relationship SR ON SR.subject_id = EXP.stock_id
  INNER JOIN stock S ON S.stock_id = SR.object_id
  INNER JOIN organism O ON O.organism_id = S.organism_id
  INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'project_type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) PTYPE ON PTYPE.project_id = P.project_id 
  INNER JOIN (SELECT * FROM projectprop PP WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'sub_type' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))) SUBTYPE ON SUBTYPE.project_id = P.project_id
  WHERE SR.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'sample_of' AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN'))
  AND 
    PTYPE.value = 'genotyping'
  AND
    SUBTYPE.value = 'SSR'
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}