<?php
spl_autoload_register(function($className){
	$root = __DIR__;
    $file = $root.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.substr($className, strpos($className, '\\')+1).'.php';
	if(is_file($file)) require $file;
});