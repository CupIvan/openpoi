<?php

if (empty($_SERVER['argv'][1])) die('Usage: php '.basename($_SERVER['argv'][0]).' PATH_TO_PARSER_FILE');

set_include_path(get_include_path().PATH_SEPARATOR.'./m/');

spl_autoload_register(function($x){
	if ($x == 'Parser') $x .= '.class';
	else $x  = str_replace('Parser', '.parser', $x);
	$x .= ".php";
	require_once $x;
});

require_once 'mysql.class.php';
require_once 'config.php';

$parser = $_SERVER['argv'][1];
require_once $parser;

foreach (get_declared_classes() as $className)
if (strpos($className, 'Parser') > 1)
if (in_array('start', get_class_methods($className)))
{
	echo "START: $className\n";
	$parser = new $className();
	$parser->start();
	print_r($parser->stat());
	exit;
}
