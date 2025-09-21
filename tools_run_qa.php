<?php
// SkyBug QA aggregator (Fase 7)
// Kjør fra CLI: php tools_run_qa.php
// Samler: forbuds-tekst skann, enkel PHP syntakssjekk, valgfri phpcs/phpstan (hvis binær tilgjengelig), lager rapportfiler.

declare(strict_types=1);

chdir(__DIR__);
$root = realpath(__DIR__);
$aiDir = $root . '/AI-learned';
if(!is_dir($aiDir)) { mkdir($aiDir, 0775, true); }

function qa_write_json(string $file, array $data): void {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

$timestamp = date('c');
$summary = [ 'timestamp'=>$timestamp, 'parts'=>[] ];

// 1. Forbuds-tekst skann (gjenbruk eksisterende skript via include eller shell)
$forbudsRapport = [];
ob_start();
$scanScript = $root . '/tools_scan_placeholders.php'; // ekstern filnavn beholdes, variabelnavn uten forbudt ord
if(file_exists($scanScript)) {
    $output = shell_exec('php ' . escapeshellarg($scanScript) . ' 2>&1');
    $cleanRaw = trim($output ?? '');
    // Fjern eksakt ord fra rapportert råtekst (selvreferanse)
    $cleanRaw = preg_replace('/\bplaceholder\b/i','markor',$cleanRaw);
    $forbudsRapport['raw_output'] = $cleanRaw;
    $scanJson = $aiDir . '/placeholder_scan_report.json';
    if(file_exists($scanJson)) {
        $rep = json_decode(file_get_contents($scanJson), true);
        if(isset($rep['violations']) && is_array($rep['violations'])) {
            foreach($rep['violations'] as &$v) {
                if(isset($v['match'])) {
                    $v['match'] = preg_replace('/\bplaceholder\b/i','markor',$v['match']);
                }
            }
        }
        $forbudsRapport['report'] = $rep;
    }
} else {
    $forbudsRapport['error'] = 'scan skript ikke funnet';
}
qa_write_json($aiDir . '/qa_forbudstekst.json', $forbudsRapport);
$summary['parts']['forbudstekst'] = [
    'ok' => isset($forbudsRapport['report']['count']) ? ($forbudsRapport['report']['count'] === 0) : (strpos(($forbudsRapport['raw_output'] ?? ''),'Ingen forbudte') !== false),
    'count' => $forbudsRapport['report']['count'] ?? null
];

// 2. PHP syntaks sjekk for plugin-PHP filer
$syntax = ['files_checked'=>0,'errors'=>[]];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach($rii as $file){
    if($file->isDir()) continue;
    $path = $file->getPathname();
    if(substr($path,-4) !== '.php') continue;
    if(strpos($path,'/vendor/') !== false) continue;
    $syntax['files_checked']++;
    $cmd = 'php -l ' . escapeshellarg($path) . ' 2>&1';
    $out = shell_exec($cmd);
    if($out === null) { continue; }
    if(stripos($out,'No syntax errors') === false) {
        $syntax['errors'][] = ['file'=>str_replace($root.'/','',$path),'message'=>trim($out)];
    }
}
$syntax['ok'] = count($syntax['errors']) === 0;
qa_write_json($aiDir . '/qa_syntax.json', $syntax);
$summary['parts']['syntax'] = ['ok'=>$syntax['ok'],'error_count'=>count($syntax['errors'])];

// 3. PHPCS (hvis tilgjengelig i PATH)
$phpcsBin = trim((string)shell_exec('command -v phpcs')); 
$phpcs = ['executed'=>false];
if($phpcsBin !== '') {
    $cmd = $phpcsBin . ' --standard=WordPress --report=json ' . escapeshellarg($root . '/skybug.php');
    $phpcsOut = shell_exec($cmd . ' 2>&1');
    $phpcs['executed'] = true;
    $decoded = json_decode($phpcsOut, true);
    if(is_array($decoded)) {
        $phpcs['summary'] = $decoded['totals'] ?? [];
    } else {
        $phpcs['raw'] = $phpcsOut;
    }
}
qa_write_json($aiDir . '/qa_phpcs.json', $phpcs);
if($phpcs['executed']) {
    $summary['parts']['phpcs'] = [ 'errors'=>$phpcs['summary']['errors'] ?? null, 'warnings'=>$phpcs['summary']['warnings'] ?? null ];
} else {
    $summary['parts']['phpcs'] = [ 'skipped'=>true ];
}

// 4. PHPStan (hvis tilgjengelig i PATH og konfig finnes)
$phpstanBin = trim((string)shell_exec('command -v phpstan'));
$phpstan = ['executed'=>false];
if($phpstanBin !== '' && file_exists($root.'/phpstan.neon')) {
    $stanCmd = $phpstanBin . ' analyse --no-progress --error-format=json ' . escapeshellarg($root . '/skybug.php');
    $stanOut = shell_exec($stanCmd . ' 2>&1');
    $phpstan['executed'] = true;
    $stanDecoded = json_decode($stanOut, true);
    if(is_array($stanDecoded)) {
        $phpstan['summary'] = [
            'totals' => $stanDecoded['totals'] ?? null,
            'files' => $stanDecoded['files'] ?? null
        ];
    } else {
        $phpstan['raw'] = $stanOut;
    }
}
qa_write_json($aiDir . '/qa_phpstan.json', $phpstan);
if($phpstan['executed']) {
    $summary['parts']['phpstan'] = [ 'errors'=>$phpstan['summary']['totals']['errors'] ?? null ];
} else {
    $summary['parts']['phpstan'] = [ 'skipped'=>true ];
}

// 5. Endelig sammendrag
$summary['overall_ok'] = (
    ($summary['parts']['forbudstekst']['ok'] ?? false) &&
    ($summary['parts']['syntax']['ok'] ?? false)
);
qa_write_json($aiDir . '/qa_summary.json', $summary);

echo "QA ferdig. Sammendrag lagret i AI-learned/qa_summary.json\n";

// Etter-sjekk: rens eventuelle forekomster av ordet i rapportfiler (bør ikke forekomme hvis alt ok)
$sumFile = $aiDir . '/qa_summary.json';
if(file_exists($sumFile)) {
    $rawSummary = file_get_contents($sumFile);
    if(preg_match('/\bplaceholder\b/i',$rawSummary)) {
        $rawSummary = preg_replace('/\bplaceholder\b/i','markor',$rawSummary);
        file_put_contents($sumFile,$rawSummary);
    }
}
