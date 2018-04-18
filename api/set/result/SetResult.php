<?php

namespace ChadoSearch\set\result;

use ChadoSearch\ChadoSearch;

class SetResult {
  
  private $sql;
  private $where;
  private $table_definition_callback;
  private $groupby = NULL;
  private $fasta_download = FALSE;
  private $append = NULL;
  private $disableCols = NULL;
  private $changeHeaders = NULL;
  private $rewriteCols = NULL;
  private $autoscroll = TRUE;
  private $customDownload = array();
  private $customFasta = NULL;
  private $showDownload = TRUE;
  private $showPager = TRUE;
  private $hideNullColumns = FALSE;
  
  /**
   * Setters
   * @return $this
   */
  public function sql ($sql) {
    $this->sql = $sql;
    return $this;
  }
  
  public function where ($where) {
    $this->where = $where;
    return $this;
  }
  
  public function tableDefinitionCallback ($table_definition_callback) {
    $this->table_definition_callback = $table_definition_callback;
    return $this;
  }
  
  public function groupby ($groupby) {
    $this->groupby = $groupby;
    return $this;
  }
  
  public function fastaDownload ($fasta_download) {
    $this->fasta_download = $fasta_download;
    return $this;
  }
  
  public function append ($append) {
    $this->append = $append;
    return $this;
  }
  
  public function disableCols ($disableCols) {
    $this->disableCols = $disableCols;
    return $this;
  }
  
  public function changeHeaders ($changeHeaders) {
    $this->changeHeaders = $changeHeaders;
    return $this;
  }
  
  public function rewriteCols ($rewriteCols) {
    $this->rewriteCols = $rewriteCols;
    return $this;
  }
  
  public function autoscroll ($autoscroll) {
    $this->autoscroll = $autoscroll;
    return $this;
  }
  
  public function customDownload ($customDownload) {
    $this->customDownload = $customDownload;
    return $this;
  }
  
  public function customFasta ($customFasta) {
    $this->customFasta = $customFasta;
    return $this;
  }
  
  public function showDownload ($showDownload) {
    $this->showDownload = $showDownload;
    return $this;
  }
  
  public function showPager ($showPager) {
    $this->showPager = $showPager;
    return $this;
  }
  
  public function hideNullColumns () {
    $this->hideNullColumns = TRUE;
    return $this;
  }
  
  public function execute($form, &$form_state) {
    $search_id = $form['#search_id'];
    $url = $form['#search_url'];
    $num_per_page = $form['#number_per_page'];
    $search = ChadoSearch::init($search_id, $url, $num_per_page);
    $search->createResult($form_state, $this);
  }
  
  /**
   * Getters
   */
  public function getSql () {
    return $this->sql;
  }
  
  public function getWhere () {
    return $this->where;
  }
  
  public function getTableDefinitionCallback () {
    return $this->table_definition_callback;
  }
  
  public function getGroupby () {
    return $this->groupby;
  }
  
  public function getFastaDownload () {
    return $this->fasta_download;
  }
  
  public function getAppend () {
    return $this->append;
  }
  
  public function getDisableCols () {
    return $this->disableCols;
  }
  
  public function getChangeHeaders () {
    return $this->changeHeaders;
  }
  
  public function getRewriteCols () {
    return $this->rewriteCols;
  }
  
  public function getAutoscroll () {
    return $this->autoscroll;
  }
  
  public function getCustomDownload () {
    return $this->customDownload;
  }
  
  public function getCustomFasta () {
    return $this->customFasta;
  }
  
  public function getShowDownload () {
    return $this->showDownload;
  }
  
  public function getShowPager () {
    return $this->showPager;
  }
  
  public function getHideNullColumns () {
    return $this->hideNullColumns;
  }
}