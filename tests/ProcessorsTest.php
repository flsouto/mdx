<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../includes/vendor/autoload.php');

class ProcessorsTest extends TestCase{

	function testSnippetCallIsMatched(){
		$template = "
			See example:
			#mdx:test
		";

		$match = [];
		mdx_process_template($template,function($m) use(&$match){
			$match = $m;
		});

		$this->assertEquals('test', $match['snippet_name']);
	}

	function testLessHeadersAreExtracted(){
		$template = "#mdx:test -h:require,use";
		$match = [];
		mdx_process_template($template, function($m) use(&$match){
			$match = $m;
		});
		$expected = json_encode(['require','use']);
		$this->assertEquals($expected,json_encode($match['-h']));
	}

	function testLessPhpIsMatched(){
		$template = "#mdx:test -php";
		$match = [];
		mdx_process_template($template, function($m) use(&$match){
			$match = $m;
		});
		$this->assertTrue($match['-php']);
	}

	function testOutputCallIsMatched(){
		$template = "#mdx:test -o";
		$match = [];
		mdx_process_template($template, function($m) use(&$match){
			$match = $m;
		});
		$this->assertTrue($match['-o']);
	}

	function testLinesAreModified(){
		$template = "
			Line 1
			#mdx:test -h:use,req
			Line 3
			#mdx:test2 -o
			END
		";
		$output = mdx_process_template($template,function($m){
			return mdx_parse_indent_str($m['line_content']).'Line '.$m['line_number'];
		});
		$expected = "
			Line 1
			Line 2
			Line 3
			Line 4
			END
		";
		$this->assertEquals($expected, $output);

	}



}