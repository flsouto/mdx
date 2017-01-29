# Markdown-X

## Overview

Markdown-X is a command line tool that allows you to extract functional fragments of your php source code and place them into markdown blocks. You can reference the output of an extracted snippet and tell Markdown X to place that output anywhere in your final markdown.

## Installation

You can either clone this repo or use composer:

```
composer require flsouto/mdx
```

*Notice*: nothing is added to your autoloader since this is an application, not a library. 

For convenience you may want to create an alias to the process script:

```
alias mdx='php /path/to/flsouto/mdx/process.php'
```

## Usage

Let's say you have written some library code along with a test:

```php
<?php

	// Contents of my_library.php
	function extractWords($input){
		return explode(' ', $input);
	}

	// Contents of tests.php
	require_once('my_library.php');
	function testCase1(){
		$string = "Input to be tested";
		$words = extractWords($string);
		assert(count($words)==4);
	}

```

Now you want to write some documentation for your library showing the usage of the extractWords function. If you are anything like me you don't like repeating yourself, and so you realize that the testing code ***already contains the snippet you want to demonstrate in the docs***. Here is where mdx comes in handy:

```php
	require_once('my_library.php');
	function testCase1(){
		#mdx:snippet1
		$string = "Input to be tested";
		$words = extractWords($string);
		#/mdx
		assert(count($words)==4);
	}

```

In the above code we have wrapped the fragment we want extracted and named it as "snippet1". In order to invoke that code in your markdown-x template you have to reference it like this:

```
Take a look at this code snippet:
#mdx:snippet1
```

As an example I have saved the above template in a file called README.mdx and run the following command in the terminal:

```
mdx README.mdx tests.php > README.md
```

The above produced a README.md file with the following contents:

		Take a look at this code snippet:
		```php
		<?php
		$string = "Input to be tested";
		$words = extractWords($string);		
		```

So, it extracted the snippet and placed in the markdown. Great. 


### Taking Output of a Snippet

This is super useful. Say you want to demonstrate not only the usage of your library but also what it is producing. You have to modify your source code and tell markdown-x what should be the output of a snippet:

```php
	require_once('my_library.php');
	function testCase1(){
		#mdx:snippet1
		$string = "Input to be tested";
		$words = extractWords($string);
		#/mdx print_r($words)
		assert(count($words)==4);
	}

```

Notice the line that says `#/mdx print_r($words)`. This is telling markdown-x that the output of the 'snippet1' snippet should be `print_r($words)`. Now, in your template, you can invoke that output with the ***-o*** option:

```
Take a look at this code snippet:
#mdx:snippet1

The output is:
#mdx:snippet1 -o
```

Let's run mdx:

```
mdx README.mdx tests.php > README.md
```

The contents of REAMDE.md will be (notice that the output command `print_r($words)` is now integrated into the snippet):

		Take a look at this code snippet:
		
		```php
		<?php
		$string = "Input to be tested";
		$words = extractWords($string);
		print_r($words)
		```
		The output is:
		```
		Array
		(
			[0] => Input
			[1] => to
			[2] => be
			[3] => tested
		)
		```
### Tidying html output

If the output of a snippet is in html format, you can use the `httidy` option next to `-o` in order to make the html look pretty (with indentation and such) in the output:

```
#mdx:snippet_that_produces_html -o httidy
```

Outputs:

	```html
		<div>
		  <span></span>
		  <div></div>
		</div>
	```
**Notice:** this option automatically sets the code type to 'html' so that the correct highlighter is used.

### Invoking headers

Sometimes, in order for a snippet to work, it needs additional includes and/or other stuff like constants defined at the top of a script or "use" statements. With markdown-x you can mark a line as a required header using the `#mdx:h` directive:

```php
#mdx:h autoload
require_once('vendor/autoload.php');

#mdx:h alias
use Some\Library as Alias;

// etc...
```

In the above example we have marked two headers: 'autoload' and 'alias'. Now, when you invoke a snippet from this file, these two lines will be included right after the opening php tag. Also, when the output from a snippet is invoked, these headers will be executed along with the extracted snippet, thus making everything work as expected.

*Notice:* the 'autoload' and 'alias' are arbitrary names I've chosen. You can choose any other names to mark your header lines.

### Keep headers from displaying in the docs

Even though necessary, some headers may become obvious and redundant to the readers at some point in the documentation, so you may want to suppress them when invoking a snippet:

```
Take a look at this code (I have ommited some top statements for sake of brevity)
#mdx:snippet1 -h:autoload,alias
```

If you wanted to skip only the autoload, but keep the alias:

```
#mdx:snippet1 -h:autoload
```

#### Marking headers as hidden by default

If you want a header statement to be always executed but never displayed, then you can use the "hidden" option, which should come after the name of the header like so:

```php
#mdx:h autoload hidden
require_once('vendor/autoload.php');
```

## Keep the php tag from display in the docs

In the same way you suppress headers, you can also suppress the php opening tag from appearing:

```
#mdx:snippet1 -php
```

The -php option can be mixed with the -h option as well:

```
#mdx:snippet1 -php -h:alias
```

The above would place the snippet in the markdown without the php opening tag and the alias header.

## Repeating previously used options

At some point in the documentation you will probably be using the same options again and again, like so:

```
See this example:
#mdx:snippet1 -php -h:alias,autoload

See another example:
#mdx:snippet2 -php -h:alias,autoload

See yet another example:
#mdx:snippet3 -php -h:alias,autoload

```

The good news is that you can use the special `idem` option to avoid such repetious work. This flag is going to replicate
the same options used in the last mdx command:

```
See this example:
#mdx:snippet1 -php -h:alias,autoload

See another example:
#mdx:snippet2 idem

See yet another example:
#mdx:snippet3 idem

```


## Final Thoughts

Want to see a working example used in production? Then take a look at this markdown-x template along with its resulting markdown file:

* [https://github.com/flsouto/pipe/blob/1.0.0/README.mdx](https://github.com/flsouto/pipe/blob/1.0.0/README.mdx)
* [https://github.com/flsouto/pipe/blob/1.0.0/README.md](https://github.com/flsouto/pipe/blob/1.0.0/README.md)

The steps required to make this conversion in case you want to see how it works in practice:

* Install mdx using the instructions at the install section of this documentation
* Download the example repository at [https://github.com/flsouto/pipe](https://github.com/flsouto/pipe)
* cd to the downloaded repository's root dir
* run mdx README.mdx tests/PipeTest.php

You should see the result of the conversion in your console.