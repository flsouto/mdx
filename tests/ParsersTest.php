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
		$this->assertEquals($expected, $result['headers']);		

	}

	function testHeaderOptionsAreExtracted(){
        $code = "
			<?php
			
			#mdx:h autoload hidden
			require('vendor/autoload.php');

		";

        $result = mdx_parse_source_code($code);
        $this->assertTrue($result['headers_options']['autoload']['hidden']);
    }

    function testExceptionThrownOnInvalidHeaderOption(){
        $code = "
			<?php
			
			#mdx:h autoload invalid
			require('vendor/autoload.php');

		";

        $this->expectException(\Exception::class);

        $result = mdx_parse_source_code($code);

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

		$this->assertEquals($expected, $result['snippets']);

	}

	function testSnippetsAreNotExtractedFromCommentBlock(){
		$code = "
			<?php
			/*
			#mdx:snippet1
			function test(){
				echo 'blah';
			}
			#/mdx
			*/
		";

		$result = mdx_parse_source_code($code);
		$this->assertEmpty($result['snippets']);

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

	function testOutputsMatch(){
		$code = "
			<?php
			
			#mdx:test
			function test(){
				echo 'blah';
			}
			#/mdx print_r(\$output1);


			#mdx:test2
			function test2(){
				echo 'blah2';
			}
			#/mdx print_r(\$output2)

		";

		$result = mdx_parse_source_code($code);
		$expected = ['test'=>'print_r($output1);','test2'=>'print_r($output2);'];

		$this->assertEquals($expected, $result['outputs']);

	}

    function testUncommentOutput(){
        $code = '
			#mdx:test
			$var = "this is a test";
			if($var){
			    #mdx:o echo $var
			}
			#/mdx
		';
        $result = mdx_parse_source_code($code);
        $expected = <<<EXPECTED
\$var = "this is a test";
if(\$var){
    echo \$var;
}
EXPECTED;
        $this->assertContains($expected, $result['snippets']['test']);
    }

	function testRemoveExtraEmptyLines(){
	    $lines = [
	        '',
            '',
	        'line1',
            '',
            '',
            'line2',
            ''
        ];
        $lines = mdx_remove_extra_empty_lines($lines);
        $this->assertEquals(['line1','','line2'],$lines);
    }

    function testSkipLine(){
        $code = '
			#mdx:test
			$var = "this is a test";
			$array = []; #mdx:skip
			if($var){
			    #mdx:o echo $var
			}
			#/mdx
		';
        $result = mdx_parse_source_code($code);
        $expected = <<<EXPECTED
\$var = "this is a test";
if(\$var){
    echo \$var;
}
EXPECTED;
        $this->assertContains($expected, $result['snippets']['test']);
    }

}