<?php
$content = '
<div id="sl-add-widget-template">
				<div id="sl-add-widget" class="widgets-holder-wrap">
				    <div id="widget_informer"></div>
				        <div class="sl-add-widget-template">
				            <div class="sidebar-name">
				                <h3>'. __('Create Sidebar', 'sell') .'<span class="spinner"></span></h3>
				            </div>
				        <div class="sidebar-description">
				        <p style="text-align:center">You can add unlimited widget areas for display it on pages through Visual Composer. </p>
					    <form id="addWidgetAreaForm" action="" method="post">
				            <div class="widget-content">
				                <input id="sl-add-widget-input" name="sl-add-widget-input" type="text" class="regular-text" title="'. __('Name', 'sell') .'" placeholder="'. __('Name', 'sell') .'" />
				            </div>
				            <div class="widget-control-actions">
				                <div class="aligncenter">
				                    <input class="addWidgetArea-button button-primary" type="submit" value="'. __('Create Sidebar', 'sell') .'" />
				                </div>
				                <br class="clear">
				            </div>
				        </form>
				        </div>
				    </div>
				</div>
</div>';

?>