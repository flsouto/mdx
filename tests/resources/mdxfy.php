<?php

class Test{

    function testSomething(){

        do_something();

        $this->assert();

    }

    function testAnother(){

        skipped();

    }

    function testLast(){

        do_last();

        $this->assert();

    }
}