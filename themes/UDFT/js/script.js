jQuery(document).ready( function( $ ) {

    $(window).on( 'scroll', function( e ) {
        if ( $(window).scrollTop() > 40 ) {
            $(document.body).addClass('has-fixed-el');
            //$('.top-panel-box').addClass('fixed-el');
        }
        else {
            $(document.body).removeClass('has-fixed-el');
            //$('.top-panel-box').removeClass('fixed-el');
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
            $('.custom-header').addClass('menu-opened');
        }
        else {
            $(trg).removeClass('active');
            $('ul.menu').removeClass('active');
            $('.custom-header').removeClass('menu-opened');
        }
    });


    $('.tab-marks .tab-mark').on( 'click', function( e ) {
        var mark;
        if ( e.start )
            mark = e.value;
        else
            mark = $(e.target).attr( 'title' );
        $(e.target).parents('.tab-marks').find('.tab-mark').each( function ( i, el ) {
           if ( i < mark ) $(el).addClass('passed');
           else $(el).removeClass('passed');
        });
        $(e.target).parents('.tab-request').find('input.info-mark').val(mark);
    });


    $('.tab-form-box .tab-btn').on( 'click', function(e) {
        return;
        e.preventDefault();

        var formData = new FormData();
        $(e.target).parents('form').find("input[type='file']").each(
            function(i, tag) {
                $.each($(tag)[0].files, function(i, file) {
                    formData.append(tag.name, file);
                });
            });
        var params = $(e.target).parents('form').serializeArray();
        $.each(params, function (i, val) {
            formData.append(val.name, val.value);
        });

        formData.append('action', 'send_faq_data');

        $(e.target).parents('.tab-request').find('.rate-request-anwer').empty().removeClass('active');

        $.ajax({
            'type'        : 'POST',
            'data'        : formData,
            'url'         : location.protocol + '//' + location.host + '/wp-admin/admin-ajax.php',
            'processData' : false,
            'contentType' : false,
            'enctype'     : 'multipart/form-data',
            'success'     : function(data) {
                data = JSON.parse(data);
                $(e.target).parents('.tab-request').find('.rate-request-anwer').html('<span>' + data.content + '</span>').addClass('active');
                setTimeout( function() {
                    $(e.target).parents('.tab-request').find('.rate-request-anwer').empty().removeClass('active');
                }, 3000);
            }
        });
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
        if ( document.body.clientWidth >= 1194 ) {
            $(e.target).parents('.mm-blocks-box').find('.mm-content-box').each(function (i, el) {
                $(el).removeClass('active');
            });
            $(e.target).parent().addClass('active');
        }
    } );


    $('.mm-items-box > .menu-link').on( 'mouseenter', function( e ) {
        if ( document.body.clientWidth >= 1194 ) {
            $(e.target).parents('.menu-top-block').find('.mm-items-box').each(function (i, el) {
                $(el).removeClass('active');
            });
            $(e.target).parents('.mm-items-box').addClass('active');
        }
    } );

    /*$('.menu-item .item-icon').on( 'click', function( e ) {
        e.preventDefault();
        if ( $(e.target).hasClass('depth-0') ) {
            $(e.target).closest('.menu-top-item').toggleClass('opened')
        }
        else if ( $(e.target).hasClass('depth-1') ) {
            $(e.target).closest('.menu-item').toggleClass('opened')
        }
    });*/


    function toggle_box( args ) {

        var args;

        if ( !args ) {
            args = { 'css_class' : 'opened',
                'super_parent' : 'ul.menu',
                'parent_box' : '.menu-top-item',
                'target_box' : '.menu-top-block',
                'target_el' : '.mm-items-box',
                'time' : 500 }
        }
        else args = args;

        return function ( e ) {

            if ($(window).width() < 1194) {

                e.preventDefault();

                var h = 0;

                $(e.target).parents(args['parent_box']).find(args['target_box']).first().find(args['target_el']).each(function (i, el) {
                    h += $(el).outerHeight();
                });


                if (!$(e.target).parents(args['parent_box']).hasClass(args['css_class'])) {
                    $(e.target).parents(args['super_parent']).find(args['parent_box']).each(
                        function (i, el) {
                            if ($(el).hasClass(args['css_class'])) {
                                $(el).find(args['target_box']).animate({'height': '0px'},
                                    {
                                        'duration': args['time'],
                                        'done': function () {
                                            $(el).removeClass(args['css_class']);
                                        }
                                    });
                            }
                        });

                    $(e.target).parents(args['parent_box']).find(args['target_box']).first().animate({'height': h + 'px'},
                        {
                            'duration': args['time'],
                            'done': function () {
                                $(e.target).parents(args['parent_box']).addClass(args['css_class']);
                                $(e.target).parents(args['parent_box']).find(args['target_box']).css({'height': 'auto'});
                            }
                        });
                }
                else {
                    $(e.target).parents(args['parent_box']).find(args['target_box']).first().animate({'height': 0 + 'px'},
                        {
                            'duration': args['time'],
                            'done': function () {
                                $(e.target).parents(args['parent_box']).removeClass(args['css_class']);
                            }
                        });
                }

            }

        }

    }


    $('a.menu-link').on( 'click', function( e ) {
         var trg, c;

        if ( e.target.nodeName == 'SPAN' ) trg = $(e.target).parent();
        else trg = $(e.target);

        c = $(trg).attr('class')
         /*if ( c == 'mm-content-title'  || e.target.nodeName == 'SPAN' ) {
         }
         else {
             e.preventDefault();
         }*/
        if ( c && ( !c.match(/parent-link/) && !c.match(/main-menu-link/) && !c.match(/sub-menu-link/) ) )
            e.preventDefault();
    });

    $('.parent-link, a.mm-content-title').on( 'click', function( e ) {

        var trg;

        if ( e.target.nodeName == 'SPAN' ) trg = $(e.target).parent();
        else trg = $(e.target);

        if ( !$(trg).parent().hasClass('clicked') ) {
            $(trg).parent().addClass('clicked');
            setTimeout(function () {
                $(trg).parent().removeClass('clicked');
            }, 1000);
        }
    });

    $('.menu-top-item.menu-item-has-children > a').on( 'click', toggle_box( ) );
    $('.mm-items-box > a').on( 'click', toggle_box(
            {
                'css_class' : 'opened',
                'super_parent' : '.menu-top-block',
                'parent_box' : '.mm-items-box',
                'target_box' : '.mm-content-div',
                'target_el' : '.mm-item',
                'time' : 500
            }
        )
    );

    $(window).on( 'resize', function( e ) {
        if ( $(window).width() > 1194 ) {
            //$('.header-menu > ul.menu').mCustomScrollbar('destroy');
            $( '.header-menu .menu .menu-top-item .menu-top-block, .mm-items-box' ).css( { 'height': 'auto' });
            $('.mm-items-box .mm-content-div').css( { 'height' : '547px' } );
        }
        else {
            $( '.header-menu .menu .menu-top-item .menu-top-block, .mm-content-div' ).css( { 'height' : '0px' } );
            //$('.header-menu > ul.menu').mCustomScrollbar();
        }
    } );

    /*if ( $(window).outerWidth() > 1194 ) {
        $('.header-menu > ul.menu').mCustomScrollbar('destroy');
    }
    else {
        $('.header-menu > ul.menu').mCustomScrollbar();
    }*/








    faq_started = 0;

    $('ul.menu-faq .tab-item').on( 'click', function( e ) {
        if ( !$(e.target).hasClass('active') ) {
            $(e.target).parents('ul.menu').find('a').removeClass('active');
            $('.main-tab').removeClass('active');
            $(e.target).addClass('active');
            $('.main-tab').each( function( i, el ) {
                if ( $(e.target).attr('data-target') == '' || $(el).attr('data-target') == $(e.target).attr('data-target') ) {
                    $(el).addClass('active');
                }
            });
            if ( $(e.target).parent().is( ':last-child' ) && faq_started == 0 ) {
                $('.more-button-box button.btn').trigger( 'click' );
                faq_started = 1;
                return;
            }
        }
        else {
        }
    });


    $('.more-button-box button.btn').on( 'click', function( e ) {

        var manager, items, v = 0, h = 0;
        manager = $(e.target).parents('.tabs-container').find('.tabs-titles a.tab-item.active');
        if ( $(manager).attr('data-target') != '' ) {

            items = $(e.target).parents('.tabs-container').find('.tabs-box');

            $(items).each( function ( i, el ) {
                if ( $(el).attr('data-target') == $(manager).attr('data-target') ) {
                    if ( $(el).hasClass('to-display') ) {
                    }
                    else {
                        if ( parseInt(v) < 8 ) {
                            $(el).addClass('to-display');
                            v++;
                        }
                    }
                }
            });

            $(manager).attr( 'data-active-items', v );

        }
        else {
            items = $('.tabs-container .tabs-box');

            $(items).each( function ( i, el ) {
                if ( !$(el).hasClass('to-display') && parseInt( v ) < 8 ) {
                    $(el).addClass('to-display');
                    v++;
                }
            });

        }



    });

    function formValidate( form ) {

        var res;

        $(form).find('.f-input').each( function( i, el ) {
            $(el).parent().removeClass('has-error');
            value = $(el).val();
            switch ( $(el).attr('name') ) {
                case'email':
                    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
                    res = re.test(value);
                    break;
                case'phone':
                    value = value.replace(/[-+ ()_]/g, '');
                    res = value.length > 9 && value.length < 14;
                    break;
                case'name':
                    res = value.length != 0;
                    break;
            }
            if ( !res ) {
                $(el).parent().addClass('has-error');
                //$(el).val( '' );
            }
            else $(el).parent().removeClass('has-error');
        });

        return res;

    }


    $('form .f-input, form .f-textarea').on( 'blur', function( e ) {
        formValidate( $(e.target).parents('form') );
    });

    $('a.mhf').on( 'click', function( e ) {

        if ( $(e.target).hasClass('force-link') ) return;

        e.preventDefault();

        if ( formValidate( $(e.target).parents('form') ) ) {

            var formData = new FormData(), call;
            $(e.target).parents('form').find("input[type='file']").each(
                function(i, tag) {
                    $.each($(tag)[0].files, function(i, file) {
                        formData.append(tag.name, file);
                    });
                });
            var params = $(e.target).parents('form').serializeArray();
            $.each(params, function (i, val) {
                formData.append(val.name, val.value);
            });

            formData.append('action', 'send_form_data');

            //$(e.target).parents('form').find('a.button').addClass('loading');
            $("html, body").addClass('has-modal');
            $('.modal-box').css( { top : $(window).scrollTop() + 'px' } );

            call = $.ajax({
                'type'        : 'POST',
                'data'        : formData,
                'url'         : location.protocol + '//' + location.host + '/kmwp/wp-admin/admin-ajax.php',
                'processData' : false,
                'contentType' : false,
                'enctype'     : 'multipart/form-data',
                'success'     : function(data) {
                    data = JSON.parse(data);
                    //$(e.target).parents('form').find('a.button').removeClass('loading');
                    setTimeout( function() {
                        $("html, body").removeClass('has-modal');
                        window.location = '/thank-you';
                    }, 1000 );
                }
            });

            /*setTimeout( function() {
                call.abort();
                call = null;
                window.location = '/';
            }, 2000);*/

        }

    });


    $('form.lead-form input, form.lead-form textarea').on( 'focus', function( e ) {
        if ( $(window).outerWidth() < 480 ) {
            $('.line-menu').animate( { 'opacity' : 0 }, 500, function() { $('.line-menu').addClass('hidden'); });
        }
    });

    $('body').on( 'click', function( e ) {
        if ( $(window).outerWidth() < 480 ) {
            if (!$(e.target).hasClass('f-input') && !$(e.target).hasClass('f-textarea') && !$(e.target).hasClass('placeholder')) {
                $('.line-menu').removeClass('hidden').animate( { 'opacity' : 1 }, 500 );
            }
        }
    });

    $('.placeholder').on( 'click', function( e ) {
        if ( !$(e.target).hasClass('active') ) {
            if ( $(e.target).parent().find('input, textarea').val().length == 0 ) {
                $(e.target).addClass('active');
            }
            $(e.target).parent().find('input, textarea').trigger( 'focus' );
        }
        else {
            if ( $(e.target).parent().find('input, textarea').val().length == 0 ) {
               $(e.target).removeClass('active');
            }
            $(e.target).parent().find('input, textarea').trigger('blur');
        }
    } );

    $('.f-input, .f-textarea').on( 'focus', function( e ) {
        $(e.target).parent().find('.placeholder').addClass('active');
    } );

    $('.f-input, .f-textarea').on( 'blur', function( e ) {
        if ( $(e.target).val().length == 0 ) {
            $(e.target).parent().find('.placeholder').removeClass('active');
        }
    } );

    $('.f-input, .f-textarea').each( function( i, el ) {
        if ( $(el).val().length > 0 ) {
            $(el).parent().find('.placeholder').addClass( 'active' );
        }
        else {
            $(el).parent().find('.placeholder').addClass('visible');
        }
    });

    setTimeout( function() {
        $('.placeholder.active').addClass('visible');
    }, 700);



    $('.imprs-box .tab2-trigger').first().trigger('click');
    $('.products-box .tab2-trigger').first().trigger('click');
    $('a.tab-item').eq(1).trigger('click');
    $('.more-button-box button.btn').trigger( 'click' );
    //$('a.tab-item').eq(2).trigger('click');
    //$('.more-button-box button.btn').trigger( 'click' );
    $('a.tab-item').eq(0).trigger('click');
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
    $('.tab-form-box').each( function( i, el ) {
        var mark = $(el).find('input.info-mark').val();
        $(el).parents('.tab-request-box').find('.tab-mark').each(function( i, el ) {
             if ( $(el).attr('title') == mark ) {
                 $(el).trigger( 'click', { 'start' : true, 'value' : mark } );
             }
        });
    });



    var animatein_time = 0.5;
    var delay_time = 2.5;

    function slide1() {
        $(".svg .slide1").stop(true);
        $(".svg #slide1_img1").animate({top: "370px"}, animatein_time * 1000, function () {
            $(".svg #slide1_text").animate({top: "0px"}, animatein_time * 1000, function () {
                $(".svg .slide1").delay(delay_time * 1000).animate({opacity: 0}, animatein_time * 1000, function () {
                    slide2()
                });
            });
        });
    }

    function slide2() {
        $(".svg .slide2").stop(true);
        $(".svg #slide2_img1").animate({top: "410px"}, animatein_time * 1000, function () {
            $(".svg #slide2_text").animate({top: "0px"}, animatein_time * 1000, function () {
                $(".svg .slide2").delay(delay_time * 1000).animate({opacity: 0}, animatein_time * 1000, function () {
                    slide3()
                });
            });
        });
    }

    function slide3() {
        $(".svg .slide3").stop(true);
        $(".svg #slide3_img1").animate({top: "500px"}, animatein_time * 1000, function () {
            $(".svg #slide3_text").animate({top: "0px"}, animatein_time * 1000, function () {
                $(".svg .slide3").delay(delay_time * 1000).animate({opacity: 0}, animatein_time * 1000, function () {
                    slide4()
                });
            });
        });
    }

    function slide4() {
        $(".svg .slide4").stop(true);
        $(".svg #slide4_img1").animate({top: "380px"}, animatein_time * 1000, function () {
            $(".svg #slide4_img2").animate({top: "450px"}, animatein_time * 1000, function () {
                $(".svg #slide4_img3").animate({top: "520px"}, animatein_time * 1000, function () {
                    $(".svg #slide4_text").animate({top: "0px"}, animatein_time * 1000, function () {
                        $(".svg .slide4").delay(delay_time * 1000).animate({opacity: 0}, animatein_time * 1000, function () {
                            slide5()
                        });
                    })
                });
            });
        });
    }

    function slide5() {
        $(".svg .slide5").stop(true);
        $(".svg #slide5_img1").animate({top: "380px"}, animatein_time * 1000, function () {
            $(".svg #slide5_img2").animate({top: "450px"}, animatein_time * 1000, function () {
                $(".svg #slide5_img3").animate({top: "520px"}, animatein_time * 1000, function () {
                    $(".svg #slide5_text").animate({top: "0px"}, animatein_time * 1000, function () {
                        $(".svg .slide5").delay(delay_time * 1000).animate({opacity: 0}, animatein_time * 1000, function () {
                            slide6()
                        });
                    })
                })
            });
        });
    }

    function slide6() {
        $(".svg .slide6").stop(true);
        $(".svg #slide6_img1").animate({top: "300px"}, animatein_time * 1000, function () {
            $(".svg #slide6_text").animate({top: "0px"}, animatein_time * 1000, function () {
                $(".svg .slide6").delay(delay_time * 1000).animate({opacity: 0}, animatein_time * 1000, function () {
                    slide7()
                });
            });
        });
    }

    function slide7() {
        $(".svg .slide7").stop(true);
        $(".svg #slide7_img1").animate({top: "320px"}, animatein_time * 1000, function () {
            $(".svg #slide7_text").animate({top: "0px"}, animatein_time * 1000, function () {
                $(".svg .slide7").delay(delay_time * 1000).animate({opacity: 0}, animatein_time * 1000, function () {
                    slide8()
                });
            });
        });
    }

    function slide8() {
        $(".svg .slide8").stop(true);
        $(".svg #slide8_img1").animate({top: "360px"}, animatein_time * 1000, function () {
            $(".svg #slide8_text").animate({top: "0px"}, animatein_time * 1000, function () {
                $(".svg .slide8").delay(delay_time * 1000).animate({opacity: 0}, animatein_time * 1000, function () {
                    loop_clear()
                });
            });
        });
    }

    function loop_clear() {
        $(".svg .slide").each(function (i, element) {
            $(element).css({opacity: 1, top: (-1 * $(element).height()) - 10});
        });
        slide1();
    }

    slide1();

    //initBanner();





    function setPhoneMask(selector) {
        var eventHandler;
        if (typeof window.jQuery.fn.mask !== 'undefined') {
            var el1 = jQuery(selector);
            el1.attr('maxlength',40);
            el1.focus(function(){
                el1.unmask();
            });
            el1.blur(function(){
                var val = jQuery(this).first().val().replace( /\D+/g, '');

                var $mask = val.length<14?"(000) 000-0000":"+000 (000) 000-0000#";
                switch (val.length){
                    case 11:
                        $mask = "+0 (000) 000-0000";
                        break;
                    case 12:
                        $mask = "+00 (000) 000-0000";
                        break;
                    case 13:
                        $mask = "+000 (000) 000-0000";
                        break;
                }
                el1.mask($mask);
            });

        } else {
            setTimeout(setPhoneMask, 1000, selector);
        }
    }


    setPhoneMask('form input[name="phone"]');

   /* var options =  {
        onKeyPress: function(cep, e, field, options) {
            var masks = ['+0 (000) 000-0000', '+00 (000) 000-0000', '+000 (000) 000-0000' ], mask = '+0 (000) 000-0000';
            switch (cep.length){
                case 11:
                    mask = "+0 (000) 000-0000";
                    break;
                case 12:
                    mask = "+00 (000) 000-0000";
                    break;
                case 13:
                    mask = "+000 (000) 000-0000";
                    break;
            }
            $('form input[name="phone"]').mask(mask, options);
        }};

    $('form input[name="phone"]').mask('00000-000', options);*/


});