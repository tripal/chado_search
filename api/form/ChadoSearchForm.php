<?php

namespace ChadoSearch\form;

use ChadoSearch\Set;

use ChadoSearch\result\WaitingBox;

use ChadoSearch\form\control\Hidden;
use ChadoSearch\form\control\TextField;
use ChadoSearch\form\control\TextArea;
use ChadoSearch\form\control\Select;
use ChadoSearch\form\control\Markup;
use ChadoSearch\form\control\File;
use ChadoSearch\form\control\Submit;
use ChadoSearch\form\control\Reset;

use ChadoSearch\form\combo\LabeledFilter;
use ChadoSearch\form\combo\BetweenFilter;
use ChadoSearch\form\combo\TextFilter;
use ChadoSearch\form\combo\TextAreaFilter;
use ChadoSearch\form\combo\SelectFilter;
use ChadoSearch\form\combo\SelectOptionFilter;
use ChadoSearch\form\combo\DynamicMarkup;
use ChadoSearch\form\combo\DynamicSelectFilter;
use ChadoSearch\form\combo\DynamicTextFields;
use ChadoSearch\form\combo\Fieldset;
use ChadoSearch\form\combo\Tab;
use ChadoSearch\form\combo\SelectShortcut;
use ChadoSearch\form\combo\Throbber;
use ChadoSearch\form\combo\CustomOutput;

class ChadoSearchForm {
  
  public $form;
  public $form_state, $search_name, $path;
  public $base_sql;
  
  // Constructor
  public function __construct(&$form_state, $search_name, $path) {
    $this->form_state =& $form_state;
    $this->search_name = $search_name;
    $this->path = $path;
    $box = new WaitingBox($search_name);
    $this->form['wait'] = array (
      '#markup' => $box->getSrc(),
    );
  }
  
  /**********************************************************
   * Attached an SQL for the base table
   */
  public function addBaseSQL ($sql) {
    $this->form_state['base_sql'] = $sql;
  }
  
  /**********************************************************
   * Basic form components
  */
  // Hidden
  public function addHidden ($conf) {
    if (!Set::check($conf, 'SetHidden')) {
      return;
    }
    $e = new Hidden($this->search_name, $conf->getId());
    $e->value = $conf->getValue();
    $e->newline = $conf->getNewLine();
    $e->attach($this->form, $this->form_state);
  }
  
  // Textfield
  public function addTextfield ($conf) {
    if (!Set::check($conf, 'SetTextField')) {
      return;
    }
    $e = new TextField($this->search_name, $conf->getId());
    $e->title = $conf->getTitle();
    $e->required = $conf->getRequired();
    $e->size = $conf->getSize();
    $e->newline = $conf->getNewLine();
    $e->attach($this->form, $this->form_state);
  }

  // Textarea
  public function addTextarea ($conf) {
    if (!Set::check($conf, 'SetTextArea')) {
      return;
    }
    $e = new TextArea($this->search_name, $conf->getId());
    $e->title = $conf->getTitle();
    $e->required = $conf->getRequired();
    $e->cols = $conf->getColumns();
    $e->rows = $conf->getRows();
    $e->newline = $conf->getNewLine();
    $e->attach($this->form, $this->form_state);
  }
  
  // Select
  public function addSelect ($conf) {
    if (!Set::check($conf, 'SetSelect')) {
      return;
    }
    $e = new Select($this->search_name, $conf->getId());
    $e->title = $conf->getTitle();
    $e->options = $conf->getOptions();
    $e->multiple = $conf->getMultiple();
    $e->size = $conf->getSize();
    $e->newline = $conf->getNewLine();
    $e->attach($this->form, $this->form_state);
  }
  
  // Markup
  public function addMarkup ($conf) {
    if (!Set::check($conf, 'SetMarkup')) {
      return;
    }
    $e = new Markup($this->search_name, $conf->getId());
    $e->markup = t($conf->getText());
    $e->newline = $conf->getNewLine();
    $e->attach($this->form, $this->form_state);
  }

  // File
  public function addFile ($conf) {
    if (!Set::check($conf, 'SetFile')) {
      return;
    }
    $e = new File($this->search_name, $conf->getId());
    $e->search_name = $this->search_name;
    $e->label = $conf->getTitle();
    $e->instruction = $conf->getDescription();
    $e->size = $conf->getSize() ? $conf->getSize() : 20;
    $e->label_width = $conf->getLabelWidth();
    $e->newline = $conf->getNewLine();
    $e->attach($this->form, $this->form_state);
  }
  
  // Submit
  public function addSubmit ($conf = NULL) {
    if ($conf && !Set::check($conf, 'SetSubmit')) {
      return;
    }
    $id = is_object($conf) ? $conf->getId() : 'submit';
    $value = is_object($conf)? $conf->getValue() : 'Search';
    $e = new Submit($this->search_name, $id ? $id : 'submit');
    $e->value =  $value ? $value : 'Search';
    $e->newline = is_object($conf)? $conf->getNewLine() : FALSE;
    $e->attach($this->form, $this->form_state);
  }
  
