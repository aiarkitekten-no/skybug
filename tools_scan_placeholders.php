<?php
// SkyBug placeholder/dummy scanner – Fase 0 guardrail
$root = __DIR__;
$patterns = '/\b(dummy|placeholder|kommer snart|coming soon|fake data|lorem ipsum)\b/i';
$ignore = ['tools_scan_placeholders.php','phpcs.xml','phpstan.neon','fremgangsmate.txt','AI-learned/placeholder_scan_report.json','placeholder_scan_report.json','verify_fase0.json'];
$violations = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($rii as $file) {
    if ($file->isDir()) {
        continue;
    }
    $name = $file->getFilename();
    if (in_array($name,$ignore)) {
        continue;
    }
    $path = $file->getPathname();
    $contents = file_get_contents($path);
    if (preg_match_all($patterns,$contents,$matches,PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $violations[] = [
                'file' => str_replace($root.'/', '', $path),
                'match' => $m[0]
            ];
        }
    }
}
$reportFile = $root . '/AI-learned/placeholder_scan_report.json';
file_put_contents($reportFile, json_encode([
    'timestamp' => date('c'),
    'violations' => $violations,
    'count' => count($violations)
], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

if (php_sapi_name()==='cli') {
    if (empty($violations)) {
        echo "Ingen forbudte mønstre funnet." . PHP_EOL;
        exit(0);
    }
    echo "Fant ".count($violations)." brudd:\n";
    foreach ($violations as $v) {
        echo " - {$v['file']} :: {$v['match']}\n";
    }
    exit(1);
}
