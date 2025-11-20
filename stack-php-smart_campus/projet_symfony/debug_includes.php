<?php
require __DIR__ . '/vendor/autoload.php';
$files = array_filter(get_included_files(), function($f){ return strpos($f, 'Kernel.php') !== false; });
var_dump($files);
