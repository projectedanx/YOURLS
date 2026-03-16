<?php
$content = file_get_contents('includes/functions-formatting.php');
$search = <<<EOT
function yourls_deep_replace(\$search, \$subject ){
    \$found = true;
    while(\$found) {
        \$found = false;
        foreach( (array) \$search as \$val ) {
            while( strpos( \$subject, \$val ) !== false ) {
                \$found = true;
                \$subject = str_replace( \$val, '', \$subject );
            }
        }
    }

    return \$subject;
}
EOT;

$replace = <<<EOT
function yourls_deep_replace(\$search, \$subject ){
    \$subject = (string) \$subject;

    \$count = 1;
    while ( \$count ) {
        \$subject = str_replace( \$search, '', \$subject, \$count );
    }

    return \$subject;
}
EOT;

$new_content = str_replace($search, $replace, $content);
file_put_contents('includes/functions-formatting.php', $new_content);
if ($content === $new_content) echo "Failed to replace\n";
else echo "Replaced successfully\n";
