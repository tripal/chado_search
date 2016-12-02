<?php
namespace ChadoSearch;

use ChadoSearch\SessionVar;

use ChadoSearch\form\ChadoSearchForm;

use ChadoSearch\result\Table;
use ChadoSearch\result\Pager;
use ChadoSearch\result\Download;
use ChadoSearch\result\CustomDownload;
use ChadoSearch\result\Fasta;
use ChadoSearch\result\ResultQuery;

class ChadoSearch {

  public $search_id, $path, $number_per_page;

  // Return a ChadoSearch object
  public static function init ($search_id, $path = NULL, $number_per_page = 20) {
    $obj = new ChadoSearch();
    $obj->search_id = $search_id;
    if ($path != NULL) {
      $obj->path = $path;
    } else {
      $obj->path = "chado_search/$search_id";
    }
    $pager_setting = chado_search_get_setting_by_id($search_id, 'num_per_page');
    $obj->number_per_page = $pager_setting? $pager_setting : $number_per_page;
    return $obj;
  }

  /****************************************************
   * Build the Drupal menu system
  */
  // Build the menu for a search function
  public function buildMenu($title, $file = NULL, $ajax_callbacks = NULL) {
    $url = $this->path;
    $search_id = $this->search_id;
    $num_per_page = $this->number_per_page;
    $num_token = count(explode("/", $url));
    $items[$url] = array(
        'title' => $title,
        'description' => 'Search Page',
        'page callback' => 'chado_search_callback',
        'page arguments' => array($search_id, $url, "$num_per_page", $num_token),
        'access arguments' => array('access content'),
        'type' => MENU_CALLBACK,
    );
    $items["$url/pager"] = array(
        'description' => 'Ajax call back for changing pages of the search result',
        'page callback' => 'chado_search_ajax_pager',
        'page arguments' => array($num_token + 1, $search_id, $url, "$num_per_page"),
        'access arguments' => array('access content'),
        'type' => MENU_CALLBACK,
    );
    $items["$url/download"] = array(
        'description' => 'Ajax call back for downloading the search result',
        'page callback' => 'chado_search_ajax_download',
        'page arguments' => array($search_id, $url, "$num_per_page"),
        'access arguments' => array('access content'),
        'type' => MENU_CALLBACK,
    );
    $items["$url/fasta"] = array(
        'description' => 'Ajax call back for downloading fasta file',
        'page callback' => 'chado_search_ajax_download_fasta_feature',
        'page arguments' => array($search_id, $url, "$num_per_page"),
        'access arguments' => array('access content'),
        'type' => MENU_CALLBACK,
    );
    if (is_array($ajax_callbacks)) {
      foreach($ajax_callbacks AS $k => $v) {
        $items["$url/ajax/$k"] = array(
            'description' => 'User defined Ajax callback',
            'page callback' => $v,
            'page arguments' => array($num_token + 2),
            'access arguments' => array('access content'),
            'type' => MENU_CALLBACK,
        );
      }
    }
    if ($file != NULL) {
      $items[$url]['file'] = $file;
      $items["$url/pager"]['file'] = $file;
      $items["$url/download"]['file'] = $file;
      $items["$url/fasta"]['file'] = $file;
      if (is_array($ajax_callbacks)) {
        foreach($ajax_callbacks AS $k => $v) {
          $items["$url/ajax/$k"]['file'] = $file;
        }
      }
    }
    return $items;
  }

  /****************************************************
   * Prepare a ChadoSearchForm object for creating a Drupal form
  */
  // Prepare a form object for building form items
  public function prepareForm (&$form_state) {
    $sbf = new ChadoSearchForm($form_state, $this->search_id, $this->path);
    return $sbf;
  }

