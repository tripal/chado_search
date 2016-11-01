<?php
// Create 'germplasm_search' MView
function chado_search_create_gene_search_mview() {
  $view_name = 'chado_search_gene_search';
  chado_search_drop_mview($view_name);
  $schema = array (
    'table' => $view_name,
    'fields' => array (
      'feature_id' => array (
        'type' => 'int',
      ),
      'name' => array (
        'type' => 'varchar',
        'length' => '255',
      ),
      'uniquename' => array (
        'type' => 'text',
      ),
      'seqlen' => array (
        'type' => 'int'
      ),
      'organism' => array (
        'type' => 'varchar',
        'length' => 510
      ),
      'organism_common_name' => array (
        'type' => 'varchar',
        'length' => 255
      ),
      'feature_type' => array (
        'type' => 'varchar',
        'length' => '1024'
      ),
      'analysis' => array (
        'type' => 'varchar',
        'length' => '255'
      ),
      'blast_value' => array (
        'type' => 'text'
      ),
      'kegg_value' => array (
        'type' => 'text'
      ),
      'interpro_value' => array (
        'type' => 'text'
      ),
      'go_term' => array (
        'type' => 'text'
      )
    )
  );
  $sql = "
SELECT
F.feature_id,
F.name AS feature_name,
F.uniquename AS feature_uniquename, 
F.seqlen AS feature_seqlen,
(SELECT genus || ' ' || species FROM organism WHERE organism_id = F.organism_id) AS organism,
(SELECT common_name FROM organism WHERE organism_id = F.organism_id) AS organism_common_name,
(SELECT name FROM cvterm WHERE cvterm_id = F.type_id) AS feature_type,
A.name AS analysis,
-- Blast Best Hit
(SELECT string_agg(distinct 
   (SELECT array_to_string(regexp_matches(value, '<Hit_def>(.+?)</Hit_def>'), '') 
     FROM analysisfeatureprop AFP2 WHERE AFP2.analysisfeatureprop_id = AFP.analysisfeatureprop_id) 
    , '. ')
  FROM analysisfeatureprop AFP
  INNER JOIN analysisfeature AF2 ON AF2.analysisfeature_id = AFP.analysisfeature_id
  WHERE 
    type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'analysis_blast_output_iteration_hits')
  AND
  AF2.feature_id = F.feature_id
) AS blast_value,
-- KEGG
(SELECT string_agg(distinct 
   (SELECT trim(regexp_replace(value, '<.+>', '')) 
     FROM analysisfeatureprop AFP2 WHERE AFP2.analysisfeatureprop_id = AFP.analysisfeatureprop_id) 
    , '. ')
  FROM analysisfeatureprop AFP
  INNER JOIN analysisfeature AF2 ON AF2.analysisfeature_id = AFP.analysisfeature_id
  WHERE 
    type_id IN (SELECT cvterm_id FROM cvterm WHERE cv_id = (SELECT cv_id FROM cv WHERE name = 'KEGG_ORTHOLOGY'  or name = 'KEGG_PATHWAYS'))
  AND
  AF2.feature_id = F.feature_id
) AS kegg_value,
-- Interpro
(
SELECT string_agg(distinct value, '. ') 
FROM (
  SELECT
  AF2.feature_id, 
  array_to_string (regexp_matches(value, 'name=\"(.+?)\"', 'g'), '') AS value
  FROM analysisfeatureprop AFP2
  INNER JOIN analysisfeature AF2 ON AF2.analysisfeature_id = AFP2.analysisfeature_id
  WHERE AFP2.type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'analysis_interpro_xmloutput_hit')
  AND AF2.feature_id = F.feature_id
) INTERPRO GROUP BY feature_id
) AS interpro_value,
-- GO term
(
SELECT string_agg(distinct name, '. ') 
FROM (
  SELECT feature_id,
  V.name
  FROM feature_cvterm FC
  INNER JOIN cvterm V ON V.cvterm_id = FC.cvterm_id
  WHERE FC.feature_id = F.feature_id
  AND cv_id IN (SELECT cv_id FROM cv WHERE name IN ('biological_process', 'cellular_component', 'molecular_function'))
) GOTERM GROUP BY feature_id
) AS go_term
-- Base Table
FROM feature F
INNER JOIN analysisfeature AF ON AF.feature_id = F.feature_id
INNER JOIN analysis A ON A.analysis_id = AF.analysis_id
-- Restrict the sequences to 'unigenes' or 'reftrans
WHERE (A.analysis_id IN (
SELECT analysis_id FROM analysisprop WHERE type_id = (SELECT cvterm_id FROM cvterm WHERE name = 'Analysis Type') AND value IN ('unigene', 'whole_genome', 'bulk_data', 'ncbi_cotton_data'))
)
-- Restrict the sequence type to gene/mRNA/contig
AND F.type_id IN (SELECT cvterm_id FROM cvterm WHERE name IN ('gene', 'mRNA', 'contig') AND cv_id = (SELECT cv_id FROM cv WHERE name = 'sequence')) 
  ";
  tripal_add_mview($view_name, 'chado_search', $schema, $sql, '');
}
