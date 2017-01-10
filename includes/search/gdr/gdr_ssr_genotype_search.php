<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_ssr_genotype_search_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('ssr_genotype_tabs')
      ->items(array('/search/ssr_genotype' => 'SSR Genotype', '/search/snp_genotype' => 'SNP Genotype'))
  );
  
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('project_name')
      ->title('Dataset')
      ->column('project_name')
      ->table('chado_search_ssr_genotype_search')
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('marker_uniquename')
      ->title('Marker Name')
  );
  $form->addMarkup(
      Set::markup()
      ->id('marker_example')
      ->text("(e.g. GD12, EPDCU5100)")
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('stock_uniquename')
      ->title('Germplasm Name')
      ->column('stock_uniquename')
      ->table('chado_search_ssr_genotype_search')
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_ssr_genotype_search')
      ->multiple(TRUE)
  );

  $form->addSubmit();
  $form->addReset();
  $desc =
  "Search SSR Genotype is a page where users can search the SSR genotype data by dataset 
      name, marker name, germplasm name and/or species. Click the next tab to search for 
      SNP Genotype. To search for SSR genotype data only for cultivars and breeding selections 
      please visit the <a href=\"/legacy/bt_search_genotype/by_variety\">'Search Genotyping 
      Data'</a> page in the <a href=\"/legacy/breeders_toolbox\">Breeders Toolbox</a>.
     <b> | " . l('Short video tutorial', 'https://www.youtube.com/watch?v=ARZGxKz5mRo', array('attributes' => array('target' => '_blank'))) . ' | ' . l('Text tutorial', 'tutorial/search_snp_genotype') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('ssr_genotype_search')
      ->startWidget('project_name')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_ssr_genotype_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_ssr_genotype_search_base_query();
  
  // Add conditions
  $where [0] = Sql::selectFilter('project_name', $form_state, 'project_name');
  $where [1] = Sql::textFilter('marker_uniquename', $form_state, 'marker_uniquename');
  $where [2] = Sql::selectFilter('stock_uniquename', $form_state, 'stock_uniquename');
  $where [3] = Sql::selectFilter('organism', $form_state, 'organism');
  $group_by = "marker_uniquename,allele:chado_search_ssr_genotype_search:</br>";
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_ssr_genotype_search_table_definition')
    ->groupby($group_by)
    ->customDownload(array('disable_default' => TRUE, 'chado_search_ssr_genotype_search_download_wide_form' => 'Wide Form', 'chado_search_ssr_genotype_search_download_long_form' => 'Long Form', 'chado_search_ssr_genotype_search_download_custom_table' => 'Table'))
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_ssr_genotype_search_base_query() {
  $query = "SELECT 
                 'P_' || marker_uniquename AS marker_allele, 
                 'View ' || count (stock_uniquename) || ' germplasm(s)' AS germplasm, 
                 * 
               FROM {chado_search_ssr_genotype_search} CSDS";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_ssr_genotype_search_table_definition () {
  $headers = array(
      'marker_allele:s:chado_search_ssr_genotype_search_link_polymorphism:feature_id' => 'Marker Allele',
      'marker_uniquename:s:chado_search_ssr_genotype_search_link_feature:feature_id' => 'Marker Name',
      'allele:s:chado_search_ssr_genotype_search_link_marker_allele:marker_uniquename,allele,marker_organism_id' => 'Allele',
      'germplasm:s:chado_search_ssr_genotype_search_link_marker_allele_filtered:marker_uniquename,allele,marker_organism_id' => 'Germplasm',
      'project_name:s:chado_search_ssr_genotype_search_link_project:project_id' => 'Dataset',
      'citation:s:chado_search_ssr_genotype_search_link_pub:pub_id' => 'Publication'
  );
  return $headers;
}

// Define the download table
function chado_search_ssr_genotype_search_download_definition () {
  $headers = array(
      'feature_id' => 'Feature_id',
      'marker_allele' => 'Marker Allele',
      'marker_uniquename' => 'Marker Name',
      'allele' => 'Allele',
      'stock_uniquename' => 'Germplasm',
      'project_name' => 'Dataset',
      'genotype_id' => 'Genotype_id',
      'genotype' => 'Genotype',
      'stock_id' => 'Stock_id'
  );
  return $headers;
}

