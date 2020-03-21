'use strict';

import jQuery from 'jquery';
import 'jquery-ui/ui/core';
import 'jquery-ui/ui/widget';
import 'presstify-framework/template/list-table/js/scripts';
import 'presstify-framework/partial/progress/js/scripts';

jQuery(function ($) {
  /** @param {Object} $.tify */
  $.widget('tify.tifyListTable', $.tify.tifyListTable, {
    // Instanciation.
    _create: function () {
      this._super();

      this.import = {handler: undefined, progress: undefined, xhr: undefined};
      this._initImportHandler();
    },
    // INITIALISATIONS.
    // -----------------------------------------------------------------------------------------------------------------
    // Initialisation des événements déclenchement.
    _initEvents: function () {
      this._super();

      this._on(this.el, {'click [data-control="list-table.import-rows"]': this._onClickImportRows});
      this._on(this.el, {'click [data-control="list-table.import-rows.cancel"]': this._onClickImportRowsCancel});
    },
    // Initialisation de l'indicateur de traitement d'import.
    _initImportHandler: function () {
      if (this.import.handler === undefined) {
        this.import.handler = $('[data-control="list-table.import-rows.handler"]', this.el);
      }
      if (this.import.progress === undefined) {
        this.import.progress = $('[data-control="list-table.import-rows.progress"]', this.el).tifyProgress();
      }
    },
    // EVENEMENTS.
    // -----------------------------------------------------------------------------------------------------------------
    // Clique sur le bouton de lancement de l'import complet.
    _onClickImportRows: function (e) {
      e.preventDefault();

      let table = this.dataTable.api(),
          info = table.page.info(),
          /** @param {number} info.recordsDisplay */
          max = info.recordsDisplay - info.start;

      this.importProgress().max(max).reset();
      this._doImportHandlerShow();

      this._doImportRows(table.page.info().page);
    },
    // Clique sur le bouton d'annulation de l'import complet.
    _onClickImportRowsCancel: function (e) {
      e.preventDefault();

      let table = this.dataTable.api();

      if (this.import.xhr) {
        this.import.xhr.abort();
        this.import.xhr = null;

        table.rows().every(function () {
          $(this.node()).attr('aria-progress', 'false');
        });
        this._doImportHandlerHide();
      }
    },
    // ACTIONS.
    // -----------------------------------------------------------------------------------------------------------------
    // Masquage de l'indicateur de progression.
    _doImportHandlerHide() {
      this.import.handler.attr('aria-display', 'false');
    },
    // Import des lignes d'une page.
    _doImportHandlerShow() {
      this.import.handler.attr('aria-display', 'true');
    },
    // Import des lignes d'une page.
    _doImportRows(page) {
      let self = this,
          table = self.dataTable.api(),
          callback = function (index) {
            let info = table.page.info(),
                paged = info.page + 1,
                idx = index + info.start;

            self.importProgress('increment');

            if (idx + 1 < info.end) {
              table.one('draw.list-table.import-row', function () {
                self._doImportRow(index + 1, callback);
              }).row(index).draw(false);
            } else if (table.page.info().pages > paged) {
              self._doImportRows(paged);
            } else {
              table.row(index).draw(false);
              table.one('draw.list-table.import-rows', function () {
                self._doImportHandlerHide();
              });
            }
          };

      this.import.xhr = undefined;

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
      if (!this.isImportXhrCanceled()) {
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
    // VERIFICATEUR
    // Verification d'annulation de l'import.
    isImportXhrCanceled: function () {
      return (this.import.xhr === null) || (this.import.xhr && this.import.xhr.readyState === 0);
    },
    // ACCESSEURS.
    // -----------------------------------------------------------------------------------------------------------------
    // Délégation d'appel de l'indicateur de progression.
    importProgress: function () {
      this.import.progress.tifyProgress(...arguments);

      return this.import.progress.tifyProgress('instance');
    }
  });
});