<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$route['api/docs'] = 'api_docs/index';
$route['api/docs/openapi.json'] = 'api_docs/openapi';
$route['api/v2'] = 'api_v2/index';
$route['api/v2/(.+)'] = 'api_v2/index';
