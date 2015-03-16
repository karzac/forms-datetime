<?php

require __DIR__ . '/../vendor/autoload.php';

if (!class_exists('Tester\Assert')) {
	echo "Install Nette Tester using `composer update --dev`\n";
	exit(1);
}

Tester\Environment::setup();

$tmpDir = __DIR__ . '/tmp';
if (!file_exists($tmpDir)) mkdir($tmpDir);
define('TEMP_DIR', $tmpDir . '/' . (isset($_SERVER['argv']) ? md5(serialize($_SERVER['argv'])) : getmypid()));
Tester\Helpers::purge(TEMP_DIR);

function id($val)
{
	return $val;
}
