(function( $ ) {

	'use strict';

	function tfsInitAdminPage() {

		$(document).ready(function() {

			if ( $('#woo-amz-admin-settings-form').length ) {

				$('#feed-progress').hide();
				$('#feed-status').hide();
				$('#feed-warning').hide();
				$('#download_inventory').show();
	
				if ( $('#tfs_start_amz_data_feed') ) {
	
					let feedEnabled = document.getElementById('feed_enabled').checked;
		
					$( '#feed_submit' ).click(function() {
		
						if ( feedEnabled ) {
			
							tfsWooAmzAjax( feedEnabled, false, true );
	
							$('#send_inventory').hide();
	
							$('#feed-progress').show();
	
							$('#feed-progress').html('<p>Current Progress: <strong>Loading...</strong></p>');
	
							$('#feed-status').show();
							$('#feed-status-text').html('<p>Current Status: <strong>Starting</strong></p>');
	
							$('#download_inventory').hide();
	
							$('#feed-warning').show();
	
			
						} else {
		
							alert('Enable Feed and click "Save Settings" before running!');
		
						}
			
					});

					$( '#feed_continue' ).click(function() {

						if ( feedEnabled ) {

							tfsWooAmzAjax( feedEnabled, true, false );

							$('#send_inventory').hide();
	
							$('#feed-progress').show();
	
							$('#feed-progress').html('<p>Current Progress: <strong>Loading...</strong></p>');
	
							$('#feed-status').show();
							$('#feed-status-text').html('<p>Current Status: <strong>Starting</strong></p>');
	
							$('#download_inventory').hide();
	
							$('#feed-warning').show();

						}

					});
	
				}

			}

		});

	}


	tfsInitAdminPage();


	function tfsWooAmzAjax( value, continueFeed, restartFeed, timer = null ) {

		console.log('okay');

		(function worker() {

			$('#download_inventory').hide();
			$('#feed_submit').hide();

			$.ajax({

				method: 'GET',
				url: tfs_woo_amz_int_object.api_url + 'woo-amz-feed/',
				data: {
				  enabled: value,
				  continue: continueFeed,
				  restart: restartFeed
				},
				beforeSend: function ( xhr ) {

					xhr.setRequestHeader( 'X-WP-Nonce', tfs_woo_amz_int_object.api_nonce );

				},	  
				success: function( response ) {

					//$('#feed-progress').html('<p>Current Progress: Loading...</p>');
		
					//console.log('success: ' + JSON.parse( response.responseJSON ) );
	
				},
				complete: function( data ) {

					let parsed_data = JSON.parse( data.responseJSON );
	
					console.log('complete: ' + parsed_data );

					//try to see if parsed_data.completed is available yet, if not, the feed is new so start it
					try {

						if ( parsed_data.completed == 0 ) {
		
							$('#feed-progress').html('<p>Current Progress: '+ parsed_data.products_processed + ' / ' + parsed_data.products_to_process + '</p>');
		
							$('#feed-status-text').html('<p>Current Status: <strong>Running...</strong></p>');
							
							timer = setTimeout(worker, 5000);
		
						} else if ( parsed_data.completed == 1 ) {
				
							$('#feed-progress').hide();
		
							$('#feed-status-text').html('<p>Current Status: <strong>Completed!</strong></p>');
		
							$('#ajax-loader').hide();

							$('#send_inventory').show();

							$('#feed-warning').hide();
		
							clearTimeout(timer);

							$('#feed_submit').show();

							$('#download_inventory').show();
								
						}

					} catch {
			
						$('#feed-status-text').html('<p>Current Status: <strong>Running...</strong></p>');
	
						timer = setTimeout(worker, 5000);

					}
	
				},
				fail: function( data ) {
	
					console.log('fail: ' + JSON.parse(data.responseJSON) );
	
				},
				error: function ( data ) {
	
					console.log('error: ' + JSON.parse(data.responseJSON) );
	
				}
	
			});
	
		})();
		
	}

})( jQuery );
