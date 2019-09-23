/* global jQuery */
"use strict";

import 'presstify-framework/template/list-table/js/scripts';
import 'presstify-framework/partial/progress/js/scripts';

jQuery(function ($) {
  /** @param {Object} $.tify */
  $.widget('tify.tifyListTable', $.tify.tifyListTable, {
    // Instanciation de l'élément.
    _create: function () {
      this._super();

      this.import = {progress: undefined, xhr: undefined};
      this._initImportProgress();
    },
    // INITIALISATIONS.
    // -----------------------------------------------------------------------------------------------------------------
    // Initialisation des événements déclenchement.
    _initEvents: function () {
      this._super();

      this._on(this.el, {'click [data-control="list-table.import-rows"]': this._onClickImportRows});
    },
    // Initialisation de l'indicateur de progression d'import.
    _initImportProgress: function () {
      if (this.import.progress === undefined) {
        this.import.progress = $('[data-control="list-table.import-rows.progress"]', this.el).tifyProgress();
      }
    },
    // EVENEMENTS.
    // -----------------------------------------------------------------------------------------------------------------
    // Clique sur le bouton de lancement de l'import complet.
    _onClickImportRows: function (e) {
      e.preventDefault();

      let self = this,
          table = self.dataTable.api(),
          info = table.page.info(),
          /** @param {number} info.recordsDisplay */
          max = info.recordsDisplay-info.start;

      this.importProgress('max', max);

      self._doImportRows(table.page.info().page);
    },
    // ACTIONS.
    // -----------------------------------------------------------------------------------------------------------------
    // Import des lignes d'une page.
    _doImportRows(page) {
      let self = this,
          table = self.dataTable.api(),
          callback = function (index) {
            let info = table.page.info(),
                paged = info.page+1,
                idx = index+info.start;

            self.import.xhr.abort();
            self.import.xhr = undefined;

            self.importProgress('increment');

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
      if (this.import.xhr === undefined) {
        let self = this,
            table = self.dataTable.api(),
            info = table.page.info(),
            row = table.row(index),
            paged = info.page + 1,
            idx = index + info.start,
            ajax = $.extend({}, self.option('ajax') || {}, {data: {action: 'import', idx: idx, paged: paged}});

        this.import.xhr = $.ajax(ajax);

        $(row.node()).attr('aria-progress', 'true');

        if (typeof callback === "function") {
          this.import.xhr.then(function () {
            callback(index, ...arguments);
          });
        }
      }
    },
    // ACCESSEURS.
    // -----------------------------------------------------------------------------------------------------------------
    // Délégation d'appel de l'indicateur de progression.
    importProgress: function () {
      return this.import.progress.tifyProgress(...arguments);
    },
  });
});