  // Reset
  public function addReset ($conf = NULL) {
    if ($conf && !Set::check($conf, 'SetReset')) {
      return;
    }
    $id = is_object($conf) ? $conf->getId() : 'reset';
    $e = new Reset($this->search_name, $id ? $id  : 'reset');
    $e->path = $this->path;
    $e->newline = is_object($conf)? $conf->getNewLine() : FALSE;
    $e->attach($this->form, $this->form_state);
  }
  
  /**********************************************************
   * Bundled form components (Filters)
  */
  // BetweenFilter (Two sets of LabeledFilter)
  public function addBetweenFilter($conf) {
    if (!Set::check($conf, 'SetBetweenFilter')) {
      return;
    }
    $f = new BetweenFilter($this);
    $f->id = $conf->getId();
    $f->id2 = $conf->getId2();
    $f->title = $conf->getTitle();
    $f->title2 = $conf->getTitle2();
    $f->label_width = $conf->getLabelWidth();
    $f->label_width2 = $conf->getLabelWidth2();
    $f->size = $conf->getSize();
    $f->newline = $conf->getNewLine();
    $f->attach($this->form, $this->form_state);
  }
  
  // LabeledFilter (A markup and a textfield)
  public function addLabeledFilter ($conf) {
    if (!Set::check($conf, 'SetLabeledFilter')) {
      return;
    }
    $f = new LabeledFilter($this);
    $f->id = $conf->getId();
    $f->title = $conf->getTitle();
    $f->required = $conf->getRequired();
    $f->size = $conf->getSize();
    $f->label_width = $conf->getLabelWidth();
    $f->newline = $conf->getNewLine();
    $f->attach($this->form, $this->form_state);
  }
  
  // TextFilter (A markup, a select, and a textfield)
  public function addTextFilter ($conf) {
    if (!Set::check($conf, 'SetTextFilter')) {
      return;
    }
    $f = new TextFilter($this);
    $f->id = $conf->getId();
    $f->title = $conf->getTitle();
    $f->required = $conf->getRequired();
    $f->label_width = $conf->getLabelWidth();
    $f->size = $conf->getSize();
    $f->newline = $conf->getNewLine();
    $f->attach($this->form, $this->form_state);
  }

  // TextareaFilter (A markup, a select, and a textarea)
  public function addTextareaFilter ($conf) {
    if (!Set::check($conf, 'SetTextAreaFilter')) {
      return;
    }
    $f = new TextAreaFilter($this);
    $f->id = $conf->getId();
    $f->title = $conf->getTitle();
    $f->required = $conf->getRequired();
    $f->label_width = $conf->getLabelWidth();
    $f->cols = $conf->getColumns();
    $f->rows = $conf->getRows();
    $f->newline = $conf->getNewLine();
    $f->attach($this->form, $this->form_state);
  }
  
  // SelectFilter (A markup and a select). 
  // The available values are the distinct values of specified column in specified table. 
  // Multiple columns are allowed by passing an array of columns for which values are 
  // concatenated by a comma (,). 
  // Common selection values can be passed in as an array to the $optgroup variable. 
  // Alternatively, the selection values can be grouped by passing in a $optgroup_by_pattern 
  // variable consisting of array('Display Group' => 'pattern'). 
  // If $cache is TRUE, a cacahe table will be created and this will greatly improve the 
  // performance for rendering the search form. The cache will automatically refresh when 
  // new data are added and you will never need to clear it by hand.
  public function addSelectFilter ($conf) {
    if (!Set::check($conf, 'SetSelectFilter')) {
      return;
    }
    $f = new SelectFilter($this);
    $f->id = $conf->getId();
    $f->title = $conf->getTitle();
    $f->column = $conf->getColumn();
    $f->table = $conf->getTable();
    $f->required = $conf->getRequired();
    $f->multiple = $conf->getMultiple();
    $f->column_natural_sort = $conf->getColumnNaturalSort();
    $f->optgroup = $conf->getOptGroup();
    $f->optgroup_by_pattern = $conf->getOptGroupByPattern();
    $f->cache = $conf->getCache();
    $f->label_width = $conf->getLabelWidth();
    $f->size = $conf->getSize();
    $f->newline = $conf->getNewLine();
    $f->disables = $conf->getDisable();
    $f->only = $conf->getOnly();
    $f->attach($this->form, $this->form_state);
  }
  
  // SelectOptionsFilter (A markup and a select). The available values are passed in.
  public function addSelectOptionFilter ($conf) {
    if (!Set::check($conf, 'SetSelectOptionFilter')) {
      return;
    }
    $f = new SelectOptionFilter($this);
    $f->id = $conf->getId();
    $f->title = $conf->getTitle();
    $f->options = $conf->getOptions();
    $f->required = $conf->getRequired();
    $f->multiple = $conf->getMultiple();
    $f->nokeyconversion = $conf->getNoKeyConversion();
    $f->label_width = $conf->getLabelWidth();
    $f->newline = $conf->getNewLine();
    $f->size = $conf->getSize();
    $f->attach($this->form, $this->form_state);
  }
  
