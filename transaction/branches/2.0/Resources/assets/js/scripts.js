/* global jQuery */
"use strict";

/**
 * @see https://learn.jquery.com/jquery-ui/widget-factory/extending-widgets/
 * @see http://github.bililite.com/extending-widgets.html
 */
jQuery(function ($) {
  $.widget('tify.tifyListTable', $.tify.tifyListTable, {
    _initEvents: function(){
      this._super();
    }
  });
});