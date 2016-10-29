<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../includes/vendor/autoload.php');

class ParsersTest extends TestCase{

	function testHeadersAreExtracted(){
		
		$code = "
			<?php
			
			#mdx:h autoload
			require('vendor/autoload.php');

			#mdx:h use
			use Namespace\ClassName;

		";

		$expected = [
			'autoload' => "require('vendor/autoload.php');",
			'use' => "use Namespace\ClassName;"
		];

		$result = mdx_parse_source_code($code);
		$this->assertEquals(json_encode($expected), json_encode($result['headers']));		

	}

	function testSnippetsAreExtracted(){
		$code = "
			<?php
			
			#mdx:snippet1
			function test(){
				echo 'blah';
			}
			#/mdx

		";

		$snippet1 = 'function test(){'."\n";
		$snippet1.= "	echo 'blah';"."\n";
		$snippet1.= '}';

		$expected = ['snippet1'=>$snippet1];

		$result = mdx_parse_source_code($code);

		$this->assertEquals(json_encode($expected), json_encode($result['snippets']));

	}

	function testExceptionWhenHeaderNameIsMissing(){
		$this->expectException(\Exception::class);
		$code = "
			<?php

			#mdx:h
			require('vendor/autoload.php');

		";

		mdx_parse_source_code($code);

	}

	function testExceptionWhenSnippetNameIsMissing(){
		$this->expectException(\Exception::class);
		$code = "
			<?php
			
			#mdx:
			function test(){
				echo 'blah';
			}
			#/mdx

		";
		mdx_parse_source_code($code);
	}

	function testExceptionWhenSnippetNotClosed(){
		$this->expectException(\Exception::class);

		$code = "
			<?php
			
			#mdx:test
			function test(){
				echo 'blah';
			}

		";

		mdx_parse_source_code($code);

	}


}