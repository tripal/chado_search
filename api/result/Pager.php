<?php

namespace ChadoSearch\result;

use ChadoSearch\SessionVar;

class Pager extends Source {
  
  public function __construct($search_id, $path, $total_pages, $showPager = TRUE) {
    $js = $this->jsPager ($search_id, $path);
    $html = $this->htmlPager ($search_id, $total_pages);
    if ($showPager) {
      $this->src = $js . $html;
    } else {
      $this->src = $js;
    }
  }
  
  private function htmlPager ($search_id, $total_pages) {
    $firstLoadPages = 1000;
    $id = '#' . $search_id . "-pager";
    $pager =
      "<script type=\"text/javascript\">
          (function ($) {
            $(document).ready(function(){
              chadoSearchCheckPageRange();
            });
            function chadoSearchCheckPageRange () {
              var currentPage = $('$id').val();
                if (currentPage == 1) {
                  $('#$search_id-pager-previous').hide();
                } else if ($('#$search_id-pager-previous').is(':hidden')) {
                  $('#$search_id-pager-previous').show();
                }
                if (currentPage == $total_pages) {
                  $('#$search_id-pager-next').hide();
                } else if ($('#$search_id-pager-next').is(':hidden')) {
                  $('#$search_id-pager-next').show();
                }
                if ((+currentPage + $firstLoadPages) > $total_pages) {
                  $('.chado_search-pager-fastforward').hide();
                } else {
                  $('.chado_search-pager-fastforward').show();
                }
                if (currentPage <= $firstLoadPages) {
                  $('.chado_search-pager-fastrewind').hide();
                } else {
                  $('.chado_search-pager-fastrewind').show();
                }
              }
            window.chadoSearchCheckPageRange = chadoSearchCheckPageRange;
            function chadoSearchLoadMorePages() {
              var lastLoadedPage = $('$id option:last-child').val();
              if (lastLoadedPage < $total_pages) {
                var select_id = '$search_id' + '-pager';
                var elmt = document.getElementById(select_id);
                var start = + lastLoadedPage + 1;
                var end = start + $firstLoadPages;
                for(var i=start; i<=$total_pages && i < end; i++) {
                  opt = document.createElement('option');
                  opt.value = i;
                  opt.innerHTML = i;
                  elmt.appendChild(opt);
                }
              }
            }
            window.chadoSearchLoadMorePages = chadoSearchLoadMorePages;
          })(jQuery);
    </script>" .
    "<div id=\"$search_id-pager-dropdown\" class=\"chado_search-pager-dropdown\">";
    $pager .= 
      "<div id=\"$search_id-pager-fastrewind\" class=\"chado_search-result_widget chado_search-pager-fastrewind\">
          <a href=\"javascript:void(0)\" onClick=\"
              (function ($) {;$search_id" . "_change_page(parseInt($('$id').val())-$firstLoadPages-1, null);
                $('$id').val(parseInt($('$id').val())-$firstLoadPages);
                chadoSearchCheckPageRange();
                return false;
              })(jQuery);\">
            &lt;&lt;
          </a>
        </div>";
    $pager .=
      "<div id=\"$search_id-pager-previous\" class=\"chado_search-result_widget chado_search-pager-previous\">
          <a href=\"javascript:void(0)\" onClick=\"
              (function ($) {;
                $search_id" . "_change_page(parseInt($('$id').val()) - 2, null);
                $('$id').val(parseInt($('$id').val()) - 1);
                chadoSearchCheckPageRange();
                return false;
              })(jQuery);\">
            &lt; Previous 
          </a>
       </div>";
    $pager .= 
      "<div  class=\"chado_search-result_widget\">
          Page
          <select id=\"$search_id-pager\" onChange=\"$search_id" . "_change_page(this.selectedIndex, null);chadoSearchLoadMorePages();\">";
            for ($i = 1; $i <= $total_pages; $i ++) {
              $pager .= "<option>$i</option>";
              if ($i >= $firstLoadPages) {
                break; // load only first few pages so the select dropdown won't overload. the rest will be loaded ondemand
              }
            }
    $pager .= 
      "   </select>
           of $total_pages
        </div>";
    $pager .= 
      "<div id=\"$search_id-pager-next\" class=\"chado_search-result_widget chado_search-pager-next\">
          <a href=\"javascript:void(0)\" onClick=\"
              (function ($) {;
                $search_id" . "_change_page(parseInt($('$id').val()), null);
                $('$id').val(parseInt($('$id').val()) + 1);
                chadoSearchCheckPageRange();
                return false;
              })(jQuery);\">
            Next &gt;
          </a>
        </div>";
    $pager .= 
      "<div id=\"$search_id-pager-fastforward\" class=\"chado_search-result_widget chado_search-pager-fastforward\">
          <a href=\"javascript:void(0)\" onClick=\"
              (function ($) {;
                chadoSearchLoadMorePages();
                $search_id" . "_change_page(parseInt($('$id').val())+$firstLoadPages-1, null);
                $('$id').val(parseInt($('$id').val())+$firstLoadPages);
                chadoSearchCheckPageRange();
                return false;
              })(jQuery);\">
            &gt;&gt;
          </a>
        </div>";
    $pager .= "</div>";
    return $pager;
  }
  
