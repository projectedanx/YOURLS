<?php
function yourls_deep_replace_old($search, $subject ){
    $found = true;
    while($found) {
        $found = false;
        foreach( (array) $search as $val ) {
            while( strpos( $subject, $val ) !== false ) {
                $found = true;
                $subject = str_replace( $val, '', $subject );
            }
        }
    }
    return $subject;
}

function yourls_deep_replace_new($search, $subject ){
    $subject = (string) $subject;

    $count = 1;
    while ( $count ) {
        $subject = str_replace( $search, '', $subject, $count );
    }

    return $subject;
}

$search = ['%0d', '%0a', '%0D', '%0A'];
$subject = 'https://example.com/some/normal/url/that/does/not/contain/any/of/the/search/strings/at/all';

$start = microtime(true);
for($i = 0; $i < 100000; $i++) {
    yourls_deep_replace_old($search, $subject);
}
$old_time = microtime(true) - $start;

$start = microtime(true);
for($i = 0; $i < 100000; $i++) {
    yourls_deep_replace_new($search, $subject);
}
$new_time = microtime(true) - $start;

echo "Old time: $old_time\n";
echo "New time: $new_time\n";