/**************************************************************
 * Custom Download SQL modification
 */
function chado_search_ssr_genotype_search_download_custom_table ($handle, $result) {
  $header = "\"#\",\"Marker Allele\",\"Marker Name\",\"Allele\",\"Germplasm\",\"Dataset\"\n";
  fwrite($handle, $header);
  $counter = 1;
  while ($row = $result->fetchObject()) {
    fwrite($handle, "\"$counter\",\"$row->marker_allele\",\"$row->marker_uniquename\",\"$row->allele\",\"$row->stock_uniquename\",\"$row->project_name\"\n");
    $counter ++;
  }
}

function chado_search_ssr_genotype_search_download_long_form ($handle, $result, $sql, $total_items, $progress_var) {
  $sql = preg_replace('/(string_agg|count|first) ?\((.+?)\)/', '$2', $sql);
  $sql = str_replace(array(", '; '", 'distinct ', ' GROUP BY marker_uniquename,allele'), array('', '', ''), $sql);
  $sql = "
      SELECT 
        project_name, 
        first(stock_id) AS stock_id,
        stock_uniquename,
        first(feature_id) AS feature_id, 
        marker_uniquename, 
        CASE
        WHEN count (genotype) > 1
        THEN string_agg(genotype, '|') 
        ELSE (SELECT description FROM {genotype} WHERE genotype_id = first(T2.genotype_id))
        END AS genotype
      FROM 
        (SELECT distinct project_name, stock_id, stock_uniquename, feature_id, marker_uniquename, replace(genotype, marker_uniquename || '_', '') AS genotype, genotype_id FROM (" . $sql . ") T
               ORDER BY project_name, stock_uniquename, marker_uniquename, genotype) T2 GROUP BY project_name, stock_uniquename, marker_uniquename";
  $result = chado_query($sql);
  $total_items = $result->rowCount();
  $header = "\"Dataset\",\"Germplasm\",\"Marker Name\",\"Allele\"\n";
  fwrite($handle, $header);
  $progress = 0;
  $counter = 1;
  global $base_url;
  while ($row = $result->fetchObject()) {
    $current = round ($counter / $total_items * 100);
    if ($current != $progress) {
      $progress = $current;
      variable_set($progress_var, $progress);
    }
    $stock_nid = chado_get_nid_from_id('stock', $row->stock_id);
    $feature_nid = chado_get_nid_from_id('feature', $row->feature_id);
    fwrite($handle, "\"$row->project_name\",\"=HYPERLINK(\"\"$base_url/node/$stock_nid\"\", \"\"$row->stock_uniquename\"\")\",\"=HYPERLINK(\"\"$base_url/node/$feature_nid\"\", \"\"$row->marker_uniquename\"\")\",\"$row->genotype\"\n");
    $counter ++;
  }
}

