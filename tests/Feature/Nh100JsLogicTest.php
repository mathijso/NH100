<?php

it('runs nh100 JS logic tests via Node', function () {
    $scriptPath = base_path('tests/nh100.test.js');
    expect(file_exists($scriptPath))->toBeTrue();

    $cmd = 'node ' . escapeshellarg($scriptPath) . ' 2>&1';
    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);

    // Provide helpful output on failure
    if ($exitCode !== 0) {
        fwrite(STDERR, "\nNode test output:\n" . implode("\n", $output) . "\n");
    }

    expect($exitCode)->toBe(0);
});


