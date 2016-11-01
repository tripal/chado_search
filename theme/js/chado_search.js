(function ($) {
  $(document).ready(function(){
    //Restore fieldset status
    $('.chado_search-fieldset-status-hidden').each(function() {
      var fset = '#' + $(this).attr('id').replace('-status-hidden', '');
      var content = '#' + $(this).attr('id').replace('-status-hidden', '-content');
      if($(this).val() == 'show') {        
        $(fset).css('border-bottom', '1px solid #CCCCCC');
        $(fset).css('border-left', '1px solid #CCCCCC');
        $(fset).css('border-right', '1px solid #CCCCCC');
        $(content).show();
      } else {
        $(fset).css('border-bottom', '0px');
        $(fset).css('border-left', '0px');
        $(fset).css('border-right', '0px');
        $(content).hide();
      }
    });
  });
  
  // Toggle fieldset
  function chado_search_fieldset_toggle(object) {
    var content = '#' + $(object).parent().attr('id') + '-content';
    var status = '#' + $(object).parent().attr('id') + '-status-hidden';
    if ($(content).is(":visible")) {
      $(content).hide(300);
      $(object).parent().css('border-bottom', '0px');
      $(object).parent().css('border-left', '0px');
      $(object).parent().css('border-right', '0px');
      $(status).val('hide');
    } else {
      $(object).parent().css('border-bottom', '1px solid #CCCCCC');
      $(object).parent().css('border-left', '1px solid #CCCCCC');
      $(object).parent().css('border-right', '1px solid #CCCCCC');
      $(content).show(300);
      $(status).val('show');
    }
  }
  window.chado_search_fieldset_toggle = chado_search_fieldset_toggle;
  
  // Remember the computed select value after submitting the form
    function chado_search_js_change_hidden (id) {
        var hidden = '#chado_search-filter-' + id + '-field-hidden';
        var select = '#chado_search-filter-' + id + '-field-select';
        $(hidden).val($(select).val());
    }
    window.chado_search_js_change_hidden = chado_search_js_change_hidden;
})(jQuery);