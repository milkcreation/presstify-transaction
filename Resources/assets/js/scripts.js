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
      this._on(this.el, {'click [data-control="list-table.row-action.import"]': this._onClickRowActionImport});
    },
    // Clique sur l'action d'import d'un élément.
    _onClickRowActionImport: function (e) {
      e.preventDefault();

      let $self = $(e.target);

      $.ajax({
        url: $self.attr('href'),
        method: 'POST',
        type: 'json'
      }).done(function (resp) {
        console.log(resp);
      });
    },
  });
});