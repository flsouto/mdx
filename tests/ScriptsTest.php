<?php

require_once(__DIR__.'/../includes/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class ScriptsTest extends TestCase{

	function testProcessScript(){
		$script = __DIR__.'/../process.php';
		$argv = [
			0 => $script,
			1 => __DIR__.'/resources/template1.php',
			2 => __DIR__.'/resources/source1.php'
		];
		ob_start();
		require(__DIR__.'/../process.php');
		$contents = ob_get_clean();
		$this->assertEquals(file_get_contents(__DIR__.'/resources/output1.md'), $contents);
	}

}