<?php

function mdx_compile($template, $source){
	if(is_array($source)){
		// already parsed
		$sources = $source;
	} else {
		$sources = mdx_parse_source_code($source);
	}
	return mdx_process_template($template,function($match) use($sources){
		$line = $match['line_number'];
		$snippet = $match['snippet_name'];
		if(!isset($sources['snippets'][$snippet])){
			throw new Exception("No source code found for snippet '$snippet' - called at line $line.");
		}
		if(!empty($match['-o'])){
			$code = mdx_build_executable($sources, $snippet);
			ob_start();
			eval($code);
			$output = ob_get_clean();
            if(empty($output)){
                throw new Exception("No output was generated for snippet '$snippet' - called at line $line.");
            }
			$outtype = '';
			if(!empty($match['-o.httidy'])){
				require_once(__DIR__.'/htmLawed.php');
				$output = htmlawed($output,['tidy'=>'2s']);
				$outtype = 'html';
			}

			return "```$outtype\n".$output."\n```";
		} else {

		    foreach($sources['headers_options'] as $header_id => $options){
		        foreach($options as $option => $value){
		            // remove headers that are hidden by default
		            if($option=='hidden' && $value){
		                $match['-h'][] = $header_id;
                    }
                }
            }

			return mdx_build_display($sources, $snippet, $match['-h'], $match['-php']);
		}

	});
}

function mdx_build_executable($sources, $snippet){
	$code = '';
	foreach($sources['headers'] as $header){
		$code .= $header."\n";
	}
	$code .= $sources['snippets'][$snippet]."\n";
	if(!empty($sources['outputs'][$snippet])){
		$code .= $sources['outputs'][$snippet];
	}
	return $code;
}

function mdx_build_display($sources, $snippet, $skip_headers=[], $skip_php=false){
	$content = "```php\n";
	if(!$skip_php){
		$content.= "<?php\n";
	}
	foreach($sources['headers'] as $name => $header){
		if(!in_array($name,$skip_headers)){
			$content.= $header."\n";
		}
	}
	$content.= "\n";
	$content.= $sources['snippets'][$snippet]."\n\n";
	if(!empty($sources['outputs'][$snippet])){
		$content .= $sources['outputs'][$snippet];
	}
	$content.="\n```";
    $content = mdx_remove_extra_empty_lines($content);

	return $content;
}