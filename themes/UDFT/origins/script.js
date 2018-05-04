jQuery(document).ready( function( $ ) {

    $('ul.menu .tab-item').on( 'click', function( e ) {
        if ( !$(e.target).hasClass('active') ) {
            $(e.target).parents('ul.menu').find('a').removeClass('active');
            $('.main-tab').removeClass('active');
            $(e.target).addClass('active');
            $('.main-tab').each( function( i, el ) {
                if ( $(e.target).attr('data-target') == '' || $(el).attr('data-target') == $(e.target).attr('data-target') ) {
                    $(el).addClass('active');
                }
            });
        }
        else {
        }
    });

    $('a.tab-link').on( 'click', function( e ) {
        e.preventDefault();
        if ( !$(e.target).hasClass('active') ) {
            $(e.target).parents('.main-tab').find('a.tab-link').removeClass('active');
            $(e.target).parents('.tab-page').find('.main-tab').removeClass('opened');
            $(e.target).addClass('active');
            $(e.target).parents('.main-tab').addClass('opened');
        }
        else {
            $(e.target).removeClass('active');
            $(e.target).parents('.main-tab').removeClass('opened');
        }
    });


    $('.imprs-box .tab2-trigger, .products-box .tab2-trigger').on( 'click', function ( e ) {
        $(e.target).parents('.tabs2-box').find('.tab2-box').removeClass( 'active' );
        if ( !$(e.target).parents('.tab2-box').hasClass('active') ) {
            $(e.target).parents('.tab2-box').addClass('active')
        }
        /*if ( $(window).width() > 987 )
            $('.product-box.active .product-content-box').css( { 'top' : $('.pr-box').scrollTop() + 'px' });*/
        if ( $(window).width() > 987 ) {
            $('.product-box.active .product-content-box').css({'top': -1 * $('.mCSB_container').css('top').replace( 'px', '' ) + 'px' });
        }
    });

    $('.menu-button').on( 'click', function( e ) {
        var trg;
        if ( !$(e.target).hasClass('.menu-button') ) trg = $('.menu-button');
        else trg = $(e.target);

        if ( !$(trg).hasClass('active') ) {
            $(trg).addClass('active');
            $('ul.menu').addClass('active');
        }
        else {
            $(trg).removeClass('active');
            $('ul.menu').removeClass('active');
        }
    });


    $('.tab-marks .tab-mark').on( 'click', function( e ) {
        var mark = $(e.target).attr( 'title' );
        $('.tab-marks .tab-mark').each( function ( i, el ) {
            if ( i < mark ) $(el).addClass('passed');
            else $(el).removeClass('passed');
        });
        $(e.target).parents('.tab-request').find('input.info-mark').val(mark);
    });


    /*$('.pr-box').on( 'scroll', function ( e ) {
        if ( $(window).width() > 987 ) {
            $('.product-box.active .product-content-box').css({'top': $(e.target).scrollTop() + 'px'});
        }
    });*/

    $(".pr-box").mCustomScrollbar({
            callbacks: {
                whileScrolling: function () {
                    if ( $(window).width() > 987 ) {
                        $('.product-box.active .product-content-box').css({'top': -1 * $('.mCSB_container').css('top').replace( 'px', '' ) + 'px' });
                    }
                }
            }
        }
    );

    $('.mm-content-title').on( 'mouseenter', function( e ) {
        $(e.target).parents('.mm-blocks-box').find('.mm-content-box').each( function ( i, el ) {
            $(el).removeClass('active');
        });
        $(e.target).parent().addClass('active');
    } );


    $('.mm-items-box > .menu-link').on( 'mouseenter', function( e ) {
        $(e.target).parents('.menu-top-block').find('.mm-items-box').each( function ( i, el ) {
            $(el).removeClass('active');
        });
        $(e.target).parents('.mm-items-box').addClass('active');
    } );




    $('.imprs-box .tab2-trigger').first().trigger('click');
    $('.products-box .tab2-trigger').first().trigger('click');
    $('a.tab-item').first().trigger('click');
    $('.header-menu .menu-top-block').each( function ( i, el ) {
        $(el).find('.mm-items-box').each( function ( i, el ) {
            if ( i == 0 ) $(el).addClass('active');
        });
    });
    $('.header-menu .mm-content-div').each( function ( i, el ) {
        $(el).find('.mm-content-box').each( function ( i, el ) {
            if ( i == 0 ) $(el).addClass('active');
        });
    });


});