function chado_search_ssr_genotype_search_download_wide_form ($handle, $result, $sql, $total_items, $progress_var) {
  $sql = preg_replace('/(string_agg|count|first) ?\((.+?)\)/', '$2', $sql);
  $sql = str_replace(array(", '; '", 'distinct ', ' GROUP BY marker_uniquename,allele'), array('', '', ''), $sql);
  $sql = "
      SELECT 
        project_name, 
        first(stock_id) AS stock_id,
        stock_uniquename,
        first(feature_id) AS feature_id, 
        marker_uniquename, 
        CASE
        WHEN count (genotype) > 1
        THEN string_agg(genotype, '|') 
        ELSE (SELECT description FROM {genotype} WHERE genotype_id = first(T2.genotype_id))
        END AS genotype
      FROM 
        (SELECT distinct project_name, stock_id, stock_uniquename, feature_id, marker_uniquename, replace(genotype, marker_uniquename || '_', '') AS genotype, genotype_id FROM (" . $sql . ") T
               ORDER BY project_name, stock_uniquename, marker_uniquename, genotype) T2 GROUP BY project_name, stock_uniquename, marker_uniquename";
  $result = chado_query($sql);
  $header = "\"Dataset\",\"Germplasm\"";
  fwrite($handle, $header);
  $counter = 1;
  $headings = array();
  $data = array();
  while ($row = $result->fetchObject()) {
    $headings[$row->feature_id] = $row->marker_uniquename;
    if (!key_exists($row->project_name . '---' . $row->stock_uniquename . '---' . $row->stock_id, $data)) {
      $values = array();
    } else {
      $values = $data[$row->project_name . '---' . $row->stock_uniquename . '---' . $row->stock_id];
    }
    $values [$row->marker_uniquename] = $row->genotype;
    $data[$row->project_name . '---' . $row->stock_uniquename . '---' . $row->stock_id] = $values;      
    $counter ++;
  }
  global $base_url;
  // Print headings
  foreach ($headings AS $feature_id => $val) {
    $feature_nid = chado_get_nid_from_id('feature', $feature_id);
    fwrite($handle, ",\"=HYPERLINK(\"\"$base_url/node/$feature_nid\"\", \"\"".$val . "\"\")\"");
  }
  fwrite($handle, "\n");
  // Print data
  $total_items = $counter;
  $progress = 0;
  $counter = 0;
  foreach ($data AS $key => $value) {
    $current = round ($counter / $total_items * 100);
    if ($current != $progress) {
      $progress = $current;
      variable_set($progress_var, $progress);
    }
    $arr = explode("---", $key);
    $project = $arr[0];
    $stock = $arr[1];
    $stock_id = $arr[2];
    $stock_nid = chado_get_nid_from_id('stock', $stock_id);
    fwrite($handle, "\"" . $project . "\",\"=HYPERLINK(\"\"$base_url/node/$stock_nid\"\", \"\"" . $stock . "\"\")\"");
    foreach ($headings AS $h) {
      $datum = key_exists($h, $value) ? $value[$h] : '';
      fwrite($handle, ",\"" . $datum . "\"");
    }
    fwrite($handle, "\n");
    $counter ++;
  }
}

// Define call back to link the featuremap to its  node for result table
function chado_search_ssr_genotype_search_link_feature ($feature_id) {
  $nid = chado_get_nid_from_id('feature', $feature_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the featuremap to its  node for result table
function chado_search_ssr_genotype_search_link_project ($project_id) {
  $nid = chado_get_nid_from_id('project', $project_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the featuremap to its  node for result table
function chado_search_ssr_genotype_search_link_pub ($pub_id) {
  $nid = chado_get_nid_from_id('pub', $pub_id);
  return chado_search_link_node ($nid);
}

// Define call back to link the marker_allele to the Allele page
function chado_search_ssr_genotype_search_link_marker_allele ($paras) {
  $marker_name = $paras[0];
  $allele = $paras[1];
  $oid = $paras[2];
  if ($marker_name && $allele) {
    return "/allele/$marker_name/$allele/$oid";
  } else {
    return NULL;
  }
}

// Define call back to link the marker_allele to the Allele page
function chado_search_ssr_genotype_search_link_marker_allele_filtered ($paras) {
  $marker_name = $paras[0];
  $allele = $paras[1];
  $oid = $paras[2];
  $project = isset($_POST['project_name']) ? urlencode($_POST['project_name']) : 0;
  $stock = isset($_POST['stock_uniquename']) ? urlencode($_POST['stock_uniquename']) : 0;
  $organism = isset($_POST['organism']) ? urlencode($_POST['organism'][0]) : 0;
  if ($marker_name && $allele) {
    return "/allele/$marker_name/$allele/$oid/$project/$stock/$organism";
  } else {
    return NULL;
  }
}

// Define call back to link the marker_allele to the Allele page
function chado_search_ssr_genotype_search_link_polymorphism ($feature_id) {
  if ($feature_id) {
    return "/polymorphism/$feature_id";
  } else {
    return NULL;
  }
}
