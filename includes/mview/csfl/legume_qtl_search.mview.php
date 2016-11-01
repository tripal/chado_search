<?php
// Create 'germplasm_search' MView
function chado_search_create_qtl_search_mview() {
  $view_name = 'chado_search_qtl_search';
  chado_search_drop_mview($view_name);
  $schema = array (
  'table' => 'chado_search_qtl_search',
  'fields' => array(
    'feature_id' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'qtl' => array(
      'type' => 'text',
      'not null' => TRUE,
    ),
    'organism_id' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'organism' => array(
      'type' => 'varchar',
      'length' => '510',
      'not null' => TRUE,
    ),
    'symbol' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'trait' => array(
      'type' => 'varchar',
      'length' => '255',
      'NOT NULL' => FALSE,
    ),
    'category' => array(
      'type' => 'varchar',
      'length' => '1024',
      'NOT NULL' => FALSE,
    ),
    'category_filter' => array(
      'type' => 'text',
      'NOT NULL' => FALSE,
    ),
    'featuremap_id' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'map' => array(
      'type' => 'text',
      'NOT NULL' => FALSE,
    ),
    'col_marker_nid' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'col_marker_uniquename' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'neighbor_marker_nid' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'neighbor_marker_uniquename' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'study_project_id' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'study' => array(
      'type' => 'varchar',
      'length' => '255',
      'not null' => FALSE,
    ),
    'pop_nid' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'population' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'lod' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'r2' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'ad_ratio' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'ref_pub_id' => array(
      'type' => 'int',
      'not null' => FALSE,
    ),
    'reference' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'aliases' => array(
      'type' => 'text',
      'not null' => FALSE,
    ),
    'type' => array(
      'type' => 'varchar',
      'length' => '100',
      'not null' => TRUE,
    ),
  ),
  'indexes' => array(
    'QTL_search_indx0' => array('organism'),
    'QTL_search_indx1' => array('qtl'),
    'QTL_search_indx2' => array('symbol'),
    'QTL_search_indx3' => array('trait'),
  ),
);
  $sql = "
SELECT 
  max(QTL.feature_id) AS feature_id,
  max(QTL.uniquename) AS qtl, 
  max(O.organism_id) AS organism_id,
  max(O.genus || ' ' || O.species) AS organism,
  max(SYMBOL.value) AS symbol,
  max(QTL.name) AS trait,
  max(CATEGORY.obj) AS category,
  '::' || string_agg(distinct CATEGORY.obj, '::') || '::' AS category_filter,  
  max(MAP.featuremap_id) AS featuremap_id,
  string_agg(distinct MAP.name, '; ') AS map,
  max(CO_LOC_M.nid) AS coloc_marker_nid,
  string_agg(distinct CO_LOC_M.uniquename, '; ') AS colocalizing_marker,
  max(NEIGHBOR_M.nid) AS neighbor_marker_nid,
  string_agg(distinct NEIGHBOR_M.uniquename, '; ') AS neighboring_marker,
  max(STUDY.project_id) AS study_project_id,
  string_agg(distinct STUDY.name, '; ') AS study,
  max(POP.nid) AS pop_nid,
  string_agg(distinct POP.uniquename, '; ') AS population,
  string_agg(distinct LOD.value, '; ') AS lod,
  string_agg(distinct R2.value, '; ') AS r2,
  string_agg(distinct ADR.value, '; ') AS ad_ratio,
  max(PUB.pub_id) AS ref_pub_id,
  string_agg(distinct PUB.uniquename, '; ') AS reference,
  max(ALIASES.trait_aliases),
  CASE WHEN max(QTLTYPE.name) = 'heritable_phenotypic_marker' THEN 'MTL' ELSE max(QTLTYPE.name) END AS type
FROM feature QTL
INNER JOIN organism O ON O.organism_id = QTL.organism_id
LEFT JOIN 
  (SELECT 
   FC.feature_id,
   SUBJ.name AS subj,
   TYPE.name AS type,
   OBJ.name AS obj 
   FROM cvterm SUBJ
   INNER JOIN cvterm_relationship VR ON SUBJ.cvterm_id = VR.subject_id
   INNER JOIN cvterm TYPE ON TYPE.cvterm_id = VR.type_id
   INNER JOIN cvterm OBJ ON OBJ.cvterm_id = VR.object_id
   INNER JOIN feature_cvterm FC ON FC.cvterm_id = SUBJ.cvterm_id
   WHERE OBJ.cv_id = (SELECT cv_id FROM cv WHERE name = 'csfl_trait_ontology')
   AND TYPE.name = 'is_a'
  ) CATEGORY ON CATEGORY.feature_id = QTL.feature_id
LEFT JOIN
  (SELECT feature_id, FM.name, FM.featuremap_id FROM featuremap FM
   INNER JOIN featurepos FP ON FM.featuremap_id = FP.featuremap_id
  ) MAP ON MAP.feature_id = QTL.feature_id
LEFT JOIN 
  (SELECT MKR.uniquename, FR.object_id, MKR.feature_id, nid FROM feature MKR
   INNER JOIN feature_relationship FR ON MKR.feature_id = FR.subject_id
   INNER JOIN cvterm V ON V.cvterm_id = FR.type_id
   LEFT JOIN chado_feature CF ON CF.feature_id = MKR.feature_id
   WHERE V.name = 'located_in' 
   AND V.cv_id = (SELECT cv_id FROM cv WHERE name = 'relationship')
  ) CO_LOC_M ON CO_LOC_M.object_id = QTL.feature_id
LEFT JOIN 
  (SELECT MKR.uniquename, FR.object_id, MKR.feature_id, nid FROM feature MKR
   INNER JOIN feature_relationship FR ON MKR.feature_id = FR.subject_id
   INNER JOIN cvterm V ON V.cvterm_id = FR.type_id
   LEFT JOIN chado_feature CF ON CF.feature_id = MKR.feature_id
   WHERE V.name = 'adjacent_to'
   AND V.cv_id = (SELECT cv_id FROM cv WHERE name = 'relationship')
  ) NEIGHBOR_M ON NEIGHBOR_M.object_id = QTL.feature_id
LEFT JOIN 
  (SELECT string_agg(value, '; ') as value, feature_id FROM featureprop FP
   WHERE type_id = 
    (SELECT cvterm_id FROM cvterm
     WHERE name = 'LOD' 
     AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')
    )
   GROUP BY feature_id
   ) LOD ON LOD.feature_id = QTL.feature_id
LEFT JOIN 
  (SELECT string_agg(value, '; ') as value, feature_id FROM featureprop FP
   WHERE type_id = 
    (SELECT cvterm_id FROM cvterm
     WHERE name = 'R2' 
     AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')
    )
   GROUP BY feature_id
   ) R2 ON R2.feature_id = QTL.feature_id
LEFT JOIN 
  (SELECT string_agg(value, '; ') as value, feature_id FROM featureprop FP
   WHERE type_id = 
    (SELECT cvterm_id FROM cvterm
     WHERE name = 'additivity_dominance_ratio' 
     AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')
    )
   GROUP BY feature_id
   ) ADR ON ADR.feature_id = QTL.feature_id
LEFT JOIN 
  (SELECT P.pub_id, uniquename, feature_id FROM pub P
   INNER JOIN feature_pub FP ON FP.pub_id = P.pub_id
   ) PUB ON PUB.feature_id = QTL.feature_id
LEFT JOIN 
  (SELECT P.project_id, name, feature_id FROM project P
   INNER JOIN feature_project FP ON FP.project_id = P.project_id
   ) STUDY ON STUDY.feature_id = QTL.feature_id
LEFT JOIN
  (SELECT S.stock_id, featuremap_id, S.uniquename, nid FROM stock S
   INNER JOIN featuremap_stock FS ON S.stock_id = FS.stock_id
   LEFT JOIN chado_stock CS ON CS.stock_id = S.stock_id
  ) POP ON POP.featuremap_id = MAP.featuremap_id
LEFT JOIN 
  (SELECT value, feature_id FROM featureprop FP
   WHERE type_id = 
    (SELECT cvterm_id FROM cvterm
     WHERE name = 'published_symbol' 
     AND cv_id = (SELECT cv_id FROM cv WHERE name = 'MAIN')
    )
   ) SYMBOL ON SYMBOL.feature_id = QTL.feature_id
INNER JOIN 
  (SELECT cvterm_id, V.name FROM cvterm V 
   INNER JOIN cv ON cv.cv_id = V.cv_id
   WHERE cv.name = 'sequence'
  ) QTLTYPE ON QTLTYPE.cvterm_id = QTL.type_id
INNER JOIN 
  (SELECT 
   F.feature_id,
   CASE 
   WHEN max(S.name) IS NULL 
   THEN '::' || max(F.name) || '::'
   ELSE
   '::' || string_agg(distinct F.name, '::') || '::' || string_agg(distinct S.name, '::') || '::'
   END as trait_aliases
   FROM feature F 
   LEFT JOIN feature_synonym FS ON F.feature_id = FS.feature_id
   LEFT JOIN synonym S ON FS.synonym_id = S.synonym_id
   WHERE F.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'QTL')
   OR F.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'heritable_phenotypic_marker')
   GROUP BY F.feature_id
  ) ALIASES ON ALIASES.feature_id = QTL.feature_id   
WHERE QTLTYPE.name = 'QTL'
OR QTLTYPE.name = 'heritable_phenotypic_marker' 
GROUP BY QTL.feature_id      
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
