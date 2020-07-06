(function( $ ) {

	'use strict';

	function tfsInitAdminPage() {

		$(document).ready(function() {

			$('#feed-progress').hide();

			if ( $('#tfs_start_amz_data_feed') ) {

				let feedEnabled = document.getElementById('feed_enabled').checked;
	
				$( '#feed_submit' ).click(function() {
	
					if ( feedEnabled ) {
	
						tfsWooAmzAjax( feedEnabled );

						$('#feed-progress').show();
	
					} else {

						alert('Enable Feed and Save before running!');

					}
		
				});
	
			}

		});

	}

	tfsInitAdminPage();

	// function tfsWooAmzAjax( value ) {

	// 	console.log('okay');

	// 		$.ajax({

	// 			method: 'GET',
	// 			url: tfs_woo_amz_int_object.api_url + 'woo-amz-feed/',
	// 			data: {
	// 			  enabled: value
	// 			},
	// 			success: function( data ) {

	// 				tfsCheckFeed( true );
		
	// 				console.log('success: ' + data);
	
	// 			},
	// 			complete: function( response ) {
		
	// 				console.log('complete: ' + response);
	
	// 			},
	// 			fail: function( response ) {
	
	// 				console.log('fail: ' + response);
	
	// 			},
	// 			error: function ( response ) {
	
	// 				console.log('error: ' + response);
	
	// 			}
	
	// 		});
		
	// }

	// function tfsCheckFeed( value ) {

	// 	(function worker() {

	// 		$.ajax({

	// 			method: 'GET',
	// 			url: tfs_woo_amz_int_object.api_url + 'woo-amz-feed/',
	// 			data: {
	// 			  check: value
	// 			},
	// 			success: function( data ) {
	
	// 				$('#feed-progress').html('<p>Current Progress: </p>' + data);
	
	// 				console.log('success: ' + data);
	
	// 			},
	// 			complete: function( response ) {
	
	// 				setTimeout(worker, 2000);
	
	// 				console.log('complete: ' + response);
	
	// 			},
	// 			fail: function( response ) {
	
	// 				console.log('fail: ' + response);
	
	// 			},
	// 			error: function ( response ) {
	
	// 				console.log('error: ' + response);
	
	// 			}
	
	// 		});
	
	// 	})();

	// }

})( jQuery );
