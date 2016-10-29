<?php

// syntax: #mdx:snippet [-h:header1,...] [-php]
function mdx_process_template($template, $callback){
	
	$lines = preg_split("/\r\n|\n/",$template);
	$output = [];

	foreach($lines as $i => $line){
		$line_trimmed = trim($line);
		if(mb_substr($line_trimmed,0,5)=='#mdx:'){
			$command = trim(mb_substr($line_trimmed,5));
			$tokens = explode(' ', $command);
			if(empty($tokens[0])){
				throw new Exception("Snippet name missing at line $i");
			}
			if($tokens[0]=='h'){
				throw new Exception("Snippet name cannot be 'h' at line $i.");
			}
			$snippet = array_shift($tokens);
			$remove_headers = [];
			$no_php_tag = false;
			$out = false;
			foreach($tokens as $token){
				if(mb_substr($token,0,3)=='-h:'){
					$remove_headers = explode(',',mb_substr($token,3));
				} else if($token=='-php'){
					$no_php_tag = true;
				} else if($token=='-o') { 
					$out = true;
				} else {
					throw new Exception("Unrecognized option '$token' at line $i");
				}
			}
			$line = $callback([
				'line_number' => $i,
				'line_content' => $line,
				'snippet_name' => $snippet,
				'-h' => $remove_headers,
				'-php' => $no_php_tag,
				'-o' => $out
			]);
		}
		$output[] = $line;
	}

	return implode("\n",$output);

}

