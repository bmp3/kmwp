(function($) {
	"use strict";

    function handle_widgets() {
	
	    function remove_widget_area(e) {
		    var widget = $(e.currentTarget).parents('.widgets-holder-wrap:eq(0)'),
			    title = widget.find('.sidebar-name h2'),
				spinner = $(title).children('span'),
				widget_name = $.trim(title.text());
				
				$(spinner).css({'visibility' : 'visible'});
		        widget.addClass('closed');


		        $.ajax({
                    type: "POST",
                    url: window.ajaxurl,
                    data: {
                         action: 'sl_delete_widget_area',
                         name: widget_name,
                    },
          
                    success: function(response) {     
                        if(response.trim() == 'widget_area-deleted') {
                           widget.slideUp(200).remove();
                        } 
                    }
                });		
        }
		
		function add_widget_handler(e) {
		    var response,
			    widget_name = $('input#sl-add-widget-input').val();
		    e.preventDefault();
			$('#sl-add-widget .spinner').css({ 'display' : 'block' });
			$.ajax({
			    type: "POST",
			    url: window.ajaxurl,
                data: {
                    action: 'sl_add_widget_area',
                    name: widget_name,
                },
                success: function(response) { 
				    response = JSON.parse(response.trim());
					if (response['code'] == 0) {
					    $('input#sl-add-widget-input').css({ 'borderColor' : 'red' })
						$('#widget_informer').css({ 'display' : 'block' }).html(response['text']);
						$('#sl-add-widget .spinner').css({ 'display' : 'none' });
					}
					if (response['code'] == 1) {
					    $('input#sl-add-widget-input').css({ 'borderColor' : '#ddd' });
						$('#sl-add-widget .spinner').removeClass('spinner').addClass('redirect').html('<strong>redirecting ...</strong>');
						window.location.href = document.location.href;
                    }					
                }				
			});
			return false;
		}

        $('#widgets-right').find('.sidebar-sl-custom .widgets-sortables').each(function(i, el) {
		    $(el).append('<span class="sl-widget_area-delete"></span>');
        });		
		
		$('.widget-liquid-right').on('click', 'span.sl-widget_area-delete', remove_widget_area);
	    
		$('#widgets-right').prepend($('#sl-add-widget-template').html());
		$('#sl-add-widget-template').remove();
		
		$('input.addWidgetArea-button').on('click', add_widget_handler)
	}
	
	handle_widgets();
  
})(jQuery);  


