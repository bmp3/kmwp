jQuery(document).ready( function( $ ) {

    /*$('.submit-code').on( 'click', function( e ) {
        saveExtraSettings( { 'name' : 'activation_code', 'value' : $('.gsc-code').val(), 'callback' : activationCodeCB() } );
    });

    function activationCodeCB() {
        return function() {

        }
    }

    $('.refresh-ssid').on( 'click', function( e ) {
        e.preventDefault();
        saveExtraSettings( { 'name' : 'googlesheetID', 'value' : $('.refresh-ssid-input').val(), 'callback' : ssIDCB() } );
    });

    function ssIDCB() {
        return function ( data ) {
            $('.refresh-ssid-input').val()
        }
    }*/

    $('.gsc-code').on( 'blur', function( e ) {
        if ( $(e.target).val().length > 10 ) {
            $('.gsc-settings-button').removeClass('disabled');
        }
    });


    function saveExtraSettings( atts = { 'name' : null, 'value' : null, 'callback' : null } ) {

        var atts = atts;

        return function( atts ) {

            $.ajax({
                'type': 'POST',
                'data': { action : 'gsc_save_extra_settings', 'name' : atts['name'], 'value' : atts['value'] },
                'url': ajaxdata.siteurl + '/wp-admin/admin-ajax.php',
                'processData': true,
                'success': function (data) {
                    data = JSON.parse(data);
                    atts.callback( data );
                }
            });

        }( atts )

    }

    $('.gsc-forms').on('change', function (e) {
        var form_id;
        if ( e.target.nodeName == 'SELECT' )
            form_id = $(e.target).find('option:selected').val();
        else
            form_id = $(e.target);
        $('.form-fields-box').each(function (i, el) {
            if ($(el).attr('data-form') == form_id)
                $(el).addClass('active');
            else
                $(el).removeClass('active');
        });
    });

    $('.form-fields.sortable').sortable({
        revert: true,
        connectWith: ".connectedSortable",
        update: function (e, ui) {
            var end_pos = ui.item.index();
            if (ui.sender) {
                $(ui.sender).find('.form-item').each(function (i, el) {
                    $(el).attr('data-position', i);
                });
            }
            $(ui.item).parent().find('.form-item').each(function (i, el) {
                $(el).attr('data-position', i);
            });
        }
    });

    $('.gsc-btn.enable-btn, .gsc-btn.disable-btn, .gsc-btn.save-btn').on('click', function (e) {
        var data = {};
        data.form_id = $(e.target).parents('.form-fields-box').attr('data-form');
        data.action = 'gsc_save';
        if ($(e.target).hasClass('save-btn')) {
            data.state = $(e.target).parents('.form-fields-box').attr('data-state');
        }
        else {
            if ($(e.target).hasClass('enable-btn')) {
                data.state = 'enabled';
            }
            else if ($(e.target).hasClass('disable-btn')) {
                data.state = 'disabled';
            }
        }
        data.fields = {};
        data.fields.active = {};
        data.fields.hidden = {};
        $(e.target).parents('.form-fields-box').find('.form-fields.active .form-item').each(
            function (i, el) {
                data.fields.active[i] = {
                    'position': $(el).attr('data-position'),
                    'type': $(el).attr('data-type'),
                    'name': $(el).attr('data-name'),
                    'state': 'active'
                }
            });
        $(e.target).parents('.form-fields-box').find('.form-fields.hidden .form-item').each(
            function (i, el) {
                data.fields.hidden[i] = {
                    'position': $(el).attr('data-position'),
                    'type': $(el).attr('data-type'),
                    'name': $(el).attr('data-name'),
                    'state': 'hidden'
                }
            });

        data = JSON.stringify( data );

        $.ajax({
            'type': 'POST',
            'data': { action : 'gsc_save', data : data },
            'url': location.protocol + '//' + location.host + '/kmwp/wp-admin/admin-ajax.php',
            'processData': true,
            'contentType': 'application/x-www-form-urlencoded; charset=UTF-8',
            'enctype': 'multipart/form-data',
            'success': function (data) {
                data = JSON.parse(data);
                if ( data.value ) {
                    var b, t;
                    b = $( e.target ).parents('.form-fields-box').find('.enable-disable-btn');
                    if ( data.value == 'enabled' ) {
                        t = $( b).text().replace('Enable', 'Disable');
                        $( b ).removeClass('enable-btn').addClass('disable-btn');
                    }
                    else if ( data.value == 'disabled' ) {
                        t = $( b ).text().replace('Disable', 'Enable');
                        $( b ).removeClass('disable-btn').addClass('enable-btn');
                    }
                    $( b ).empty().html( t );
                }
            }
        });

    });

    $('.gsc-change-settings .tab-title span').on( 'click', function( e ) {
        $('.gsc-change-settings .tab-box').removeClass('active');
        $(e.target).parents('.tab-box').addClass('active');
    });

    $('.gsc-forms').val( $('.gsc-forms option:last-child').val() ).trigger( 'change', { 'target' : $('.gsc-forms option:last-child').val() } );
    if ( $('.gsc-code').val().length > 10 ) {
        $('.gsc-settings-button').removeClass('disabled');
    }

});