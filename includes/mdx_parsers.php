<?php

function mdx_parse_source_code($source_code){

	$lines = preg_split("/\r\n|\n/",$source_code);
	
	$headers = [];
	$snippets = [];
	$outputs = [];

	$context = 'scan'; // header|snippet|scan
	$header_id = '';
	$snippet_id = '';
	$snippet = [];
	$indent_level = 0;

	foreach($lines as $i => $line){

		if($context=='scan'){
			// Looking for headers/snippets
			$line_trimmed = trim($line);
			if(mb_substr($line_trimmed,0,5)=='#mdx:'){
				if($line_trimmed=='#mdx:h'){
					throw new Exception("Could not parse mdx source code: header name is empty at line $i.");
				}
				if(mb_substr($line_trimmed,0,7)=='#mdx:h '){
					$header_id = trim(mb_substr($line_trimmed, 6));
					$context = 'header';
				} else {
					$indent_level = mdx_parse_indent_level($line);
					$snippet_id = trim(mb_substr($line_trimmed,5));
					if(empty($snippet_id)){
						throw new Exception("Could not parse mdx source code: snippet name is empty at line $i");
					}
					$context = 'snippet';
				}
			}
		} else if($context=='header') {
			// Copy this line as header if not empty
			$line_trimmed = trim($line);
			if(!empty($line_trimmed)){
				$headers[$header_id] = $line_trimmed;
				$context = 'scan';				
			}
		} else if($context=='snippet'){
			$line_trimmed = trim($line);
			// match closing snippet
			if(mb_substr($line_trimmed,0,5)=='#/mdx'){
				$snippets[$snippet_id] = implode("\n",$snippet);
				$output = trim(mb_substr($line_trimmed,5));
				if($output){
					$outputs[$snippet_id] = $output;
				}
				$context = 'scan';
			} else {
			// increment snippet
				$snippet []= mdx_indent_less($line, $indent_level);
			}
		}
	
	}

	if($context=='header'){
		throw new Exception("Could not parse mdx source code: nothing matched after header '$name'");
	}

	if($context=='snippet'){
		throw new Exception("Error parsing mdx source code: no closing delimiter for snippet '$name'");
	}

	return [
		'headers' => $headers,
		'snippets' => $snippets,
		'outputs' => $outputs
	];

}

function mdx_parse_indent_level($line){
	$level = 0;
	for($i=0;$i<mb_strlen($line);$i++){
		$char = mb_substr($line,$i,1);
		if($char==" "||$char=="\t"){
			$level++;
		} else {
			break;
		}
	}
	return $level;
}

function mdx_parse_indent_str($line){
	$str = "";
	for($i=0;$i<mb_strlen($line);$i++){
		$char = mb_substr($line,$i,1);
		if($char==" "||$char=="\t"){
			$str .= $char;
		} else {
			break;
		}
	}
	return $str;
}


function mdx_indent_less($line, $level){

	for($i=0;$i<$level;$i++){
		$char = mb_substr($line,$i,1);
		if($char==" "||$char=="\t"){
			continue;
		} else {
			break;
		}
	}

	return mb_substr($line, $i);
}
