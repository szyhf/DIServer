<?php
//Redis配置
return [
	'default'    => [
		'Host'     => '127.0.0.1',
		'Port'     => 6373,
		'Timeout'  => 0,
		'Prefix'   => "",
		'Account'  => "",
		'Password' => ""
	],
	'NotDefault' => [
		'Host'    => '127.0.0.2',
		'Port'    => 6373,
		'Timeout' => 0,
		'Prefix'  => "",
	]
];