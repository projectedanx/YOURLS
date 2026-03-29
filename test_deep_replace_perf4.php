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
$subject = ['test%%0d0dtest', 'another%%0d%0Atest'];

$r1 = yourls_deep_replace_old($search, $subject);
var_dump($r1);
$r2 = yourls_deep_replace_new($search, $subject);
var_dump($r2);
