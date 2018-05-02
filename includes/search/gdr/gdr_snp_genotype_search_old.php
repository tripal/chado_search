<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_snp_genotype_search_old_form ($form) {
  $form->addTabs(
      Set::tab()
      ->id('snp_genotype_tabs')
      ->items(array('/search/ssr_genotype' => 'SSR Genotype', '/search/snp_genotype' => 'SNP Genotype'))
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('project_name')
      ->title('Dataset')
      ->column('project_name')
      ->table('chado_search_snp_genotype_search')
      ->cache(TRUE)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_snp_genotype_search')
      ->multiple(TRUE)
      ->cache(TRUE)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('stock_uniquename')
      ->title('Germplasm Name')
      ->column('stock_uniquename')
      ->table('chado_search_snp_genotype_search')
      ->cache(TRUE)
  );
  $form->addFile(
      Set::file()
      ->id('stock_name_file')
      ->title('')
      ->labelWidth(1)
      ->newLine()
  );
  $form->addTextFilter(
      Set::textFilter()
      ->id('feature_uniquename')
      ->title('SNP')
      ->newLine()
  );
  // Restricted by Location
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('genome')
      ->title('Genome')
      ->column('genome')
      ->table('chado_search_snp_genotype_location')
      ->disable(array('Malus x domestica Whole Genome v1.0 Assembly & Annotation'))
      ->cache(TRUE)
      ->labelWidth(140)
      ->newLine()
      );
   $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('location')
      ->title('Chr/Scaffold')
      ->dependOnId('genome')
      ->callback('chado_search_snp_genotype_search_old_ajax_location')
      ->labelWidth(140)
      ); 
  $form->addBetweenFilter(
      Set::betweenFilter()
      ->id('fmin')
      ->title("between")
      ->id2('fmax')
      ->title2("and")
      ->labelWidth2(50)
      ->size(10)
      );
  $form->addMarkup(
      Set::markup()
      ->id('location_unit')
      ->text("<strong>bp</strong>")
      ->newLine()
  );
  $form->addLabeledFilter(
      Set::LabeledFilter()
      ->id('gene_model')
      ->title('Gene Model')
  );
  $form->addLabeledFilter(
      Set::LabeledFilter()
      ->id('range')
      ->title('+/-')
      ->labelWidth(30)
  );
  $form->addMarkup(
      Set::markup()
      ->id('range_unit')
      ->text("<strong>bp</strong>")      
      ->newLine()
  );

  $form->addSubmit();
  $form->addReset();
  $desc = "Search SNP Genotype is a page where users can search for the SNP genotyope 
      dataset based on the germplasm and SNP markers used in the dataset. Click the next tab 
      to search for SSR Genotype. To search for SNP genotype data only for cultivars and 
      breeding selections please visit the <a href=\"/legacy/bt_search_genotype/by_variety\">
      'Search Genotyping Data'</a> page in the <a href=\"/legacy/breeders_toolbox\">Breeders Toolbox</a>. ".
     " | <b>" ./* l('Short video tutorial', 'https://www.youtube.com/watch?v=ARZGxKz5mRo', array('attributes' => array('target' => '_blank'))) . ' | ' . */l('Text tutorial', 'tutorial/search_snp_genotype') . ' | ' .
       l('Email us with problems and suggestions', 'contact') . '</b>';
  $form->addFieldset(
      Set::fieldset()
      ->id('snp_genotype_search_old')
      ->startWidget('project_name')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_snp_genotype_search_old_form_validate (&$form, &$form_state) {

    $gene_model = $form_state['values']['gene_model'];
    if ($gene_model) {
      $sql = "SELECT feature_id FROM {feature} WHERE lower(name) = :name OR lower(uniquename) = :uniquename";
      $feature_id = chado_query($sql, array(":name" => strtolower($gene_model), ":uniquename" => strtolower($gene_model)))->fetchField();
      if (!$feature_id) {
        form_set_error('gene_model', t('Gene model not found.'));
      }
      $sql = "SELECT srcfeature_id, fmin, fmax FROM {featureloc} WHERE feature_id = :feature_id";
      $alignment = chado_query($sql, array(":feature_id" => $feature_id))->fetchObject();
      if (!$alignment) {
        form_set_error('gene_model', t('Gene model\'s position not available.'));
      }
      $form_state['values']['srcfeature_id'] = $alignment->srcfeature_id;
      if ($form_state['values']['range']) {
        $form_state['values']['srcfmin'] = $alignment->fmin - $form_state['values']['range'];
        $form_state['values']['srcfmax'] = $alignment->fmax + $form_state['values']['range'];
      }
      else {
        $form_state['values']['srcfmin'] = $alignment->fmin;
        $form_state['values']['srcfmax'] = $alignment->fmax;
      }
    }
  
}

// Submit the form
function chado_search_snp_genotype_search_old_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_snp_genotype_search_old_base_query();
  
  // Add conditions
  $where [] = Sql::selectFilter('project_name', $form_state, 'project_name');
  $where [] = Sql::selectFilter('stock_uniquename', $form_state, 'stock_uniquename');
  $where [] = Sql::file('stock_name_file', 'stock_uniquename', TRUE);
  $where [] = Sql::selectFilter('organism', $form_state, 'organism');
  $where [] = Sql::textFilter('feature_uniquename', $form_state, 'feature_uniquename');
  $sub [] = Sql::selectFilter('genome', $form_state, 'genome');
  $sub [] = Sql::selectFilter('location', $form_state, 'landmark');
  $sub [] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
  if (isset($form_state['values']['srcfeature_id'])) {
    $sub [] = "landmark_feature_id = " . $form_state['values']['srcfeature_id'];
    $sub [] = "fmin >= " . $form_state['values']['srcfmin'] . " AND fmax <= " . $form_state['values']['srcfmax'];
  }
  $con = " WHERE ";
  for ($i = 0; $i < count($sub); $i ++) {
    if ($sub[$i] != "") {
      if ($i > 0 && $con != " WHERE ") {
        $con .= " AND ";
      }
      $con .= $sub[$i];
    }
  }
  if($con != " WHERE ") {
    $where [] = "GL.feature_id IN (SELECT feature_id FROM {chado_search_snp_genotype_location} $con)";
  }
  Set::result()
    ->sql($sql)
    ->where($where)
    ->tableDefinitionCallback('chado_search_snp_genotype_search_old_table_definition')
    ->customDownload(array('chado_search_snp_genotype_search_old_download_wide_form' => 'Wide Form'))
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_snp_genotype_search_old_base_query() {
  $query = "SELECT GS.*, landmark_feature_id, landmark, genome, fmin, fmax, location FROM {chado_search_snp_genotype_search} GS INNER JOIN {chado_search_snp_genotype_location} GL ON GS.feature_id = GL.feature_id";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_snp_genotype_search_old_table_definition () {
  $headers = array(
    'feature_name:s:chado_search_link_feature:feature_id' => 'Marker',
    'genotype:s' => 'Allele',
    'location:s:chado_search_link_jbrowse:landmark_feature_id,location' => 'Position',
    'stock_uniquename:s:chado_search_link_stock:stock_id' => 'Germplasm',
  );
  return $headers;
}

// User defined: Populating the landmark for selected organism
function chado_search_snp_genotype_search_old_ajax_location ($val) {
  $sql = "SELECT distinct landmark FROM {chado_search_snp_genotype_location} WHERE genome = :genome ORDER BY landmark";
  return chado_search_bind_dynamic_select(array(':genome' => $val), 'landmark', $sql);
}

function chado_search_snp_genotype_search_old_download_wide_form ($handle, $result, $sql, $total_items, $progress_var) {
  set_time_limit(6000);
  $sql = "
      SELECT
        project_name,
        first(stock_id) AS stock_id,
        stock_uniquename,
        first(feature_id) AS feature_id,
        feature_uniquename,
        CASE
        WHEN count (genotype) > 1
        THEN string_agg(genotype, '|')
        ELSE first(genotype)
        END AS genotype
      FROM
        (SELECT distinct project_name, stock_id, stock_uniquename, feature_id, feature_uniquename, genotype FROM (" . $sql . ") T
               ORDER BY project_name, stock_uniquename, feature_uniquename, genotype) T2 GROUP BY project_name, stock_uniquename, feature_uniquename";
  $result = chado_query($sql);
  $header = "\"Dataset\",\"Germplasm\"";
  fwrite($handle, $header);
  $counter = 1;
  $headings = array();
  $data = array();
  while ($row = $result->fetchObject()) {
    $headings[$row->feature_id] = $row->feature_uniquename;
    if (!key_exists($row->project_name . '---' . $row->stock_uniquename . '---' . $row->stock_id, $data)) {
      $values = array();
    } else {
      $values = $data[$row->project_name . '---' . $row->stock_uniquename . '---' . $row->stock_id];
    }
    $values [$row->feature_uniquename] = $row->genotype;
    $data[$row->project_name . '---' . $row->stock_uniquename . '---' . $row->stock_id] = $values;
    $counter ++;
  }
  global $base_url;
  // Print headings
  foreach ($headings AS $feature_id => $val) {
    $feature_nid = chado_search_link_entity('feature', $feature_id);
    fwrite($handle, ",\"=HYPERLINK(\"\"$base_url$feature_nid\"\", \"\"".$val . "\"\")\"");
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
    $stock_nid = chado_search_link_entity('stock', $stock_id);
    fwrite($handle, "\"" . $project . "\",\"=HYPERLINK(\"\"$base_url$stock_nid\"\", \"\"" . $stock . "\"\")\"");
    foreach ($headings AS $h) {
      $datum = key_exists($h, $value) ? $value[$h] : '';
      fwrite($handle, ",\"" . $datum . "\"");
    }
    fwrite($handle, "\n");
    $counter ++;
  }
}
