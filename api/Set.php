<?php

namespace ChadoSearch;

use ChadoSearch\set\result\SetResult;

use ChadoSearch\set\form\SetBetweenFilter;
use ChadoSearch\set\form\SetCustomOutput;
use ChadoSearch\set\form\SetDynamicMarkup;
use ChadoSearch\set\form\SetDynamicSelectFilter;
use ChadoSearch\set\form\SetDynamicTextFields;
use ChadoSearch\set\form\SetFieldset;
use ChadoSearch\set\form\SetFile;
use ChadoSearch\set\form\SetHidden;
use ChadoSearch\set\form\SetLabeledFilter;
use ChadoSearch\set\form\SetMarkup;
use ChadoSearch\set\form\SetReset;
use ChadoSearch\set\form\SetSelect;
use ChadoSearch\set\form\SetSelectFilter;
use ChadoSearch\set\form\SetSelectOptionFilter;
use ChadoSearch\set\form\SetSelectShortCut;
use ChadoSearch\set\form\SetSubmit;
use ChadoSearch\set\form\SetTab;
use ChadoSearch\set\form\SetTextArea;
use ChadoSearch\set\form\SetTextAreaFilter;
use ChadoSearch\set\form\SetTextField;
use ChadoSearch\set\form\SetTextFilter;
use ChadoSearch\set\form\SetThrobber;
use ChadoSearch\form\combo\CustomOutput;

/**
 * A control class to set configuration parameters
 * @author ccheng
 *
 */
class Set {
  
/**
 * Check conf type
 * @param $conf
 * @param $class 
 */
  static public function check ($conf, $class) {
    $cls = chado_search_get_class($conf);
    if ($cls == $class) {
      return TRUE;
    }
    else {
      form_set_error('invalid_set', "Invalid $class. Fail to initialize '" . preg_replace('/^Set/', '', $class) . "'.");
      return FALSE;
    }
  }
  
  /**
   * Set configuration for search result
   */
  static public function result () {
    return new SetResult();
  }
  
  /**
   * Set configuration for form elements
   */
  static public function betweenFilter () {
    return new SetBetweenFilter();
  }
  
  static public function customOutput () {
    return new SetCustomOutput();
  }
  
  static public function dynamicMarkup () {
    return new SetDynamicMarkup();
  }
  
  static public function dynamicSelectFilter () {
    return new SetDynamicSelectFilter();
  }
  
  static public function dynamicTextFields () {
    return new SetDynamicTextFields();
  }
  
  static public function fieldset () {
    return new SetFieldset();
  }
  
  static public function file () {
    return new SetFile();
  }
  
  static public function hidden () {
    return new SetHidden();
  }
  
  static public function labeledFilter () {
    return new SetLabeledFilter();
  }
  
  static public function markup () {
    return new SetMarkup();
  }
  
  static public function reset () {
    return new SetReset();
  }
  
  static public function select () {
    return new SetSelect();
  }
  
  static public function selectFilter () {
    return new SetSelectFilter();
  }
  
  static public function selectOptionFilter () {
    return new SetSelectOptionFilter();
  }
  
  static public function selectShortCut () {
    return new SetSelectShortCut();
  }
  
  static public function submit () {
    return new SetSubmit();
  }
  
  static public function tab () {
    return new SetTab();
  }
  
  static public function textArea () {
    return new SetTextArea();
  }
  
  static public function textAreaFilter () {
    return new SetTextAreaFilter();
  }
  
  static public function textField () {
    return new SetTextField();
  }
  
  static public function textFilter () {
    return new SetTextFilter();
  }
  
  static public function throbber () {
    return new SetThrobber();
  }

}