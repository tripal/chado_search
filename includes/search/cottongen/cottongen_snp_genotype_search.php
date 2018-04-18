<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_snp_genotype_search_form ($form) {
  chado_search_snp_genotype_cache_mview();
  $form->addTabs(
      Set::tab()
      ->id('snp_genotype_tabs')
      ->items(array('/find/ssr_genotype' => 'SSR Genotype', '/find/snp_genotype' => 'SNP Genotype'))
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('project_name')
      ->title('Dataset')
      ->column('project_name')
      ->table('chado_search_snp_genotype_cache')
      ->cache(TRUE)
      ->labelWidth(140)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('organism')
      ->title('Species')
      ->column('organism')
      ->table('chado_search_snp_genotype_cache')
      ->multiple(TRUE)
      ->cache(TRUE)
      ->labelWidth(140)
      ->newLine()
  );
  $form->addSelectFilter(
      Set::selectFilter()
      ->id('stock_uniquename')
      ->title('Germplasm Name')
      ->column('stock_uniquename')
      ->table('chado_search_snp_genotype_search')
      ->multiple(TRUE)
      ->cache(TRUE)
      ->labelWidth(140)
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
      ->labelWidth(140)
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
      ->callback('chado_search_snp_genotype_search_ajax_location')
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
      ->id('snp_genotype_search')
      ->startWidget('project_name')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Submit the form
function chado_search_snp_genotype_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = chado_search_snp_genotype_search_base_query();
  
  // Add conditions
  $where [] = Sql::selectFilter('project_name', $form_state, 'project_name');
  $where [] = Sql::textFilter('feature_uniquename', $form_state, 'feature_uniquename');
  $allStocks = variable_get('chado_search_snp_genotype_search_stocks');
  $selStocks = $form_state['values']['stock_uniquename'];
  $notNullStocks = array();
  foreach ($selStocks AS $s) {
    $id = array_search($s, $allStocks);
    if ($id !== FALSE) {
      $notNullStocks [] = "s$id";
    }
  }
  $where [] = Sql::notNullCols($notNullStocks);
  
/*
  $where [] = Sql::selectFilter('organism', $form_state, 'organism');   
  $where [] = Sql::selectFilter('stock_uniquename', $form_state, 'stock_uniquename');
  $where [] = Sql::file('stock_name_file', 'stock_uniquename', TRUE);
  
  $sub [] = Sql::selectFilter('genome', $form_state, 'genome');
  $sub [] = Sql::selectFilter('location', $form_state, 'landmark');
  $sub [] = Sql::betweenFilter('fmin', 'fmax', $form_state, 'fmin', 'fmax');
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
    $where [] = "feature_id IN (SELECT feature_id FROM {chado_search_snp_genotype_location} $con)";
  } */

  Set::result()
    ->sql($sql)
    ->tableDefinitionCallback('chado_search_snp_genotype_search_table_definition')
    ->where($where)
    ->disableCols($disableCols)
    ->customDownload(array('chado_search_snp_genotype_search_download_polymorphic' => 'Table (Polymorphic)'))
    ->hideNullColumns()
    ->execute($form, $form_state);
}

/*************************************************************
 * SQL
*/
// Define query for the base table. Do not include the WHERE clause
function chado_search_snp_genotype_search_base_query() {
  $query = "SELECT * FROM {chado_search_snp_genotype_cache}";
  return $query;
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_snp_genotype_search_table_definition () {
  $stocks = variable_get('chado_search_snp_genotype_search_stocks');
  $headers = array(
    'feature_name:s:chado_search_link_feature:feature_id' => 'Marker',
  );
  foreach ($stocks AS $id => $s) {
    $headers['s' . $id . ':s'] = $s;
  }
  return $headers;
}

// User defined: Populating the landmark for selected organism
function chado_search_snp_genotype_search_ajax_location ($val) {
  $sql = "SELECT distinct landmark FROM {chado_search_snp_genotype_location} WHERE genome = :genome ORDER BY landmark";
  return chado_search_bind_dynamic_select(array(':genome' => $val), 'landmark', $sql);
}

function chado_search_snp_genotype_search_download_polymorphic ($handle, $result, $sql, $total_items, $progress_var, $headers) {
  set_time_limit(6000);  
  fwrite($handle, "\"#\",");
  $col = 0;
  foreach ($headers AS $k => $v) {
    fwrite($handle, "\"". $v . "\"");
    $col ++;
    if ($col < count($headers)) {
      fwrite($handle, ",");
    } else {
      fwrite($handle, "\n");
    }
  }
  $progress = 0;
  $counter = 1;
  $line_no = 1;
  while ($row = $result->fetchObject()) {
    $current = round ($counter / $total_items * 100);
    if ($current != $progress) {
      $progress = $current;
      variable_set($progress_var, $progress);
    }
    $line = "\"$line_no\",";
    $col = 0;
    $polymorphic = FALSE;
    $gtype = NULL;
    foreach ($headers AS $k => $v) {
      $value = $row->$k;
      if (preg_match('/s\d+/', $k)) {
        $gtype = $gtype == NULL ? $value : $gtype;
        if ($value != $gtype) {
          $polymorphic = TRUE;
        }
      }
      $line .= '"' . str_replace('"', '""', $value) . '"';
      $col ++;
      if ($col < count($headers)) {
        $line .= ",";
      } else {
        $line .= "\n";
      }
    }
    if ($polymorphic) {
      fwrite($handle, $line);
      $line_no ++;
    }
    $counter ++;
  }
}

function chado_search_snp_genotype_cache_mview() {
  // Check if the cache table exists
  $exist_search = chado_table_exists('chado_search_snp_genotype_search');
  $exist_cache = chado_table_exists('chado_search_snp_genotype_cache');
  if ($exist_search) {
    if (!$exist_cache) {
      // Get all distinct germplasms in the search mview
      $sql = "SELECT DISTINCT stock_id, stock_uniquename FROM {chado_search_snp_genotype_search}";
      $results = chado_query($sql);
      $stocks = array();
      while ($stock = $results->fetchObject()) {
        $stocks [$stock->stock_id] = $stock->stock_uniquename;
      }
      variable_set('chado_search_snp_genotype_search_stocks', $stocks);
      if (count($stocks != 0)) {
        // Create the cache table
        $sql = 
          "CREATE TABLE IF NOT EXISTS {chado_search_snp_genotype_cache} (
             feature_id integer,
             feature_name varchar(510),
             feature_uniquename text,
             project_id integer,
             project_name varchar(255)";
        foreach ($stocks AS $id => $s) {
          $sql .= ', s' .  $id . ' varchar(255)';
        }
        $sql .= ", CONSTRAINT feature_id_uniq UNIQUE(feature_id))";
        chado_query($sql);
      }
    }
  }
}

function chado_search_snp_genotype_search_drush_run() {
  print "Populating chado_search_snp_genotype_cache...";
  $exist_search = chado_table_exists('chado_search_snp_genotype_search');
  $exist_cache = chado_table_exists('chado_search_snp_genotype_cache');
  if ($exist_search && $exist_cache) {
    // Populate the cache table
    $sql = "SELECT * FROM {chado_search_snp_genotype_search}";
    $results = chado_query($sql);
    while ($r = $results->fetchObject()) {
      $exists = chado_query("SELECT feature_id FROM {chado_search_snp_genotype_cache} WHERE feature_id = :feature_id", array(':feature_id' => $r->feature_id))->fetchField();
      $col = 's' . $r->stock_id;
      // Insert if not exist
      if (!$exists) {
        $sql =
        "INSERT INTO {chado_search_snp_genotype_cache}
               (feature_id,
                feature_name,
                feature_uniquename,
                project_id,
                project_name,
                $col)
              VALUES (
                $r->feature_id,
                '$r->feature_name',
                '$r->feature_uniquename',
                $r->project_id,
                '$r->project_name',
                '$r->genotype'
              )";
      }
      // Update if exist
      else {
        $sql =
        "UPDATE {chado_search_snp_genotype_cache}
              SET $col = '$r->genotype'
              WHERE feature_id = $r->feature_id
            ";
      }
      chado_query($sql);
    }
  }
}
