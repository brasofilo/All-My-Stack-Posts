<?php

// Contains configuration parameters for the examples
// You will have to substitute your own values here.
global $plugin; 
require_once 'stackphp/api.php';
require_once 'stackphp/auth.php';
require_once 'stackphp/filestore_cache.php';

// Replace this data with your own if you want to test
// the authentication examples. They will NOT work
// until the keys below are replaced with your own.

// You will need both a server-side and client-side key
// in order to test all examples. Enter the appropriate
// values below.
if(defined('IMPLICIT'))
{
    API::$key = 'ABqBaKNdubSh1TTyhKC35w((';
    Auth::$client_id = 14;
}
else
{
    API::$key = 'h2Ao77BlzltDV4dovmOKtA((';
    Auth::$client_id = 0;
    Auth::$client_secret = '';
}

// Set the cache we will use
global $disable_cache;
if( !empty( $disable_cache ) )//!isset($_GET['no_api_cache'] ) )
	API::SetCache(new FilestoreCache($plugin->plugin_path.'cache'));