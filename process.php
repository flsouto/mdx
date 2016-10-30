<?php

require_once(__DIR__.'/includes/vendor/autoload.php');

if(empty($argv[2])){
	die("Usage: command <template> <source> [source]...\n");
}

$template = $argv[1];

if(!file_exists($template)){
	die("Template file not found: $template\n");
}

$sources = array_slice($argv, 2);
$parsed = [];
foreach($sources as $src){
	if(!file_exists($src)){
		die("Source file not found: $src\n");
	}
	$contents = file_get_contents($src);
	try{
		$result = mdx_parse_source_code($contents);
		$parsed = array_merge_recursive($parsed, $result);
	} catch(\Exception $e) {
		die("Could not parse source file $src: ".$e->getMessage()."\n");
	}
}

echo mdx_compile(file_get_contents($template), $parsed);