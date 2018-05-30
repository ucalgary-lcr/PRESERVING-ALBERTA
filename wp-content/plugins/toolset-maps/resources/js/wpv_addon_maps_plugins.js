var WPViews = WPViews || {};

/**
 * Client-side behaviour on WP plugins page
 * @since 1.4.1
 * @param $ jQuery
 * @class
 */
WPViews.ViewAddonMapsPlugins = function( $ ) {
	var self = this;

	self.startEvents = function() {
		$('#js-wpv-map-api-key-form').on('submit', function( event ) {
			event.preventDefault();
			self.save_api_key_options();
		} );
	};

	/**
	 * Saves API key using same backend code path as key saving from Toolset -> Settings -> Maps.
	 *
	 * (Uses simpler feedback, as js-toolset-event-update-setting-section-* events are not available here.)
	 */
	self.save_api_key_options = function() {
		var api_key = $( '#js-wpv-map-api-key' ).val();
		var data = {
			action:		'wpv_addon_maps_update_api_key',
			api_key:	api_key,
			wpnonce:	wpv_addon_maps_plugins_local.global_nonce
		};

		$.ajax({
			type:		"POST",
			dataType:	"json",
			url:		ajaxurl,
			data:		data
		})
		.done(function( response ){
			if ( response.success ) {
				$('#js-wpv-map-api-key-form').closest('.notice-warning').hide('slow');
			}
		});
	};

	self.init = function() {
		self.startEvents();
	};

	// Start
	self.init();
};

jQuery( document ).ready( function( $ ) {
	WPViews.view_addon_maps_plugins = new WPViews.ViewAddonMapsPlugins( $ );
} );