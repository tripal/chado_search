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
      ->alsoDependOn(array('organism'))
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
      ->id('snp_genotype_search')
      ->startWidget('project_name')
      ->endWidget('reset')
      ->description($desc)
  );
  return $form;
}

// Validate the form
function chado_search_snp_genotype_search_form_validate (&$form, &$form_state) {
  
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
function chado_search_snp_genotype_search_form_submit ($form, &$form_state) {
  // Get base sql
  $sql = "SELECT * FROM {chado_search_snp_genotype_cache}";
  $disableCols = "";
  
  // Get selected stocks
  $selStocks = $form_state['values']['stock_uniquename']; //stocks from selection

  // Get selected organisms
  $selOrgs = $form_state['values']['organism'];
  
  // Convert selected ogranisms into stock selection ONLY IF no stock is selected
  $org_stocks = array();
  if (!key_exists('0', $selOrgs) && count($selOrgs) != 0 && (key_exists('0', $selStocks) || count($selStocks) == 0)) {
    $organisms = variable_get('chado_search_snp_genotype_search_organisms');
    $index = 0;
    foreach($selOrgs AS $o) {
      foreach($organisms[$o] AS $stk) {
        $org_stocks["o$index"] = $stk;
        $index ++;
      }
    }
    unset($selStocks['0']); // make sure organism will be filtered if no stock is selected
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
  
  // Finally, filter on stocks IF 'Any' is not selected or there is at least one stock to filter
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
  
  // Filter the genome position
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
  asort($stocks);
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
    $orgs = $_POST['organism'] ? $_POST['organism'] : array();
    foreach($orgs AS $idx => $o) {
      if ($o == '0') {
        unset ($orgs[$idx]);
      }
    }
    if (count($orgs) > 0) {
      $sql = "SELECT distinct stock_uniquename FROM {chado_search_snp_genotype_cache_project} WHERE project_name = :project_name AND organism IN (:organism) ORDER BY stock_uniquename";
      return chado_search_bind_dynamic_select(array(':project_name'=> $val, ':organism' => $orgs), 'stock_uniquename', $sql);
    }
    else {
      $sql = "SELECT distinct stock_uniquename FROM {chado_search_snp_genotype_cache_project} WHERE project_name = :project_name ORDER BY stock_uniquename";
      return chado_search_bind_dynamic_select(array(':project_name'=> $val), 'stock_uniquename', $sql);
    }
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
  print "Checking Required MViews...\n";
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
                srcfeature_id,
                location,
                project_id,
                project_name,
                allele,
                $col)
              VALUES (
                $r->feature_id,
                '$r->feature_name',
                '$r->feature_uniquename',
                (SELECT first(landmark_feature_id) FROM (SELECT * FROM {chado_Search_snp_genotype_location} WHERE feature_id = $r->feature_id ORDER BY char_length(landmark), landmark) T),
                (SELECT first(location) FROM (SELECT * FROM {chado_Search_snp_genotype_location} WHERE feature_id = $r->feature_id ORDER BY char_length(landmark), landmark) T),
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
    // if chado_search_snp_genotype_cache not exists, create it
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
             srcfeature_id integer,
             location varchar(510),
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
    
    // if chado_search_snp_genotype_cache_project not exists, create it
    if (!$exist_cache_project) {
      print "Create MView chado_search_snp_genotype_cache_project...\n";
      $sql = 
      "SELECT DISTINCT project_name, organism, stock_uniquename 
       INTO {chado_search_snp_genotype_cache_project} 
       FROM {chado_search_snp_genotype_search}";
      chado_query($sql);
    }
    
    // if chado_search_snp_genotype_cache already exists, check if there are new stocks. 
    // If there are, add them as new columns to chado_search_snp_genotype_cache AND
    // add them as new rows to chado_search_snp_genotype_cache_project, 
    // vice versa for stocks that no longer exist 
    if ($exist_cache) {
      print "Checking new stocks...\n";
      $before_stocks = variable_get('chado_search_snp_genotype_search_stocks');
      $after_stocks = chado_search_snp_genotype_cache_get_stocks();
      $only_before = array_diff_key($before_stocks, $after_stocks);
      $only_after = array_diff_key($after_stocks, $before_stocks);
      // Remove stock columns that no longer exist. Also remove those stocks from the 'project_cache'
      if (count($only_before) != 0) {
        print "Remove stock data that no longer exist...\n";
        foreach($only_before AS $id => $name) {
          $sql = "ALTER TABLE {chado_search_snp_genotype_cache} DROP COLUMN s$id IF EXISTS";
          chado_query($sql);
          $sql = "DELETE FROM {chado_search_snp_genotype_cache_project} WHERE stock_uniquename = '$name'";
          chado_query($sql);
        }
      }
      // Add new stock columns. Also add new rows to the 'project_cache'
      if (count($only_after) != 0) {
        print "Add new stock data...\n";
        foreach($only_after AS $id => $name) {
          $sql = "ALTER TABLE {chado_search_snp_genotype_cache} ADD COLUMN s$id";
          chado_query($sql);
          $sql = "INSERT INTO {chado_search_snp_genotype_cache_project} (project_name, organism, stock_uniquename) (SELECT DISTINCT project_name, organism, stock_uniquename FROM {chado_search_snp_genotype_search} WHERE stock_id = $id )";
          chado_query($sql);
        }
      }
    }
  }
}
