<?php

use ChadoSearch\Set;
use ChadoSearch\Sql;

/*************************************************************
 * Search form, form validation, and submit function
 */
// Search form
function chado_search_snp_genotype_search_form ($form) {
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
      ->table('chado_search_snp_genotype_search')
      ->cache(TRUE)
      ->labelWidth(140)
      ->newLine()
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('organism')
      ->title('Species')
      ->dependOnId('project_name')
      ->callback('chado_search_snp_genotype_search_ajax_dynamic_organism')
      ->multiple(TRUE)
      ->labelWidth(140)
      ->newLine()
  );
  $form->addDynamicSelectFilter(
      Set::dynamicSelectFilter()
      ->id('stock_uniquename')
      ->title('Germplasm Name')
      ->dependOnId('project_name')
      ->callback('chado_search_snp_genotype_search_ajax_dynamic_stock')
      ->multiple(TRUE)
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
  $sql = "SELECT * FROM {chado_search_snp_genotype_cache}";
  $disableCols = "";
  
  // Get selected stocks
  $selStocks = $form_state['values']['stock_uniquename']; //stocks from selection
  
  // Convert selected ogranisms into stock selection
  $selOrgs = $form_state['values']['organism'];
  $org_stocks = array();
  if (!key_exists('0', $selOrgs) && count($selOrgs) != 0) {
    $organisms = variable_get('chado_search_snp_genotype_search_organisms');
    $index = 0;
    foreach($selOrgs AS $o) {
      foreach($organisms[$o] AS $stk) {
        $selStocks["o$index"] = $stk;
        $index ++;
      }
    }
  }
  $selStocks += $org_stocks;

  // Add uploaded file into stock selection
  $file = $_FILES['files']['tmp_name']['stock_name_file'];
  $file_stocks = array();
  if ($file) {
    $handle = fopen($file, 'r');
    $index = 0;
    while ($line = fgets($handle)) {
      $name = trim($line);
      $file_stocks ["f$index"] = $name;
      $index ++;
    }
  }
  $selStocks += $file_stocks;
  
  $notNullStocks = array();
  if (!key_exists('0', $selStocks) && count($selStocks) != 0) {
    $allStocks = variable_get('chado_search_snp_genotype_search_stocks');
    $select = "feature_id, feature_name, allele";
    foreach ($selStocks AS $s) {
      $id = array_search($s, $allStocks);
      if ($id !== FALSE) {
        $notNullStocks [] = "s$id";
        $select .= ", s$id";
      }
    }
    if ($select != "feature_id, feature_name, allele") {
      $sql = "SELECT $select FROM {chado_search_snp_genotype_cache}";
    }
  }
  
  // Add conditions
  $where [] = Sql::selectFilter('project_name', $form_state, 'project_name');
  $where [] = Sql::textFilter('feature_uniquename', $form_state, 'feature_uniquename');
  $where [] = Sql::notNullCols($notNullStocks); // Remove all NULL stock rows
  
/*
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
    ->hideNullColumns()  // Remove all NULL (stock) columns
    ->execute($form, $form_state);
}

/*************************************************************
 * Build the search result table
*/
// Define the result table
function chado_search_snp_genotype_search_table_definition () {
  $stocks = variable_get('chado_search_snp_genotype_search_stocks');
  $headers = array(
    'feature_name:s:chado_search_link_feature:feature_id' => 'Marker',
    'allele:s' => 'Allele',
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

function chado_search_snp_genotype_search_ajax_dynamic_organism ($val) {
  if ($val && chado_table_exists('chado_search_snp_genotype_cache_project')) { 
    $sql = "SELECT distinct organism FROM {chado_search_snp_genotype_cache_project} WHERE project_name = :project_name ORDER BY organism";
    return chado_search_bind_dynamic_select(array(':project_name'=> $val), 'organism', $sql);
  }
}

function chado_search_snp_genotype_search_ajax_dynamic_stock ($val) {
  if ($val && chado_table_exists('chado_search_snp_genotype_cache_project')) {
    $sql = "SELECT distinct stock FROM {chado_search_snp_genotype_cache_project} WHERE project_name = :project_name ORDER BY stock";
    return chado_search_bind_dynamic_select(array(':project_name'=> $val), 'stock', $sql);
  }
}

/**
 * Custom download only polymorphic data
 */
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

/**
 * Check cache MView and populate its data when 'drush csrun' is issued
 */
function chado_search_snp_genotype_search_drush_run() {
  print "Checking MView chado_search_snp_genotype_cache...\n";
  chado_search_snp_genotype_cache_mview();
  print "Populating chado_search_snp_genotype_cache...";
  $exist_search = chado_table_exists('chado_search_snp_genotype_search');
  $exist_cache = chado_table_exists('chado_search_snp_genotype_cache');
  if ($exist_search && $exist_cache) {
    // Populate the cache table
    $sql = "SELECT * FROM {chado_search_snp_genotype_search}";
    $results = chado_query($sql);
    while ($r = $results->fetchObject()) {
      $exists = 
        chado_query(
          "SELECT feature_id, project_id FROM {chado_search_snp_genotype_cache} WHERE feature_id = :feature_id AND project_id = :project_id", 
          array(':feature_id' => $r->feature_id, ':project_id' => $r->project_id))->fetchField();
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
                allele,
                $col)
              VALUES (
                $r->feature_id,
                '$r->feature_name',
                '$r->feature_uniquename',
                $r->project_id,
                '$r->project_name',
                '$r->allele',
                '$r->genotype'
              )";
      }
      // Update if exist
      else {
        $sql =
        "UPDATE {chado_search_snp_genotype_cache}
              SET $col = '$r->genotype'
              WHERE feature_id = $r->feature_id
              AND project_id = $r->project_id
            ";
      }
      chado_query($sql);
    }
  }
}

/**
 * Before creating the cache table, we need to know how many stocks are there so we can use them to create columns
 */
function chado_search_snp_genotype_cache_get_stocks() {
  // Get all distinct stocks in the search mview
  $sql = "SELECT DISTINCT stock_id, stock_uniquename FROM {chado_search_snp_genotype_search}";
  $results = chado_query($sql);
  $stocks = array();
  $organisms = array();
  while ($stock = $results->fetchObject()) {
    $stocks [$stock->stock_id] = $stock->stock_uniquename;
    $organism =
    chado_query(
        "SELECT (SELECT genus || ' ' || species FROM {organism} WHERE organism_id = S.organism_id) AS organism
              FROM {stock} S
              WHERE stock_id = :stock_id",
        array(':stock_id' => $stock->stock_id))->fetchObject();
        if (isset($organisms[$organism->organism])) {
          array_push($organisms[$organism->organism], $stock->stock_uniquename);
        }
        else {
          $organisms[$organism->organism] = array($stock->stock_uniquename);
        }
  }
  // Store all stocks and their organism mapping for the search
  variable_set('chado_search_snp_genotype_search_stocks', $stocks);
  variable_set('chado_search_snp_genotype_search_organisms', $organisms);
  return $stocks;
}

/**
 * Create cache MView 
 */
function chado_search_snp_genotype_cache_mview() {
  // Check if the cache table exists
  $exist_search = chado_table_exists('chado_search_snp_genotype_search');
  $exist_cache = chado_table_exists('chado_search_snp_genotype_cache');
  $exist_cache_project = chado_table_exists('chado_search_snp_genotype_cache_project');
  if ($exist_search) {
    if (!$exist_cache) {
      $stocks = chado_search_snp_genotype_cache_get_stocks();
      if (count($stocks != 0)) {
        print "Create MView chado_search_snp_genotype_cache...\n";
        // Create the cache table
        $sql =
        "CREATE TABLE IF NOT EXISTS {chado_search_snp_genotype_cache} (
             feature_id integer,
             feature_name varchar(510),
             feature_uniquename text,
             project_id integer,
             project_name varchar(255),
             allele text";
        foreach ($stocks AS $id => $s) {
          $sql .= ', s' .  $id . ' varchar(255)';
        }
        $sql .= ", CONSTRAINT feature_project_uniq UNIQUE(feature_id, project_id))";
        chado_query($sql);
      }
    }
    if (!$exist_cache_project) {
      print "Checking MView chado_search_snp_genotype_cache_project...\n";
      $sql = 
      "SELECT DISTINCT project_name, organism, stock_uniquename 
       INTO {chado_search_snp_genotype_cache_project} 
       FROM {chado_search_snp_genotype_search}";
      chado_query($sql);
    }
  }
}
