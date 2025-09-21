<?php
// Enkel pakkescript: php tools_package_plugin.php
// Lager skybug-<versjon>.zip i samme katalog.

chdir(__DIR__);
$root = realpath(__DIR__);
$version = '1.0.0';
$zipName = 'skybug-' . $version . '.zip';
$exclude = ['vendor','node_modules','*.zip','*.log','qa_forbudstekst.json','placeholder_scan_report.json'];

$zip = new ZipArchive();
if(file_exists($zipName)) { unlink($zipName); }
if($zip->open($zipName, ZipArchive::CREATE)!==true){
    fwrite(STDERR, "Kunne ikke opprette arkiv\n");
    exit(1);
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach($rii as $file){
    $path = $file->getPathname();
    if($file->isDir()) { continue; }
    $rel = substr($path, strlen($root)+1);
    $skip = false;
    foreach($exclude as $pattern){
        if(fnmatch($pattern, basename($rel))) { $skip = true; break; }
    }
    if($skip) { continue; }
    if(preg_match('/^AI-learned\/(?:qa_forbudstekst|placeholder_scan_report)/',$rel)) { continue; }
    $zip->addFile($path, 'skybug/'.$rel);
}
$zip->close();
echo "Pakkefil generert: $zipName\n";
