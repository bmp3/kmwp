jQuery(document).ready( function($) {

	function handle_progress_bar( args ) {

		var k = 0, progress = args.current_progress.value, total = args.finish_value.value;
		if ( args.finish_value.value != 0 ) k = args.current_progress.value / args.finish_value.value;
		if ( args.to_percentage ) {
			progress = ( k * 100 ).toFixed(1) + '%';
			total = ( args.finish_value.value / 1000 ).toFixed(1) + 'Kb';
		}

		$(args.box).find(args.start_value.selector).text( args.start_value.value );
        $(args.box).find(args.finish_value.selector).text( total );
        $(args.box).find(args.current_progress.selector).text( progress );

        $(args.box).find('.progress-bar').css( { 'width' : parseInt( k * $(args.box).innerWidth() ) + 'px' } );
	}

    gio = {

		imgs : [],
        items : [], 		
		busy : false,
		globalBusy : false,
		bad_requests : 0,
		folders : [],
		files : 0,
		progress : 0,
		success : 0,
		no_need : 0,
		rejected : 0,
		general_bytes : 0,
		saved_bytes : 0,
		mtt : null,
		gtt : null,

		
		init : function( start = true ) {
			clearInterval( gio.mtt );
			clearInterval( gio.gtt );
			gio.busy = false;
			gio.globalBusy = false;
			gio.bad_requests = 0;
			gio.progress = 0;
			gio.success = 0;
			gio.no_need = 0;
			gio.rejected = 0;
			gio.general_bytes = 0,
			gio.saved_bytes = 0,
			gio.total = gio.items.length;
			
			gio.setInfo();

			if ( !start ) { 
			    gio.items.length = 0;
				$.ajax({
					type : "POST",
					url : ajaxurl,
					data : { action : 'gio_cancel_optimize' },
					success : function() {
						$('.gio-start-optimize').removeClass('active');
					},
					error : function() {
					}
				});
			}
			else {
				$('.rejected-imgs').text('');
			}

		},
		
		message : function( args = { 'result' : 'img_compressed', 'message' : '', 'img_info' : null } ) {
			if ( args.result == 'folder_started' ) {
				$('.gio-results').prepend('<div class="gio-result success">' + args.message + '</div>');
			}
			if ( args.result == 'img_compressed' ) {
				$('.gio-results').prepend('<div class="gio-result success">' + args.message + '</div>');
				gio.success++;
                gio.progress++;
			}
			else if ( args.result == 'img_passed' ) {
				$('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
				gio.no_need++;
                gio.progress++;
			}
            else if ( args.result == 'google_rejected' ) {
                $('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
				gio.rejected++;
                gio.progress++;				
            }
            else if ( args.result == 'manual_recompress' ) {
				$('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
				gio.rejected++;
                gio.progress++;
            }			
            else if ( args.result == 'google_soft_reject' ) {
                $('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
            }
			else if ( args.result == 'img_has_invalid_format' ) {
				$('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
                gio.progress++;
			}			
			else if ( args.result == 'no_more_imgs' ) {
				$('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
			}
			else if ( args.result == 'empty_dir' ) {
				$('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
			}	
            else if ( args.result == 'google_hard_reject' ) {
				$('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
            }
            else if ( args.result == 'server_reject' ) {
				$('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
            }	
            else if ( args.result == 'empty_folder' ) {
				$('.gio-results').prepend('<div class="gio-result error">' + args.message + '</div>');
            }
			

			gio.setInfo( { 'img_info' : args.img_info } );
			
		},
		
		setCookie : function (name, value, options) {
		  options = options || {};

		  var expires = options.expires;

		  if (typeof expires == "number" && expires) {
			var d = new Date();
			d.setTime(d.getTime() + expires * 1000);
			expires = options.expires = d;
		  }
		  if (expires && expires.toUTCString) {
			options.expires = expires.toUTCString();
		  }

		  value = encodeURIComponent(value);

		  var updatedCookie = name + "=" + value;

		  for (var propName in options) {
			updatedCookie += "; " + propName;
			var propValue = options[propName];
			if (propValue !== true) {
			  updatedCookie += "=" + propValue;
			}
		  }

		  document.cookie = updatedCookie;
		},	

		getCookie : function (name) {
		  var matches = document.cookie.match(new RegExp(
			"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
		  ));
		  return matches ? decodeURIComponent(matches[1]) : undefined;
		},

		deleteCookie : function(name) {
		  setCookie(name, "", {
			expires: -1
		  })
		},

		implode : function ( glue, pieces ) {	// Join array elements with a string
			return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
		},

		explode : function ( delimiter, string ) {	// Split a string by string

			var emptyArray = { 0: '' };

			if ( arguments.length != 2
				|| typeof arguments[0] == 'undefined'
				|| typeof arguments[1] == 'undefined' )
			{
				return null;
			}

			if ( delimiter === ''
				|| delimiter === false
				|| delimiter === null )
			{
				return false;
			}

			if ( typeof delimiter == 'function'
				|| typeof delimiter == 'object'
				|| typeof string == 'function'
				|| typeof string == 'object' )
			{
				return emptyArray;
			}

			if ( delimiter === true ) {
				delimiter = '1';
			}

			return string.toString().split ( delimiter.toString() );
		},	
		
		optimizeImages : function () {
			var currentImg, data;
			if ( gio.imgs.length > 0 ) {
				if ( !gio.busy ) {
					gio.busy = true;
					currentImg = gio.imgs[0];
					gio.optimizeSingleImg( currentImg )( currentImg );
				}
			}
			else {
				gio.busy = false;
				clearInterval( gio.mtt );
				$('.destination-dirs .selected-item').each( function( i, el ) {
					$(el).removeClass('selected-item');
				});				
				$('.gio-start-optimize').removeClass('active');
				if ( gio.items.length > 0 ) {
                    gio.items.shift();
                    gio.globalBusy = false;
					if ( gio.items.length == 0 ) {
						clearInterval(gio.gtt);
						$.ajax({
							type : "POST",
							url : ajaxurl,
							data : { action : 'gio_cancel_optimize' },
							success : function() {
							},
							error : function() {
							}
						});						
					}
                }

			}
			
			
		},
		
		optimizeSingleImg : function ( img ) {
			global_img = img;
			return function( img ) {

				$('.info .current-file').text( img );

				if ( !global_img.match( /.jpg|.png|.jpeg/ ) ) {
					gio.message( { 'result' : 'img_has_invalid_format', 'message' : 'file ' + global_img + ' has invalid format' } );
					gio.imgs.shift();
					gio.busy = false;
					return;				
				}
				if ( gio.bad_requests > 1440 ) {
					gio.imgs.length = 0;
					gio.bad_requests = 0;
					busy = false;
					clearInterval( mtt );
					gio.message( { 'result' : 'google_hard_reject', 'message' : gio_data.google_hard_reject } );
					return;
				}			
				$.ajax({
					type : "POST",
					url : ajaxurl,
					data : { action : 'gio_optimize_single_img', img : global_img },
					success: function(data)	{
						if ( gio.items.length == 0 ) return;
						data = JSON.parse(data);
						if ( data.result == 1 ) {
							gio.message( { 'result' : 'img_compressed', 'message' : data.content, 'img_info' : data.current_img } );
							gio.bad_requests = 0;
							gio.imgs.shift();
							gio.busy = false;
						}
                        else if ( data.result == 2 ) {
                            gio.message( { 'result' : 'google_rejected', 'message' : data.content, 'img_info' : { type : 'google_rejected', file : gio.getFileName( data.current_img.file ) } } );
                            gio.bad_requests = 0;
                            gio.imgs.shift();
                            gio.busy = false;
                        }
                        else if ( data.result == 3 ) {
                            gio.message( { 'result' : 'img_passed', 'message' : data.content } );
                            gio.bad_requests = 0;
                            gio.imgs.shift();
                            gio.busy = false;
                        }	
                        else if ( data.result == 4 ) {
                            gio.message( { 'result' : 'manual_recompress', 'message' : data.content, 'img_info' : { type : 'manual_recompress', file : gio.getFileName( data.current_img.file ) } } );
                            gio.bad_requests = 0;
                            gio.imgs.shift();
                            gio.busy = false;
                        }						
						else if ( data.result == 0 ) {
							if ( data.content == 'http request error, try later' ) {
								gio.message( { 'result' : 'google_soft_reject', 'message' : gio_data.gio_soft_reject } );
								setTimeout( gio.optimizeSingleImg( global_img ), 5000);
								gio.bad_requests++;
							}
						}
					},
					error: function() {
							gio.message( { 'result' : 'server_reject', 'message' : gio_data.server_reject } );
							setTimeout( gio.optimizeSingleImg( global_img ), 5000);
							gio.bad_requests++;					
					}				
				});
			}		
		},

		setInfo : function( args ) {

			var bytes = 0;
			if ( gio.items.length == 0 ) return;
			if ( args && args.img_info && args.img_info.start ) bytes = args.img_info.start;
			gio.general_bytes += bytes;

            if ( args && args.img_info && args.img_info.type == 'success' ) {
                var percentage;
                $('.info .saving .saved-bytes').html( parseInt( $('.info .saving .saved-bytes').text() ) + parseInt( args.img_info.save ) );
                $('.info .saving .general-bytes').html( parseInt( $('.info .saving .general-bytes').text() ) + parseInt( args.img_info.start ) );
                percentage = parseInt( $('.info .saving .saved-bytes').text() ) / parseInt( $('.info .saving .general-bytes').text() );
                $('.info .general-percentage').html( (percentage*100).toFixed(2) + '%' );

                gio.saved_bytes += args.img_info.save;

            }
            if ( args && args.img_info && args.img_info.type && ( args.img_info.type == 'google_rejected' || args.img_info.type == 'manual_recompress' ) ) {
                $('.info .rejected-imgs').append( '<div>' + gio.getFileName( args.img_info.file ) + '</div>' );
            }

            handle_progress_bar( {
				'box' : '.progress-bar-box',
				'start_value' : { 'selector' : '.start-value span', 'value' : 0 },
                'finish_value' : { 'selector' : '.finish-value span', 'value' : gio.total },
                'current_progress' : { 'selector' : '.current-progress span', 'value' : gio.progress }
			} );

            handle_progress_bar( {
                'box' : '.saving-bar-box',
                'start_value' : { 'selector' : '.start-value span', 'value' : 0 },
                'finish_value' : { 'selector' : '.finish-value span', 'value' : gio.general_bytes },
                'current_progress' : { 'selector' : '.current-progress span', 'value' : gio.saved_bytes },
				'to_percentage' : true
            } );

            $('.info .digit.success').text( gio.success );
            $('.info .no-need').text( gio.no_need );
            $('.info .rejected').text( gio.rejected );
        },
		
		getFileName : function( file ) {
			if ( file && file != undefined ) var r = file.match( /wp-content.+/ );
			if ( r && r[0] ) return r[0];
			else return '';
		}
	
	}

    $('.admin-gio-box button.gio-start-optimize').on( 'click', function(e)	{
		if ( gio.items.length == 0 ) {         
		    $('.destination-dirs .selected-item').each( function( i, el ) {
			    $(el).removeClass('selected-item');
		    });
			$(e.target).addClass('ready');
			return; 
		}
		gio.init( true );
		$('.gio-start-optimize').addClass('active');
		if ( gio.items.length > 0 ) {
			$(e.target).removeClass('ready');
			gio.progress = 0;
			gio.gtt = setInterval(function () {
				if ( !gio.globalBusy && gio.items[0] && gio.items[0] != '' ) {
					gio.globalBusy = true;
					gio.currentFolder = gio.items[0];
					gio.imgs = gio.items;
					gio.mtt = setInterval(gio.optimizeImages, 200);
				}
			}, 200);
		}		
		
    });
	
	$('.admin-gio-box button.gio-cancel-optimize').on( 'click', function(e) {
        gio.init( false );	
        $('.destination-dirs .selected-item').each( function( i, el ) {
			$(el).removeClass('selected-item');
		});
	});

	$('.dir-link').on( 'click', function(e) {
		e.preventDefault();
		if ( !gio.busy ) {
            var _target = $(e.target).parent();
            if ( !e.ctrlKey ) {
            	if ( !$(_target).hasClass('opened-dir-box') ) {
                    $(_target).children('ul.dir-box').addClass('opened-dir');
                    $(_target).addClass('opened-dir-box');
                }
                else {
                    $(_target).children('ul.dir-box').removeClass('opened-dir');
                    $(_target).removeClass('opened-dir-box');
                }
            }
            else if( e.ctrlKey ) {
                if ( !$(_target).hasClass('selected-item') ) {
					$(e.target).parent().find('a.file-link').each( function( i, el ) {
						if ( $.inArray( $(el).attr('data-value'), gio.items ) == -1 ) 
							gio.items.push( $(el).attr('data-value') );
					});
					$('#gio-dest').text(gio.items.join());
                    $(_target).addClass('selected-item');
                    $(_target).find('.dir-link-box').addClass('selected-item');
                    $(_target).find('.file-link-box a').addClass('selected-item');
                    $(_target).find('.file-link a').addClass('selected-item');
				}
				else {
					$(e.target).parent().find('a.file-link').each( function( i, el ) {
                        gio.items.remByVal($(el).attr('data-value'));
					});
                    $('#gio-dest').text(gio.items.join());
                    $(_target).removeClass('selected-item');
                    $(_target).find('.dir-link-box').removeClass('selected-item');
                    $(_target).find('.file-link-box a').removeClass('selected-item');
                    $(_target).find('.file-link a').removeClass('selected-item');
				}
			}

        }
	});

	$('.file-link').on( 'click', function(e) {
        e.preventDefault();
        if ( !gio.busy ) {
        	if ( e.ctrlKey ) {
        		if ( !$(e.target).hasClass('selected-item') ) {
					if ( $.inArray( $(e.target).attr('data-value'), gio.items ) == -1 )
						gio.items.push( $(e.target).attr('data-value') );
                    $('#gio-dest').text(gio.items.join());
                    $(e.target).addClass('selected-item');
                }
                else {
                    gio.items.remByVal($(e.target).attr('data-value'));
                    $('#gio-dest').text(gio.items.join());
                    $(e.target).removeClass('selected-item');
				}
            }
        }
	});
	
	
	$('.settings-submit').on( 'click', function( e ) {
		$(e.target).addClass('active');
		$.ajax({
			type : "POST",
			url : ajaxurl,
			data : { action : 'gio_save_settings', 'gio_key' : $('input.gio_key').val() },
			success : function( data ) {
				$('.admin-gio-box .settings-prompt').removeClass( 'visible' ).detach();
				$('.admin-gio-box .controls-container').addClass( 'visible' );
				$('.admin-gio-box .cover').detach();
				$(e.target).removeClass('active');
				
			},
			error : function() {
			}
		});		
	});
	
	Array.prototype.remByVal = function(val) {
		for (var i = 0; i < this.length; i++) {
			if (this[i] === val) {
				this.splice(i, 1);
				i--;
			}
		}
		return this;
	}	
	
	gio.init( false );
	
	if ( !$('.admin-gio-box .controls-container').hasClass('visible') ) {
		$('.admin-gio-box .controls-container').prepend('<div class="cover"></div>');
	}

	$( '.gio-start-optimize' ).on( 'mouseleave', function( e ) {
		$(e.target).removeClass( 'ready' );
	});

});
