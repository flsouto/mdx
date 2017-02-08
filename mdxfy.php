<?php

if(empty($argv[1])){
    die("Usage: command <TEST_FILE>\n");
}

$contents = file_get_contents($argv[1]);

$lines = [];
$i = 0;
$new_lines = [];
$context = '';
$snippet_name = '';

foreach(explode("\n", $contents) as $line){
    
    if(preg_match("/[\s\t]+function test(.*?)\(/",$line,$match)){

        if($context=='function'||$context=='snippet'){
            foreach($new_lines as $l){
                unset($lines[$l]);
            }
        }
        
        $context = 'function';

        $snippet_name = $match[1];
        $docs = [];
        $docs[] = '/*';
        $docs[] = '#mdx:'.$snippet_name;
        $docs[] = '';
        $docs[] = 'Outputs:';
        $docs[] = '';
        $docs[] = '#mdx:'.$snippet_name.' -o';
        $docs[] = '*/';

        $new_lines = [];
        
        foreach($docs as $docline){
            $lines[$i] = $docline;
            $new_lines[] = $i;
            $i++;
        }

        $lines[$i] = $line;
        $i++;

    } else if($context=='function' && trim($line)) {

        preg_match("/^([\s\t]+)/", $line, $match);

        $lines[$i] = $match[1].'#mdx:'.$snippet_name;
        $new_lines[] = $i;
        $context = 'snippet';
        $i++;
        $lines[$i] = $line;
        $i++;

    } else if($context=='snippet' && preg_match("/^([\s\t]+)".preg_quote('$this->')."/", $line, $match)){

        $context = '';

        if(empty($lines[$i-1])){
            $i -= 1;
        }

        $lines[$i] = $match[1].'#/mdx';
        $new_lines[] = $i;

        $i++;
        $lines[$i] = $line;
        $i++;

    } else {

        $lines[$i] = $line;
        $i++;

    }

    
}

echo implode("\n", $lines);