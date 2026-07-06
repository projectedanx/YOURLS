<?php
// Fix functions-options.php
$content = file_get_contents('includes/functions-options.php');
$search = <<<'CODE'
function yourls_maybe_unserialize( $original ) {
    if ( yourls_is_serialized( $original ) ) { // don't attempt to unserialize data that wasn't serialized going in
        return @unserialize( (string)$original, array( 'allowed_classes' => array( 'stdClass' ) ) );
    }
    return $original;
}
CODE;
$replace = <<<'CODE'
function yourls_maybe_unserialize( $original ) {
    if ( yourls_is_serialized( $original ) ) { // don't attempt to unserialize data that wasn't serialized going in
        $allowed_classes = array( 'stdClass' );
        if ( function_exists( 'yourls_apply_filter' ) ) {
            $allowed_classes = yourls_apply_filter( 'yourls_maybe_unserialize_allowed_classes', $allowed_classes );
        }
        return @unserialize( (string)$original, array( 'allowed_classes' => $allowed_classes ) );
    }
    return $original;
}
CODE;
$content = str_replace($search, $replace, $content);
file_put_contents('includes/functions-options.php', $content);

// Fix functions-html.php
$content = file_get_contents('includes/functions-html.php');
$search = <<<'CODE'
function yourls_html_head_output( $context, $title, $bodyclass, $components ) {
    extract($components);
CODE;
$replace = <<<'CODE'
function yourls_html_head_output( $context, $title, $bodyclass, $components ) {
    $share       = $components['share'] ?? false;
    $insert      = $components['insert'] ?? false;
    $tablesorter = $components['tablesorter'] ?? false;
    $tabs        = $components['tabs'] ?? false;
    $cal         = $components['cal'] ?? false;
    $charts      = $components['charts'] ?? false;
CODE;
$content = str_replace($search, $replace, $content);
file_put_contents('includes/functions-html.php', $content);

// Fix functions-infos.php max() in scale_data
$content = file_get_contents('includes/functions-infos.php');
$search = <<<'CODE'
function yourls_scale_data($data ) {
    $max = max( $data );
CODE;
$replace = <<<'CODE'
function yourls_scale_data($data ) {
    if ( empty( $data ) ) {
        return $data;
    }
    $max = max( $data );
CODE;
$content = str_replace($search, $replace, $content);

// Fix functions-infos.php max() in array_granularity
$search = <<<'CODE'
function yourls_array_granularity($array, $grain = 100, $preserve_max = true) {
    if ( count( $array ) > $grain ) {
        $max = max( $array );
CODE;
$replace = <<<'CODE'
function yourls_array_granularity($array, $grain = 100, $preserve_max = true) {
    if ( empty( $array ) ) {
        return $array;
    }
    if ( count( $array ) > $grain ) {
        $max = max( $array );
CODE;
$content = str_replace($search, $replace, $content);

file_put_contents('includes/functions-infos.php', $content);
echo "Modifications completed.";