  /****************************************************
   * Generating the results
   * $sql - an SQL without any condition (i.e. the WHERE clause) that defines the base table
   * $where - an array containing SQL conditions to apply to the base table. These SQLs will be concatenated by 'AND' and placed after the 'WHERE' keyword
   * $table_definition_callback - an array containing the header information of the result table. Optionally, settings (separated by :) can be specified in the key.
   *     - key: <column>:<(s)ortable>:<callback>:<argument1>:<argument2>:<argument3>...
   *     - value: displayed column name
   * $form_state: Drupal $form_state variable
   * $groupby - group the result by column(s). format = '<column>:<table>:<separator>'
   * $fasta_download - create a Fasta download link
   * $append - a free SQL string that will be append to the end of the statement
   * $disableCols - hide these columns from the result table. format = '<column1>;<column2>;<column3>;...'
   * $changeHeaders - change the title for these headers. format = '<column1>=<title1>;<column2>=<title2>;<column3>=<title3>;...'
   * $rewriteCols - rewrite the value in specified columns by passing the value to the specified function($value). format = '<column1>=<callback1>;<column2>=<callback2>;<column3>=<callback3>;...'
   * $autoscroll - automatically scroll the page to the top of the result table
   * $customDownload - an array of 'key=value' pairs where the key is the download function to which it will be passed with $handle, $result, and $sql variables for writing output. The 'value' is the 'Display Text' on the page. To disable the default download, the array needs to contain the setting: array('disable_default' => TRUE)
   * $customFasta - the function to call to modified the SQL for Fasta download. This function will be passed with the current SQL (i.e. $sql variable) and should return the modified version of SQL which retrieves all 'feature_id' for the download
   * $showDownload - add download links to the up right conner of the table
   * $showPager - add pager to the bottom right conner of the table
  */ 
  // Main Result
  public function createResult (&$form_state, $conf) {
    if (!is_array($form_state) || !isset($form_state['build_info']['form_id'])) {
      form_set_error('invalid_form_state', 'Fail to generate results. Please check the $form_state  you passed for the createResult($form_state, $conf) function.');
      return;
    }
    if (!is_object($conf) || !method_exists($conf, 'getSql')) {
      form_set_error('invalid_conf', 'Fail to generate results. Please check the $conf  you passed for the createResult($form_state, $conf) function.');
      return;
    }
    
    // Get parameters from $conf
    $sql = $conf->getSql();
    $where = $conf->getWhere();
    $table_definition_callback = $conf->getTableDefinitionCallback();
    $groupby = $conf->getGroupby();
    $fasta_download = $conf->getFastaDownload();
    $append = $conf->getAppend();
    $disableCols = $conf->getDisableCols();
    $changeHeaders = $conf->getChangeHeaders();
    $rewriteCols = $conf->getRewriteCols();
    $autoscroll = $conf->getAutoscroll();
    $customDownload = $conf->getCustomDownload();
    $customFasta = $conf->getCustomFasta();
    $showDownload = $conf->getShowDownload();
    $showPager = $conf->getShowPager();
    
    $search_id = $this->search_id;
    
    // Prepare SQL
    $result_query = new ResultQuery($search_id, $sql);
    $result_query
      ->addWhere($where)
      ->addGroupBy($groupby)
      ->appendSQL($append);
    $sql = $result_query->getSQL();
    $total_items = $result_query->count();
    $total_pages =Pager::totalPages($total_items, $this->number_per_page);

    // Prepare the result
    $div = "";
    // Show all result instead of just creating a search form
    if(key_exists('#show_all_results',$form_state)) {
      $div ="<style type=\"text/css\">body {display: block;}</style>";
      $autoscroll = $form_state['#show_all_results_scroll'];
    }
    
    // Show the first page
    if ($total_items != 0) {

      // Get custom outputs setting if it exists
      if (key_exists('custom_output_options', $form_state['values'])) {
        $custom_output = $form_state['values']['custom_output_options'];
        foreach ($custom_output AS $k => $v) {
          if (!$v) {
            $disableCols .= ";$k";
          }
        }
      }
      
      // Store settings to session variables
      SessionVar::setSessionVar($search_id, 'disabled-columns', $disableCols);
      SessionVar::setSessionVar($search_id, 'changed-headers', $changeHeaders);
      SessionVar::setSessionVar($search_id, 'rewrite-columns', $rewriteCols);
      SessionVar::setSessionVar($search_id, 'custom-fasta-download', $customFasta);
      SessionVar::setSessionVar($search_id, 'autoscroll', $autoscroll);
      
      // Build the result
      $div .= 
      "<div id=\"$search_id-result-summary\" class=\"chado_search-result-summary\">
          <div id=\"$search_id-result-count\" class=\"chado_search-result-count\">
            <strong>$total_items</strong> records were returned
          </div>";

      // Add Download(s)
      if ($showDownload) {
        // Custom Download(s)
        $custom_dl = new CustomDownload($search_id, $customDownload);
        $div .= $custom_dl->getSrc();
        
        // Fasta Download
        if ($fasta_download) {
          $fasta = new Fasta($this->search_id, $this->path);
          $div .= $fasta->getSrc();
        }
        
        // Table Download
        $dl_default = isset($customDownload['disable_default']) ? FALSE : TRUE;
        $download = new Download($this->search_id, $this->path, $dl_default);
        $div .= $download->getSrc();
        
        // Download Label
        $div .=
          "<div id=\"$search_id-download-label\" class=\"chado_search-download-label\">
              Download
           </div>";
      }
      
      $div .= "</div>";
      
      // Add Table
      $lsql = "$sql LIMIT $this->number_per_page;";
      $result = chado_query($lsql);
      if (function_exists($table_definition_callback)) {
        $headers = $table_definition_callback();
      }
      else {
        $hsql = "SELECT * FROM ($sql LIMIT 1) T";
        $fields = array_keys(db_query($hsql)->fetchAssoc());
        foreach($fields AS $field) {
          $headers[$field] = $field;
        }
        SessionVar::setSessionVar($search_id, 'default-headers', $headers);
      }
      $table = new Table($this->search_id, $result, 0, $this->number_per_page, $headers, NULL, $autoscroll);
      $div .= $table->getSrc();

      // Add Pager (and code for switching pages/sorting results)
      $pager = new Pager($this->search_id, $this->path, $total_pages, $showPager);
      $div .= $pager->getSrc();
      
    // If there is no result, show the following message
    } else {
      $div = 
        "<div id=\"$search_id-no-result\" class=\"chado_search-no-result\">
            <strong>0</strong> records were returned.
          </div>";
    }
    
    // Attach the result to form
    $form_state['values']['result'] = $div;
    $form_state['rebuild'] = true;
  }
}
