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
    $count = 1;
    while ( $count ) {
        $subject = str_replace( $search, '', $subject, $count );
    }
    return $subject;
}

$search = ['%0d', '%0a', '%0D', '%0A'];
$subject = 'test%%0d0dtest';

echo "Old: " . yourls_deep_replace_old($search, $subject) . "\n";
echo "New: " . yourls_deep_replace_new($search, $subject) . "\n";
