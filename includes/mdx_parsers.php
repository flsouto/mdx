<?php

function mdx_parse_source_code($source_code){

	$lines = preg_split("/\r\n|\n/",$source_code);
	
	$headers = [];
	$headers_options = [];
	$snippets = [];
	$outputs = [];

	$context = 'scan'; // header|snippet|comment|scan
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
					$header_str = trim(mb_substr($line_trimmed, 6));
					$header_parts = explode(' ',$header_str);
					$header_id = array_shift($header_parts);
					foreach($header_parts as $option){
					    if(empty($option)){
					        continue;
                        }
                        if($option=='hidden'){
					        $headers_options[$header_id] = [
					            'hidden' => true
                            ];
                        } else {
                            throw new Exception("Could not parse mdx source code: unrecognized option $option at $i.");
                        }
                    }
					$context = 'header';
				} else {
					$indent_level = mdx_parse_indent_level($line);
					$snippet_id = trim(mb_substr($line_trimmed,5));
					if(empty($snippet_id)){
						throw new Exception("Could not parse mdx source code: snippet name is empty at line $i");
					}
					$context = 'snippet';
				}
			} else if(mb_substr($line_trimmed,0,2)=='/*') {
				$context = 'comment';
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
					$output = rtrim($output,';').';';
					$outputs[$snippet_id] = $output;
				}
				$context = 'scan';
				$snippet = [];
			} else {
			// increment snippet
				$snippet []= mdx_indent_less($line, $indent_level);
			}
		} else if($context=='comment') {
			$line_trimmed = trim($line);
			// end of comment block
			if(mb_substr($line_trimmed,0,2)=='*/'){
				$context = 'scan';
			}
		}
	
	}

	if($context=='header'){
		throw new Exception("Could not parse mdx source code: nothing matched after header '$header_id'");
	}

	if($context=='snippet'){
		throw new Exception("Error parsing mdx source code: no closing delimiter for snippet '$snippet_id'");
	}

	return [
		'headers' => $headers,
        'headers_options' => $headers_options,
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

function mdx_remove_extra_empty_lines($content){
    if(is_array($content)){
        $type = 'array';
        $lines = array_values($content);
    } else {
        $type = 'string';
        $lines = explode("\n",$content);
    }
    $previous_was_empty = false;
    $last_line = count($lines)-1;
    foreach($lines as $i => $line){
        $line = trim($line);
        if(empty($line)){
            if($i<1 || $i==$last_line){
                unset($lines[$i]);
                $previous_was_empty = true;
            } else {
                if($previous_was_empty){
                    unset($lines[$i]);
                } else {
                    $previous_was_empty = true;
                }
            }
        } else {
            $previous_was_empty = false;
        }
    }
    if($type=='array'){
        return array_values($lines);
    } else {
        return implode("\n",$lines);
    }
}