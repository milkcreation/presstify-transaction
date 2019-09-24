jQuery( document ).ready( function( $ ){
     var // Préfixe des actions ajax
         ajax_action_prefix = $( '#ajaxActionPrefix' ).val();
         // Processus actif
        process = false;
         
    $( '#tiFyTemplatesExport-Submit' ).on( 'click', function(e){
        e.preventDefault();
        
        process = true;
        
        var data = JSON.parse( decodeURIComponent( $( '#ajaxData' ).val() ) ); 
        $( '.tiFyTemplatesExport-ProgressBar' ).tiFyProgress( 
            'option', 
            { 
                show:       true, 
                value:      0,
                close :     function( event, ui )
                    {                        
                        // Désactivation du processus d'export
                        process = false;
                        
                        // Attend de la fin de l'import en court pour fermer l'interface
                        $( document ).on( 'tiFyTemplatesExport.complete', function(){
                            ui.close();
                        });                        
                    } 
            } 
        );

        tiFyTemplatesExport( data );
    });
    
    var tiFyTemplatesExport = function( data )
    {
        if( ! process ){
            $( document ).trigger( 'tiFyTemplatesExport.complete' );
            return;
        }
        
        $.ajax({
            url :           tify.ajaxurl,
            data :          data,
            success :       function( resp ){   
                $( '.tiFyTemplatesExport-ProgressBar' ).tiFyProgress( 'option', { value: (resp.data.paged*resp.data.per_page), max: resp.data.total_items } );
                
                if( resp.data.paged >= resp.data.total_pages ){
                    $( '#tiFyTemplatesExport-Progress' ).removeClass( 'active' );
                    $( '#tiFyTemplatesExport-DownloadFile' ).html( '<a href="'+ resp.data.upload_url +'" title="'+resp.data.title+'">'+ resp.data.file +'</a>' );
                    window.location.href = resp.data.upload_url;
                    process = false;
                     $( '.tiFyTemplatesExport-ProgressBar' ).tiFyProgress( 'close' );
                }  
                tiFyTemplatesExport( resp.data.query_args );
            }
        });    
    }
});