# How to use mb_substr

This is how you can extract the five first characters of a string:

```php
require_once('includes/vendor/autoload.php');

$name = "Fábio de Lima Souto";
$substr = mb_substr($name, 0, 5);

echo $substr;
```

The above code will output:

```
Fábio
```

# How to convert string to array

```php
<?php

$name = "Fabio de Lima Souto";
$result = str_split($name);

print_r($result);
```

Outputs:

```
Array
(
    [0] => F
    [1] => a
    [2] => b
    [3] => i
    [4] => o
    [5] =>  
    [6] => d
    [7] => e
    [8] =>  
    [9] => L
    [10] => i
    [11] => m
    [12] => a
    [13] =>  
    [14] => S
    [15] => o
    [16] => u
    [17] => t
    [18] => o
)

```