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
			
							tfsWooAmzAjax( feedEnabled, false, true, false );
	
							//$('#send_inventory').hide();
	
							$('#feed-progress').show();
	
							$('#feed-progress').html('<p>Current Progress: <strong>Loading...</strong></p>');
	
							$('#feed-status').show();
							$('#feed-status-text').html('<p>Current Status: <strong>Starting</strong></p>');
	
							$('#download_inventory').hide();
	
							$('#feed-warning').show();

							$('#feed_submit').hide();
							$('#feed_continue').hide();
	
						} else {
		
							alert('Enable Feed and click "Save Settings" before running!');
		
						}
			
					});

					if ( $( '#feed_continue' ) ) {

						$( '#feed_continue' ).click(function() {

							if ( feedEnabled ) {
	
								tfsWooAmzAjax( feedEnabled, true, false, false );
	
								//$('#send_inventory').hide();
		
								$('#feed-progress').show();
		
								$('#feed-progress').html('<p>Current Progress: <strong>Loading...</strong></p>');
		
								$('#feed-status').show();
								$('#feed-status-text').html('<p>Current Status: <strong>Starting</strong></p>');
		
								$('#download_inventory').hide();
		
								$('#feed-warning').show();
								
								$('#feed_continue').hide();
								$('#feed_submit').hide();
	
							}
	
						});
	
					}

					if ( $( '#send_inventory' ).length ) {

						$( '#send_inventory' ).click(function() {

							if ( feedEnabled ) {

								tfsWooAmzAjax( feedEnabled, false, false, true );

								$('#feed-warning-amz').show();

							}

						});

					}

				}

			}

		});

	}


	tfsInitAdminPage();


	function tfsWooAmzAjax( value, runFeed, restartFeed, submitFeed, timer = null ) {

		console.log('okay');

		(function worker() {

			$('#download_inventory').hide();
			$('#feed_submit').hide();

			$.ajax({

				method: 'GET',
				url: tfs_woo_amz_int_object.api_url + 'woo-amz-feed/',
				data: {
				  enabled: value,
				  run: runFeed,
				  restart: restartFeed,
				  sendFeed: submitFeed
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

	
					//try to see if parsed_data.completed is available yet, if not, the feed is new so start it
					try {

						if ( submitFeed == false && parsed_data['status'].completed == 0 ) {
		
							$('#feed-progress').html('<p>Current Progress: '+ parsed_data['status'].products_processed + ' / ' + parsed_data['status'].products_to_process + '</p>');
		
							$('#feed-status-text').html('<p>Current Status: <strong>Running...</strong></p>');

							console.log( parsed_data );

							/**
							 * If the feed was restarted, change the data property values so that
							 * the feed will continue on the next REST API call and not restart again
							 */
							if ( parsed_data.continue_after_reset == true ) {

								runFeed = true;
								restartFeed = false;

								console.log('hit');

							}
							
							timer = setTimeout(worker, 5000);
		
						} else if ( submitFeed == false && parsed_data['status'].completed == 1 ) {
				
							$('#feed-progress').hide();
		
							$('#feed-status-text').html('<p>Current Status: <strong>Completed!</strong></p>');
		
							$('#ajax-loader').hide();

							$('#send_inventory').show();

							$('#feed-warning').hide();
		
							clearTimeout(timer);

							$('#feed_submit').show();

							$('#download_inventory').show();
								
						} else if ( submitFeed == true ) {

							console.log(parsed_data);

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
