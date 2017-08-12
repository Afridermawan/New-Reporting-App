<?php

return [
	//setting display error
	'displayErrorDetails'	=> true,

	'addContentLengthHeader' => false,

	//setting timezone
	'timezone'	=> 'Asia/Jakarta',

	//setting language
	'lang'	=> [
		'default'	=> 'en',
	],

	//setting db (with doctrine)
	'db'	=> [
		'url'	=> 'mysql://root:apple@localhost/new-reporting',
	],

	'determineRouteBeforeAppMiddleware' => true,

	//setting language
	'lang'	=> [
		'default'	=> 'en',
	],

	// Setting View
	'view' => [
		'path'	=>	__DIR__ . '/../views',
		'twig'	=> 	[
			'cache'	=>	false,
			'debug' => true
		]
	],

	'reporting' => [
	   'base_uri' => 'http://localhost/Reporting-App/public/api/',
	   'headers' => [
		   'key' => $_ENV['REPORTING_API_KEY'],
		   'Accept' => 'application/json',
		   'Content-Type' => 'application/json',
		   'Authorization'	=>	@$_SESSION['key']['key_token'],
	   ],
   ],

    'base_url' => "http://localhost/",
    "plates_path" => "/../view",
    
    'flysystem' => [
    	'path'	=> __DIR__ . "/../public/assets",
     ]
];