  public function jsPager ($search_id, $path) {
    if ($path == NULL) {
      $path = "search/$search_id/pager/";
    } else {
      $path = $path . "/pager/";
    }
    //process POST data
    $counter = 0;
    $dpost = "form_build_id=" . $_POST['form_build_id']; // Also pass the form_build_id to allow multi-tab session variables
    global $base_url;
    $js = "
      <script type=\"text/javascript\">
        (function ($) {
          function " . $search_id . "_change_page (page, order) {
            var link = '$base_url';
            link += '/$path' + page;
            var postdata = '$dpost';
            var current_order = $('#" . $search_id . "_current_order').val();
            if (order != null) {
              postdata += '&orderby=' + order;
              if (current_order == order) {
                if (current_order.match(/ DESC$/)) {
                  postdata.replace('%20DESC', '');
                } else {
                  postdata += '%20DESC';
                }
              }
            } else {
               if (current_order !== undefined) {
                 postdata += '&orderby=' + current_order;
               }
            }
            $('.chado_search-$search_id-waiting-box').show();
            $.ajax({
              url: link,
              data: postdata,
              dataType: 'json',
              type: 'POST',
              success: function(data){
                $('#" . $search_id . "-result').html(data.update);
                $('.chado_search-$search_id-waiting-box').hide();
                chadoSearchCheckPageRange ();
              }
            });
          }
          window." . $search_id . "_change_page = " . $search_id . "_change_page;
          function " . $search_id . "_change_order (column) {
            " . $search_id . "_change_page(0, column);
            $('#$search_id-pager').val(1);
          }
          window." . $search_id . "_change_order = " . $search_id . "_change_order;
        })(jQuery);
      </script>";
    return $js;
  }
  
  // Switch to the specified page
  public static function switchPage ($search_id, $page, $num_per_page) {
    $sql =  SessionVar::getSessionVar($search_id, 'sql');
    if (!$sql) {
      return array('update' => "<script>alert('Session expired. Please re-submit the search form.');</script>");
    }
    $orderby = isset($_POST['orderby']) ? $_POST['orderby'] : '';
    $offset = $num_per_page * $page;
    if ($orderby) {
      $sql .= " ORDER BY " . $orderby;
      SessionVar::setSessionVar($search_id, 'download-order', $orderby);
    }
    $sql .= " LIMIT $num_per_page OFFSET $offset";
    $result = chado_query($sql);
    $table_definition_callback = 'chado_search_' . $search_id . '_table_definition';
    if (function_exists($table_definition_callback)) {
      $headers = $table_definition_callback();
    }
    else {
      $headers = SessionVar::getSessionVar($search_id, 'default-headers');
    }
    $autoscroll = SessionVar::getSessionVar($search_id, 'autoscroll');
    $table = new Table($search_id, $result, $page, $num_per_page, $headers, $orderby, $autoscroll);
    $update = $table->getSrc();
    if ($orderby) {
      $update .= "<input id=\"" . $search_id . "_current_order\" type=\"hidden\" value=\"$orderby\">";
    }
    return array('update' => $update);
  }
  
  public static function totalPages ($total_items, $num_per_page) {
    $total_pages = (int) ($total_items / $num_per_page);
    if ($total_items % $num_per_page != 0) {
      $total_pages ++;
    }
    return $total_pages;
  }
}