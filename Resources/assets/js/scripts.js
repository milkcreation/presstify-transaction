/* global jQuery */
"use strict";

jQuery(function ($) {
  $.widget('tify.tifyListTable', $.tify.tifyListTable, {
    _initEvents: function () {
      this._super();
      this._on(this.el, {'click [data-control="list-table.full-import"]': this._onClickImportRows});
    },
    // EVENEMENTS
    // Clique sur le bouton de lancement de l'import complet.
    _onClickImportRows: function (e) {
      e.preventDefault();

      let self = this,
          table = self.dataTable.api();

      self._doImportRows(table.page.info().page);
    },
    // ACTIONS
    // Import des lignes d'une page.
    _doImportRows(page) {
      let self = this,
          table = self.dataTable.api(),
          callback = function (index, resp) {
            let info = table.page.info(),
                paged = info.page+1,
                idx = index+info.start;

            if (idx+1 < info.end) {
              table.one('draw.list-table.import-row', function () {
                self._doImportRow(index+1, callback);
              }).row(index).draw(false);
            } else if (table.page.info().pages > paged) {
              self._doImportRows(paged);
            } else {
              table.row(index).draw(false);
            }
          };

      if (table.page.info().page !== page) {
        table.one('draw.list-table.import-rows', function () {
          self._doImportRow(0, callback);
        }).page(page).draw('page');
      } else {
        self._doImportRow(0, callback);
      }
    },
    // Import de la ligne d'une page.
    _doImportRow(index, callback) {
      let self = this,
          table = self.dataTable.api(),
          info = table.page.info(),
          row = table.row(index),
          paged = info.page+1,
          idx = index+info.start,
          ajax = $.extend({}, self.option('ajax') || {}, {data: {action: 'import', idx: idx, paged: paged}}),
          xhr = $.ajax(ajax);

      $(row.node()).attr('aria-progress', 'true');

      if (typeof callback === "function") {
        xhr.then(function(resp) {
          callback(index, resp);
        });
      }
    }
  });
});