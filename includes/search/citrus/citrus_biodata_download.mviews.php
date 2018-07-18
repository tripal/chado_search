<?php 
// Return the definition of the mview
function chado_search_biodata_download_mview_definition () {
  $def = array(
    'chado_search_featuremap' => array(
      'name' => 'Map',
      'distinct' => array('featuremap_id' => 'map'),
      'title' => array(
        'featuremap' => 'Map Name',
        'organism' => 'Organism',
        'maternal_stock_uniquename' => 'Maternal Parent',
        'paternal_stock_uniquename' => 'Paternal Parent',
        'pop_size' => 'Population Size',
        'pop_type' => 'Population Type',
        'num_of_lg' => 'Number of LG',
        'num_of_loci' => 'Number of Loci'
      )
    ),
    'chado_search_gene_search' => array(
      'name' => 'Gene and Transcript',
      'distinct' => array('feature_id' => 'gene/transcript'),
      'title' => array(
        'name' => 'Name',
        'uniquename' => 'Unique Name',
        'organism' => 'Organism',
        'feature_type' => 'Type',
        'location' => 'Location',
        'analysis' => 'Analysis',
        'blast_value' => 'BLAST',
        'kegg_value' => 'KEGG',
        'interpro_value' => 'InterPro',
        'go_term' => 'GO Term',
        'gb_keyword' => 'GenBank Keyword'
      )
    ),
    'chado_search_marker_search' => array(
      'name' => 'Marker',
      'distinct' => array('marker_feature_id' => 'marker'),
      'title' => array(
        'marker_uniquename' => 'Unique Name',
        'marker_name' => 'Marker Name',
        'organism' => 'Organism',
        'map_name' => 'Map',
        'mapped_organism' => 'Mapped Organism',
        'marker_type' => 'Marker Type',
        'start' => 'Start',
        'stop' => 'Stop',
        'location' => 'Location',
        'synonym' => 'Synonym'
      )
    ),
    'chado_search_qtl_search' => array(
      'name' => 'QTL',
      'distinct' => array('feature_id' => 'QTL'),
      'title' => array(
        'qtl' => 'QTL Name',
        'organism' => 'Organism',
        'symbol' => 'Published Symbol',
        'trait' => 'Trait Name',
        'map' => 'Map'
      )
    ),
    'chado_search_sequence_search' => array(
      'name' => 'Sequence',
      'distinct' => array('feature_id' => 'sequence'),
      'title' => array(
        'name' => 'Sequence Name',
        'uniquename' => 'Unique Name',
        'featyre_type' => 'Type',
        'organism' => 'Organism',
        'analysis_name' => 'Analysis',
        'location' => 'Location'
      )
    ),
    'chado_search_germplasm_search' => array(
      'name' => 'Germplasm',
      'distinct' => array('stock_id' => 'germplasm'),
      'title' => array(
        'name' => 'Stock Name',
        'uniquename' => 'Unique Name',
        'organism' => 'Organism',
        'genome' => 'Genome',
        'alias' => 'Alias'
      )
    ),
    'chado_search_species' => array(
      'name' => 'Organism',
      'distinct' => array('organism_id' => 'organism'),
      'title' => array(
        'genus' => 'Genus',
        'species' => 'Species',
        'common_name' => 'Common Name',
        'grin' => 'GRIN',
        'geographic_origin' => 'Geographic Origin',
        'num_germplasm' => 'Number of Germplasm',
        'num_sequences' => 'Number of Sequences',
        'num_libraries' => 'Number of Libraries'
      )
    )
  );
  return $def;
}