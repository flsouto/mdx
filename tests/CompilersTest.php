<?php

use PHPUnit\Framework\TestCase;
require_once(__DIR__.'/../includes/vendor/autoload.php');

class CompilersTest extends TestCase{

	function testSnippetReplacement(){
		
		$template = "Take a look at this code:\n";
		$template.= "#mdx:snip1";

		$code = "
			<?php
			#mdx:h autoload
			require_once('vendor/autoload.php');

			#mdx:snip1
			function test(){
				return 'blah';
			}
			#/mdx echo test()
		";

		$expected = <<<EXPECTED
Take a look at this code:
```php
<?php
require_once('vendor/autoload.php');

function test(){
	return 'blah';
}

echo test();
```
EXPECTED;
		$result = mdx_compile($template, $code);
		$this->assertEquals($expected, $result);
	}

	function testSnippetReplacementWithoutHeader(){
		$template = "Take a look at this code:\n";
		$template.= "#mdx:snip1 -h:autoload";

		$code = "
			<?php
			#mdx:h autoload
			require_once('vendor/autoload.php');

			#mdx:snip1
			function test(){
				return 'blah';
			}
			#/mdx echo test()
		";

		$expected = <<<EXPECTED
Take a look at this code:
```php
<?php

function test(){
	return 'blah';
}

echo test();
```
EXPECTED;
		$result = mdx_compile($template, $code);
		$this->assertEquals($expected, $result);
	}

	function testSnippetReplacementWithoutPhpTag(){
		$template = "Take a look at this code:\n";
		$template.= "#mdx:snip1 -h:autoload -php";

		$code = "
			<?php
			#mdx:h autoload
			require_once('vendor/autoload.php');

			#mdx:snip1
			function test(){
				return 'blah';
			}
			#/mdx echo test()
		";

		$expected = <<<EXPECTED
Take a look at this code:
```php

function test(){
	return 'blah';
}

echo test();
```
EXPECTED;
		$result = mdx_compile($template, $code);
		$this->assertEquals($expected, $result);
	}

	function testOutputReplacement(){
		$code = "
			#mdx:test
			function test(){
				return 'Works!';
			}
			#/mdx echo test();
		";
		$template = "#mdx:test -o";
		$result = mdx_compile($template, $code);
		$this->assertEquals('Works!',$result);
	}

	function testExceptionSnippetNotFound(){
		$this->expectException(\Exception::class);
		$template = '#mdx:test';
		$code = '';
		mdx_compile($template, $code);
	}

	function testExceptionOutputNotFound(){
		$template = "#mdx:test -o";
		$code = "
			#mdx:test
			function test(){}
			#/mdx		
		";
		$msg = '';
		try{
			mdx_compile($template,$code);
		} catch(\Exception $e){
			$msg = $e->getMessage();
		}
		$this->assertContains('output',$msg);
	}

}