  // addDynamicMarkup (A markup whose value was derived from a select element). A $value variable will be passing into your AJAX function
  public function addDynamicMarkup ($conf) {
    if (!Set::check($conf, 'SetDynamicMarkup')) {
      return;
    }
    $f = new DynamicMarkup($this);
    $f->id = $conf->getId();
    $f->depend_on_id =$conf->getDependOnId();
    $f->callback = $conf->getCallback();
    $f->newline = $conf->getNewLine();
    $f->attach($this->form, $this->form_state);
  }
  
  // DynamicSelectFilter (A computed select whose value was derived from another select element). A $value variable will be passing into your AJAX function
  public function addDynamicSelectFilter ($conf) {
    if (!Set::check($conf, 'SetDynamicSelectFilter')) {
      return;
    }
    $f = new DynamicSelectFilter($this);
    $f->id = $conf->getId();
    $f->title = $conf->getTitle();
    $f->depend_on_id = $conf->getDependOnId();
    $f->callback = $conf->getCallback();
    $f->newline = $conf->getNewLine();
    $f->label_width = $conf->getLabelWidth();
    $f->newline = $conf->getNewLine();
    $f->cacheTable = $conf->getCacheTable();
    $f->cacheColumns = $conf->getCacheColumns();
    $f->reset_on_change_id = $conf->getResetOnChange();
    $f->attach($this->form, $this->form_state);
  }
  
  // addDynamicTextFields. Upon select status changes, a couple of text fields can be populated with values. A $value variable will be passing into your AJAX function
  public function addDynamicTextFields ($conf) {
    if (!Set::check($conf, 'SetDynamicTextFields')) {
      return;
    }
    $f = new DynamicTextFields($this);
    $f->id = $conf->getId();
    $f->target_ids = $conf->getTargetIds();
    $f->callback = $conf->getCallback();
    $f->newline = $conf->getNewLine();
    $f->reset_on_change_id = $conf->getResetOnChange();
    $f->attach($this->form, $this->form_state);
  }
  
  // Fieldset
  public function addFieldset ($conf) {
    if (!Set::check($conf, 'SetFieldset')) {
      return;
    }
    $f = new Fieldset($this);
    $f->id = $conf->getId();
    $f->title = $conf->getTitle();
    $f->start_widget = $conf->getStartWidget();
    $f->end_widget = $conf->getEndWidget();
    $f->desc = $conf->getDescription();
    $f->collapased = $conf->getCollapased();
    $f->newline = $conf->getNewLine();
    $f->attach($this->form, $this->form_state);
  }
  
  // Tabs
  public function addTabs ($conf) {
    if (!Set::check($conf, 'SetTab')) {
      return;
    }
    $f = new Tab($this);
    $f->id = $conf->getId();
    $f->items = $conf->getItems();
    $f->newline = $conf->getNewLine();
    $f->attach($this->form, $this->form_state);
  }
  
  // Drupal Throbber - add a throbber to a form element
  public function addThrobber ($conf) {
    if (!Set::check($conf, 'SetThrobber')) {
      return;
    }
    $f = new Throbber($this);
    $f->id = $conf->getId();
    $f->newline = $conf->getNewLine();
    $f->attach($this->form, $this->form_state);
  }
  
  // Hyperlink for quick selection on a Select box
  public function addSelectShortCut ($conf) {
    if (!Set::check($conf, 'SetSelectShortCut')) {
      return;
    }
    $f = new SelectShortcut($this);
    $f->id = $conf->getId();
    $f->selectbox_id = $conf->getSelectId();
    $f->value = $conf->getValue();
    $f->pretext = $conf->getPretext();
    $f->postext = $conf->getPosttext();
    $f->newline = $conf->getNewLine();
    $f->attach($this->form, $this->form_state);
  }
  
  // Allow user to customize output
  public function addCustomOutput ($conf) {
    if (!Set::check($conf, 'SetCustomOutput')) {
      return;
    }
    $f = new CustomOutput($this);
    $f->id = $conf->getId();
    $f->options = $conf->getOptions();
    $f->defaults = $conf->getDefaults();
    $f->title = $conf->getTitle();
    $f->desc = $conf->getDescription();
    $f->collapsible = $conf->getCollapsible();
    $f->collapsed = $conf->getCollapsed();
    $f->group_selection= $conf->getGroupSelection();
    $f->max_columns = $conf->getMaxColumns();
    $f->attach($this->form, $this->form_state);
    
  }
  
  /**********************************************************
   * Return the final form
  */
  // Attach result table and return the form
  public function getForm () {
    $form = $this->form;
    $form_state = $this->form_state;
    $result = isset($form_state['values']['result']) ? $form_state['values']['result'] : NULL;
    if ($result) {
      $form['result'] = array(
          '#markup' => $result,
      );
    }
    return $form;
  }
}
