jQuery(document).ready( function( $ ) {


    $('.img-control').each( function( i, el ) {
        if ( $(el).attr('data-value') == $(el).parents('.row').find('input.ps-input').val() )
            $(el).addClass('selected');
    });

    $('.img-control').on( 'click', function( e ) {
        var el, v;
        if ( !$(e.target).hasClass('img-control') ) el = $(e.target).parent();
        else el = $(e.target);
        $(el).parents('.row').find('.img-control').removeClass('selected');
        $(el).addClass('selected');
        v = $(el).parents('.row').find('input.ps-input').val();
        $(el).parents('.row').find('input.ps-input').val($(el).attr('data-value')).trigger( 'change', v );
    });

    /*$('.ps-input').on( 'change', function ( e, v ) {
        if ( v != $(e.target).val() )
            $('.ps-changed').val(1);
    });*/

    $('[name^="acf"]').on( 'change', function ( e, v ) {
        var p = $(e.target).parents('.acf-field');
        if ( $(p).attr('data-name') == 'width-layout' || $(p).attr('data-name') == 'sidebar-layout' || $(p).attr('data-name') == 'header-bg'
             || $(p).attr('data-name') == 'header-content' || $(p).attr('data-name') == 'header-text-img' ||
                $(p).attr('data-name') == 'header-form-img' || $(p).attr('data-name') == 'thank-you-img' ) {
            if (v != $(e.target).val())
                $('.ps-changed').val(1);
        }
    });

    $('button.reset-post-settings').on( 'click', function( e ) {
        $(e.target).parent().find('input.ps-reset').val(1);
        $('input#publish').trigger( 'click' );
    });

});