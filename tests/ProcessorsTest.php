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
		$template = "Line 1\n#mdx:test -h:use,req\nLine 3\n#mdx:test2 -o";
		$output = mdx_process_template($template,function($m){
			return 'Line '.$m['line_number'];
		});
		$expected = "Line 1\nLine 2\nLine 3\nLine 4";
		$this->assertEquals($expected, $output);
	}



}