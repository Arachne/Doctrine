#!/usr/bin/env php
<?php

$container = require __DIR__.'/_bootstrap.php';

// Run console application.
$container->getByType('Symfony\Component\Console\Application')->run();
