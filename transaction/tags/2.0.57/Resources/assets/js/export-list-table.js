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

    },
    // INITIALISATIONS.
    // -----------------------------------------------------------------------------------------------------------------
    // Initialisation des événements déclenchement.
    _initEvents: function () {
      this._super();

    }
  });
});