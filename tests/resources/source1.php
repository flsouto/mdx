<?php

#mdx:h autoload
require_once('includes/vendor/autoload.php');

#mdx:s1
$name = "Fábio de Lima Souto";
$substr = mb_substr($name, 0, 5);
#/mdx echo $substr

#mdx:s2
$name = "Fabio de Lima Souto";
$result = str_split($name);
#/mdx print_r($result);