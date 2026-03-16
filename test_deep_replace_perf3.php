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
$subject = 'test%%0d0dtest';

$r1 = yourls_deep_replace_old($search, $subject);
$r2 = yourls_deep_replace_new($search, $subject);
if ($r1 !== $r2) echo "Mismatch! $r1 vs $r2\n";
else echo "Match: $r1\n";

$search2 = ['a', 'b'];
$subject2 = 'aba';
$r1 = yourls_deep_replace_old($search2, $subject2);
$r2 = yourls_deep_replace_new($search2, $subject2);
if ($r1 !== $r2) echo "Mismatch! $r1 vs $r2\n";
else echo "Match: $r1\n";

$search3 = ['ab', 'cd'];
$subject3 = 'acbd';
$r1 = yourls_deep_replace_old($search3, $subject3);
$r2 = yourls_deep_replace_new($search3, $subject3);
if ($r1 !== $r2) echo "Mismatch! $r1 vs $r2\n";
else echo "Match: $r1\n";
