<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->exclude(['.git', 'config', 'var', 'translations', 'vendor'])
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'yoda_style' => false,
    'concat_space' => ['spacing' => 'one'],
    'phpdoc_align' => ['align' => 'left'],
])
    ->setFinder($finder);