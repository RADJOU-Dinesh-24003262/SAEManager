#!/usr/bin/env php
<?php

// tests/coverage-checker.php

if ($argc < 3) {
    echo "Usage: php tests/coverage-checker.php <coverage.xml> <min-coverage>\n";
    exit(1);
}

$coverageFile = $argv[1];
$minCoverage = (float)$argv[2];

if (!file_exists($coverageFile)) {
    echo "❌ Coverage file '$coverageFile' not found.\n";
    exit(1);
}

$xml = simplexml_load_file($coverageFile);

if ($xml === false) {
    echo "❌ Unable to parse XML from '$coverageFile'.\n";
    exit(1);
}

$metrics = $xml->project->metrics;

if (!$metrics) {
    echo "❌ Invalid coverage.xml format. Metrics not found.\n";
    exit(1);
}

$totalStatements = (int)$metrics['statements'];
$coveredStatements = (int)$metrics['coveredstatements'];

if ($totalStatements === 0) {
    echo "❌ No statements found in coverage report.\n";
    exit(1);
}

$coverage = ($coveredStatements / $totalStatements) * 100;
$coverageFormatted = number_format($coverage, 2);

echo "🔍 Test coverage: {$coverageFormatted}% (Minimum required: {$minCoverage}%)\n";

if ($coverage < $minCoverage) {
    echo "❌ Coverage threshold not met.\n";
    exit(1);
}

echo "✅ Coverage threshold met.\n";
exit(0);
