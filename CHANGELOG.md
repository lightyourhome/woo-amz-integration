# Change Log
All notable theme changes should be added to this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [ 0.3.0 ] - 7.6.2020 - Most recent stable version

## Added

- Database creation on plugin activation
- Custom SQL table creation, custom SQL columns for plugin feed download progress
- URL query string for triggering feed creation via CRON (trigger script)
- URL query string for triggering feed restart if it doesn't complete (process script)
- Feed restart callback function (checks if feed is finished, if not continue where it left off)

## [ 0.3.0 ] - 7.3.2020

## Added 

- Progress Tracker and SSE integration ( checks progress of product data download )
- WP REST API ( used for MWS calls to amazon and tracking product data download progress )
- Options.php to keep track of saved options in plugin settings
- Plugin settings menu
- WP Admin navigation menu
- Ability to start product feed creation from WP Admin

## [ 0.2.0 ]

## Added

- REST API file and Class Woo_REST_API to handle api calls
- WooCommerce REST API Keys
- WooCommerce REST API integration (gets data from woocommerce installation such as stock and pricing)
- Product objects containing each product's sku, pricing, and stock in accordance with Amazon
- PHP composer dependency manager (required for woocommerce rest api integration)
- HTTP handler for WooCommerce REST API (allows internal and external communication from website)
