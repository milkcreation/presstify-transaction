jQuery(document).ready(function ($) {
    /**
     * TELECHARGEMENT DU FICHIER D'IMPORT
     */
    $('.tiFyTemplatesFileImportUploadForm-FileInput').on('change', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $closest = $(this).closest('.tiFyTemplatesFileImport-Form--upload');
        $spinner = $('.tiFyTemplatesFileImportUploadForm-Spinner', $closest);

        // Affichage du spinner        
        $spinner.addClass('is-active');

        // Traitement des données
        files = e.target.files;
        var data = new FormData(),
            action = $('#ajaxActionFileImport').val();

        $.each(files, function (key, value) {
            data.append(key, value);
        });

        $.ajax({
            url: tify_ajaxurl + '?action=' + action,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (resp, textStatus, jqXHR) {
                $('#ajaxDatatablesData').val(encodeURIComponent(JSON.stringify(resp.data)));
                AjaxListTable.draw(true);

                // Masquage du spinner 
                $spinner.removeClass('is-active');
            }
        });
    });
});