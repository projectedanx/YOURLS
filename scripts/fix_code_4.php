<?php
// Fix yourls-infos.php date() loop optimization
$content = file_get_contents('yourls-infos.php');
$search = <<<'CODE'
    $now          = time();
    $current_hour = (int)date('G', $now + ($offset * 3600));
    for ($i = 23; $i >= 0; $i--) {
        $hour = ($current_hour - $i + 24) % 24;
        $h    = sprintf('%02d %s', $hour, $hour < 12 ? 'AM' : 'PM');
        // If the $last_24h doesn't have all the hours, insert missing hours with value 0
        $last_24h[ $h ] = $_last_24h[ $h ] ?? 0;
    }
CODE;
$replace = <<<'CODE'
    $now          = time();
    $current_hour = (int)date('G', $now + ($offset * 3600));
    for ($i = 23; $i >= 0; $i--) {
        $hour = ($current_hour - $i + 24) % 24;
        $h    = sprintf('%02d %s', $hour, $hour < 12 ? 'AM' : 'PM');
        // If the $last_24h doesn't have all the hours, insert missing hours with value 0
        $last_24h[ $h ] = $_last_24h[ $h ] ?? 0;
    }
CODE;
$content = str_replace($search, $replace, $content);
file_put_contents('yourls-infos.php', $content);

echo "Modifications 4 completed.";
