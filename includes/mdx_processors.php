<?php

// syntax: #mdx:snippet [-h:header1,...] [-php]
function mdx_process_template($template, $callback){
	
	$lines = preg_split("/\r\n|\n/",$template);
	$output = [];
    $last_options = [];

	foreach($lines as $i => $line){
		$line_number = $i + 1;
		$line_trimmed = trim($line);
		if(mb_substr($line_trimmed,0,5)=='#mdx:'){
			$command = trim(mb_substr($line_trimmed,5));
			$tokens = explode(' ', $command);
			if(empty($tokens[0])){
				throw new Exception("Snippet name missing at line $line_number");
			}
			if($tokens[0]=='h'){
				throw new Exception("Snippet name cannot be 'h' at line $line_number.");
			}
			$snippet = array_shift($tokens);
			$remove_headers = [];
			$no_php_tag = false;
			$out = false;
			$out_httidy = false;
			foreach($tokens as $token){
				if(mb_substr($token,0,3)=='-h:'){
					$remove_headers = explode(',',mb_substr($token,3));
				} else if($token=='-php'){
					$no_php_tag = true;
				} else if($token=='-o') {
                    $out = true;
                } else if($token=='httidy'){
                	if(!$out){
                		throw new Exception("The 'httidy' option can only be used after the -o option.");
                	}
                	$out_httidy = true;
                }
                else if(strtolower($token)=='idem') {
				    if(empty($last_options)){
				        throw new Exception("The 'idem' flag cannot be used at line $line_number because no previous options have been set.");
                    }
				    $remove_headers = $last_options['-h'];
				    $no_php_tag = $last_options['-php'];
				} else {
					throw new Exception("Unrecognized option '$token' at line $line_number");
				}
			}

			$callback_data = [
                'line_number' => $line_number,
                'line_content' => $line,
                'snippet_name' => $snippet,
                '-o' => $out,
                '-o.httidy' => $out_httidy
            ];

            if(!$out){
				$last_options = [
	                '-h' => $remove_headers,
	                '-php' => $no_php_tag
	            ];
	            $callback_data = array_merge($callback_data, $last_options);
            }

			$line = $callback($callback_data);

		}
		$output[] = $line;
	}

	return implode("\n",$output);

}

