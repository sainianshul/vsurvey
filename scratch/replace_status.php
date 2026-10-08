<?php
$directory = new RecursiveDirectoryIterator('c:/Users/anshu/Desktop/code/schotech/vcancares/resources/views/admin');
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

$count = 0;
foreach ($regex as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    if (strpos($content, 'All Statuses') !== false) {
        $content = str_replace('All Statuses', 'All Status', $content);
        file_put_contents($path, $content);
        echo "Updated: $path\n";
        $count++;
    }
}
echo "Total files updated: $count\n";
