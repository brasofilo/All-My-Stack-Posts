<?php
# http://bazaar.launchpad.net/~george-edison55/stackphp/trunk/view/head:/examples/src/config.php

global $plugin; 
if( !class_exists( 'API' )  ):
require_once 'stackphp/api.php';
require_once 'stackphp/auth.php';
require_once 'stackphp/filestore_cache.php';
endif;
 
API::$key = 'WfdrC3u7rmAQDwaSRYrw2w((';
Auth::$client_id = 1926;

# Set the cache we will use
global $disable_cache;
if( !empty( $disable_cache ) )//!isset($_GET['no_api_cache'] ) )
	API::SetCache( new FilestoreCache( $plugin->plugin_path.'cache' ) );

