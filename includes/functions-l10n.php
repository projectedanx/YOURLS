<?php
/**
 * YOURLS Translation API
 *
 * This file is a modified subset of WordPress's Translation API. It is
 * responsible for handling language localization and providing functions for
 * translating text.
 *
 * @package YOURLS
 * @since 1.6
 */

/**
 * Load POMO files required to run library
 */
use \POMO\MO;
use POMO\Translations\NOOPTranslations;

/**
 * Gets the current locale.
 *
 * @since 1.6
 * @return string The locale of the YOURLS installation (e.g., 'en_US').
 */
function yourls_get_locale() {
    global $yourls_locale;

    if ( !isset( $yourls_locale ) ) {
        // YOURLS_LANG is defined in config.
        if ( defined( 'YOURLS_LANG' ) )
            $yourls_locale = YOURLS_LANG;
    }

    if ( !$yourls_locale )
        $yourls_locale = '';

    return yourls_apply_filter( 'get_locale', $yourls_locale );
}

/**
 * Retrieves the translation of a string.
 *
 * If there is no translation, or the text domain isn't loaded, the original
 * string is returned.
 *
 * @since 1.6
 * @param string $text   The string to translate.
 * @param string $domain Optional. The text domain. Default 'default'.
 * @return string The translated string.
 */
function yourls_translate( $text, $domain = 'default' ) {
    $translations = yourls_get_translations_for_domain( $domain );
    return yourls_apply_filter( 'translate', $translations->translate( $text ), $text, $domain );
}

/**
 * Retrieves the translation of a string with a given context.
 *
 * This function is used when a string has multiple meanings and needs to be
 * translated differently depending on the context.
 *
 * @since 1.6
 * @param string $text    The string to translate.
 * @param string $context The context of the string.
 * @param string $domain  Optional. The text domain. Default 'default'.
 * @return string The translated string.
 */
function yourls_translate_with_context( $text, $context, $domain = 'default' ) {
    $translations = yourls_get_translations_for_domain( $domain );
    return yourls_apply_filter( 'translate_with_context', $translations->translate( $text, $context ), $text, $context, $domain );
}

/**
 * Retrieves the translation of a string.
 *
 * This is an alias of yourls_translate().
 *
 * @since 1.6
 * @param string $text   The string to translate.
 * @param string $domain Optional. The text domain. Default 'default'.
 * @return string The translated string.
 */
function yourls__( $text, $domain = 'default' ) {
    return yourls_translate( $text, $domain );
}

/**
 * Returns a translated sprintf() string.
 *
 * This function is a wrapper for `sprintf()` that translates the format string
 * before formatting it.
 *
 * @since 1.6
 * @param string $pattern The format string to translate.
 * @param mixed  ...$args The arguments for the format string.
 * @return string The translated and formatted string.
 */
function yourls_s( $pattern ) {
    // Get pattern and pattern arguments
    $args = func_get_args();
    // If yourls_s() called by yourls_se(), all arguments are wrapped in the same array key
    if( count( $args ) == 1 && is_array( $args[0] ) ) {
        $args = $args[0];
    }
    $pattern = $args[0];

    // get list of sprintf tokens (%s and such)
    $num_of_tokens = substr_count( $pattern, '%' ) - 2 * substr_count( $pattern, '%%' );

    $domain = 'default';
    // More arguments passed than needed for the sprintf? The last one will be the domain
    if( $num_of_tokens < ( count( $args ) - 1 ) ) {
        $domain = array_pop( $args );
    }

    // Translate text
    $args[0] = yourls__( $pattern, $domain );

    return call_user_func_array( 'sprintf', $args );
}

/**
 * Echos a translated sprintf() string.
 *
 * This function is a wrapper for `printf()` that translates the format string
 * before formatting it.
 *
 * @since 1.6
 * @param string $pattern The format string to translate.
 * @param mixed  ...$args The arguments for the format string.
 * @return void
 */
function yourls_se( $pattern ) {
    echo yourls_s( func_get_args() );
}


/**
 * Retrieves the translation of a string and escapes it for safe use in an attribute.
 *
 * @since 1.6
 * @param string $text   The string to translate.
 * @param string $domain Optional. The text domain. Default 'default'.
 * @return string The translated and escaped string.
 */
function yourls_esc_attr__( $text, $domain = 'default' ) {
    return yourls_esc_attr( yourls_translate( $text, $domain ) );
}

/**
 * Retrieves the translation of a string and escapes it for safe use in HTML.
 *
 * @since 1.6
 * @param string $text   The string to translate.
 * @param string $domain Optional. The text domain. Default 'default'.
 * @return string The translated and escaped string.
 */
function yourls_esc_html__( $text, $domain = 'default' ) {
    return yourls_esc_html( yourls_translate( $text, $domain ) );
}

/**
 * Displays the translation of a string.
 *
 * @since 1.6
 * @param string $text   The string to translate.
 * @param string $domain Optional. The text domain. Default 'default'.
 * @return void
 */
function yourls_e( $text, $domain = 'default' ) {
    echo yourls_translate( $text, $domain );
}

/**
 * Displays a translated string that has been escaped for safe use in an attribute.
 *
 * @since 1.6
 * @param string $text   The string to translate.
 * @param string $domain Optional. The text domain. Default 'default'.
 * @return void
 */
function yourls_esc_attr_e( $text, $domain = 'default' ) {
    echo yourls_esc_attr( yourls_translate( $text, $domain ) );
}

/**
 * Displays a translated string that has been escaped for safe use in HTML.
 *
 * @since 1.6
 * @param string $text   The string to translate.
 * @param string $domain Optional. The text domain. Default 'default'.
 * @return void
 */
function yourls_esc_html_e( $text, $domain = 'default' ) {
    echo yourls_esc_html( yourls_translate( $text, $domain ) );
}

/**
 * Retrieves the translation of a string with a given context.
 *
 * @since 1.6
 * @param string $text    The string to translate.
 * @param string $context The context of the string.
 * @param string $domain  Optional. The text domain. Default 'default'.
 * @return string The translated string.
 */
function yourls_x( $text, $context, $domain = 'default' ) {
    return yourls_translate_with_context( $text, $context, $domain );
}

/**
 * Displays a translated string with a given context.
 *
 * @since 1.7.1
 * @param string $text    The string to translate.
 * @param string $context The context of the string.
 * @param string $domain  Optional. The text domain. Default 'default'.
 * @return void
 */
function yourls_xe( $text, $context, $domain = 'default' ) {
    echo yourls_x( $text, $context, $domain );
}


/**
 * Retrieves the translation of a string with a given context, and escapes it for safe use in an attribute.
 *
 * @since 1.6
 * @param string $single  The string to translate.
 * @param string $context The context of the string.
 * @param string $domain  Optional. The text domain. Default 'default'.
 * @return string The translated and escaped string.
 */
function yourls_esc_attr_x( $single, $context, $domain = 'default' ) {
    return yourls_esc_attr( yourls_translate_with_context( $single, $context, $domain ) );
}

/**
 * Retrieves the translation of a string with a given context, and escapes it for safe use in HTML.
 *
 * @since 1.6
 * @param string $single  The string to translate.
 * @param string $context The context of the string.
 * @param string $domain  Optional. The text domain. Default 'default'.
 * @return string The translated and escaped string.
 */
function yourls_esc_html_x( $single, $context, $domain = 'default' ) {
    return yourls_esc_html( yourls_translate_with_context( $single, $context, $domain ) );
}

/**
 * Retrieves the plural or single form of a string based on the number.
 *
 * @since 1.6
 * @param string $single The text that will be used if $number is 1.
 * @param string $plural The text that will be used if $number is not 1.
 * @param int    $number The number to compare against to use either $single or $plural.
 * @param string $domain Optional. The text domain. Default 'default'.
 * @return string The translated plural or single form of the string.
 */
function yourls_n( $single, $plural, $number, $domain = 'default' ) {
    $translations = yourls_get_translations_for_domain( $domain );
    $translation = $translations->translate_plural( $single, $plural, $number );
    return yourls_apply_filter( 'translate_n', $translation, $single, $plural, $number, $domain );
}

/**
 * Retrieves the plural or single form of a string with a given context.
 *
 * @since 1.6
 * @param string $single  The text that will be used if $number is 1.
 * @param string $plural  The text that will be used if $number is not 1.
 * @param int    $number  The number to compare against to use either $single or $plural.
 * @param string $context The context of the string.
 * @param string $domain  Optional. The text domain. Default 'default'.
 * @return string The translated plural or single form of the string.
 */
function yourls_nx($single, $plural, $number, $context, $domain = 'default') {
    $translations = yourls_get_translations_for_domain( $domain );
    $translation = $translations->translate_plural( $single, $plural, $number, $context );
    return yourls_apply_filter( 'translate_nx', $translation, $single, $plural, $number, $context, $domain );
}

/**
 * Registers plural strings for translation, but does not translate them.
 *
 * This function is used to register plural strings for translation, but it does
 * not actually translate them. This is useful when you want to register a
 * string for translation, but you don't want to translate it until later.
 *
 * @since 1.6
 * @param string      $singular The single form of the string.
 * @param string      $plural   The plural form of the string.
 * @param string|null $domain   Optional. The text domain. Default null.
 * @return array An array containing the single and plural forms of the string.
 */
function yourls_n_noop( $singular, $plural, $domain = null ) {
    return array(
        0 => $singular,
        1 => $plural,
        'singular' => $singular,
        'plural' => $plural,
        'context' => null,
        'domain' => $domain
    );
}

/**
 * Registers plural strings with context for translation, but does not translate them.
 *
 * @since 1.6
 * @param string      $singular The single form of the string.
 * @param string      $plural   The plural form of the string.
 * @param string      $context  The context of the string.
 * @param string|null $domain   Optional. The text domain. Default null.
 * @return array An array containing the single and plural forms of the string.
 */
function yourls_nx_noop( $singular, $plural, $context, $domain = null ) {
    return array(
        0 => $singular,
        1 => $plural,
        2 => $context,
        'singular' => $singular,
        'plural' => $plural,
        'context' => $context,
        'domain' => $domain
    );
}

/**
 * Translates the result of yourls_n_noop() or yourls_nx_noop().
 *
 * @since 1.6
 * @param array  $nooped_plural The result of yourls_n_noop() or yourls_nx_noop().
 * @param int    $count         The number of items.
 * @param string $domain        Optional. The text domain. Default 'default'.
 * @return string The translated string.
 */
function yourls_translate_nooped_plural( $nooped_plural, $count, $domain = 'default' ) {
    if ( $nooped_plural['domain'] )
        $domain = $nooped_plural['domain'];

    if ( $nooped_plural['context'] )
        return yourls_nx( $nooped_plural['singular'], $nooped_plural['plural'], $count, $nooped_plural['context'], $domain );
    else
        return yourls_n( $nooped_plural['singular'], $nooped_plural['plural'], $count, $domain );
}

/**
 * Loads a MO file into a text domain.
 *
 * @since 1.6
 * @param string $domain The text domain.
 * @param string $mofile The path to the MO file.
 * @return bool True on success, false on failure.
 */
function yourls_load_textdomain( $domain, $mofile ) {
    global $yourls_l10n;

    $plugin_override = yourls_apply_filter( 'override_load_textdomain', false, $domain, $mofile );

    if ( true == $plugin_override ) {
        return true;
    }

    yourls_do_action( 'load_textdomain', $domain, $mofile );

    $mofile = yourls_apply_filter( 'load_textdomain_mofile', $mofile, $domain );

    if ( !is_readable( $mofile ) ) {
        trigger_error( 'Cannot read file ' . str_replace( YOURLS_ABSPATH.'/', '', $mofile ) . '.'
                    . ' Make sure there is a language file installed. More info: http://yourls.org/translations' );
        return false;
    }

    $mo = new MO();
    if ( !$mo->import_from_file( $mofile ) )
        return false;

    if ( isset( $yourls_l10n[$domain] ) )
        $mo->merge_with( $yourls_l10n[$domain] );

    $yourls_l10n[$domain] = &$mo;

    return true;
}

/**
 * Unloads a text domain.
 *
 * @since 1.6
 * @param string $domain The text domain to unload.
 * @return bool True on success, false on failure.
 */
function yourls_unload_textdomain( $domain ) {
    global $yourls_l10n;

    $plugin_override = yourls_apply_filter( 'override_unload_textdomain', false, $domain );

    if ( $plugin_override )
        return true;

    yourls_do_action( 'unload_textdomain', $domain );

    if ( isset( $yourls_l10n[$domain] ) ) {
        unset( $yourls_l10n[$domain] );
        return true;
    }

    return false;
}

/**
 * Loads the default text domain.
 *
 * Loads the MO file for the current locale.
 *
 * @since 1.6
 * @return bool True on success, false on failure.
 */
function yourls_load_default_textdomain() {
    $yourls_locale = yourls_get_locale();

    if( !empty( $yourls_locale ) )
        return yourls_load_textdomain( 'default', YOURLS_LANG_DIR . "/$yourls_locale.mo" );

    return false;
}

/**
 * Returns the translations for a text domain.
 *
 * If the text domain is not loaded, a new NOOPTranslations instance is returned.
 *
 * @since 1.6
 * @param string $domain The text domain.
 * @return NOOPTranslations A translations instance.
 */
function yourls_get_translations_for_domain( $domain ) {
    global $yourls_l10n;
    if ( !isset( $yourls_l10n[$domain] ) ) {
        $yourls_l10n[$domain] = new NOOPTranslations;
    }
    return $yourls_l10n[$domain];
}

/**
 * Checks if a text domain is loaded.
 *
 * @since 1.6
 * @param string $domain The text domain.
 * @return bool True if the text domain is loaded, false otherwise.
 */
function yourls_is_textdomain_loaded( $domain ) {
    global $yourls_l10n;
    return isset( $yourls_l10n[$domain] );
}

/**
 * Translates a user role name.
 *
 * @since 1.6
 * @param string $name The user role name.
 * @return string The translated user role name.
 */
function yourls_translate_user_role( $name ) {
    return yourls_translate_with_context( $name, 'User role' );
}

/**
 * Gets a list of available languages.
 *
 * @since 1.6
 * @param string|null $dir Optional. A directory in which to search for language files.
 *                         Default YOURLS_LANG_DIR.
 * @return array An array of language codes.
 */
function yourls_get_available_languages( $dir = null ) {
    $languages = array();

    $dir = is_null( $dir) ? YOURLS_LANG_DIR : $dir;

    foreach( (array) glob( $dir . '/*.mo' ) as $lang_file ) {
        $languages[] = basename( $lang_file, '.mo' );
    }

    return yourls_apply_filter( 'get_available_languages', $languages );
}

/**
 * Formats a number with localized thousands separator.
 *
 * @since 1.6
 * @param int $number   The number to format.
 * @param int $decimals Optional. The number of decimal places. Default 0.
 * @return string The formatted number.
 */
function yourls_number_format_i18n( $number, $decimals = 0 ) {
    global $yourls_locale_formats;
    if( !isset( $yourls_locale_formats ) )
        $yourls_locale_formats = new YOURLS_Locale_Formats();

    $formatted = number_format( $number, abs( intval( $decimals ) ), $yourls_locale_formats->number_format['decimal_point'], $yourls_locale_formats->number_format['thousands_sep'] );
    return yourls_apply_filter( 'number_format_i18n', $formatted );
}

/**
 * Formats a date for the current locale.
 *
 * @since 1.6
 * @param string   $dateformatstring The format string for the date.
 * @param int|bool $timestamp        Optional. A Unix timestamp. Default false (current time).
 * @return string The formatted date.
 */
function yourls_date_i18n( $dateformatstring, $timestamp = false ) {
    /**
     * @var YOURLS_Locale_Formats $yourls_locale_formats
     */
    global $yourls_locale_formats;
    if( !isset( $yourls_locale_formats ) )
        $yourls_locale_formats = new YOURLS_Locale_Formats();

    if ( false === $timestamp ) {
        $timestamp = yourls_get_timestamp( time() );
    }

    // store original value for language with untypical grammars
    $req_format = $dateformatstring;

    /**
     * Replace the date format characters with their translatation, if found
     * Example:
     *     'l d F Y' gets replaced with '\L\u\n\d\i d \M\a\i Y' in French
     * We deliberately don't deal with 'I', 'O', 'P', 'T', 'Z' and 'e' in date format (timezones)
     */
    if ( ( !empty( $yourls_locale_formats->month ) ) && ( !empty( $yourls_locale_formats->weekday ) ) ) {
        $datemonth            = $yourls_locale_formats->get_month( date( 'm', $timestamp ) );
        $datemonth_abbrev     = $yourls_locale_formats->get_month_abbrev( $datemonth );
        $dateweekday          = $yourls_locale_formats->get_weekday( date( 'w', $timestamp ) );
        $dateweekday_abbrev   = $yourls_locale_formats->get_weekday_abbrev( $dateweekday );
        $datemeridiem         = $yourls_locale_formats->get_meridiem( date( 'a', $timestamp ) );
        $datemeridiem_capital = $yourls_locale_formats->get_meridiem( date( 'A', $timestamp ) );

        $dateformatstring = ' '.$dateformatstring;
        $dateformatstring = preg_replace( "/([^\\\])D/", "\\1" . yourls_backslashit( $dateweekday_abbrev ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])F/", "\\1" . yourls_backslashit( $datemonth ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])l/", "\\1" . yourls_backslashit( $dateweekday ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])M/", "\\1" . yourls_backslashit( $datemonth_abbrev ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])a/", "\\1" . yourls_backslashit( $datemeridiem ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])A/", "\\1" . yourls_backslashit( $datemeridiem_capital ), $dateformatstring );

        $dateformatstring = substr( $dateformatstring, 1, strlen( $dateformatstring ) -1 );
    }

    $date = date( $dateformatstring, $timestamp );

    // Allow plugins to redo this entirely for languages with untypical grammars
    return yourls_apply_filter('date_i18n', $date, $req_format, $timestamp);
}

/**
 * Class that loads the calendar locale.
 *
 * @since 1.6
 */
class YOURLS_Locale_Formats {
    /**
     * Stores the translated strings for the full weekday names.
     *
     * @since 1.6
     * @var array
     * @access private
     */
    var $weekday;

    /**
     * Stores the translated strings for the one character weekday names.
     *
     * There is a hack to make sure that Tuesday and Thursday, as well
     * as Sunday and Saturday, don't conflict. See init() method for more.
     *
     * @see YOURLS_Locale_Formats::init() for how to handle the hack.
     *
     * @since 1.6
     * @var array
     * @access private
     */
    var $weekday_initial;

    /**
     * Stores the translated strings for the abbreviated weekday names.
     *
     * @since 1.6
     * @var array
     * @access private
     */
    var $weekday_abbrev;

    /**
     * Stores the translated strings for the full month names.
     *
     * @since 1.6
     * @var array
     * @access private
     */
    var $month;

    /**
     * Stores the translated strings for the abbreviated month names.
     *
     * @since 1.6
     * @var array
     * @access private
     */
    var $month_abbrev;

    /**
     * Stores the translated strings for 'am' and 'pm'.
     *
     * Also the capitalized versions.
     *
     * @since 1.6
     * @var array
     * @access private
     */
    var $meridiem;

    /**
     * Stores the translated number format
     *
     * @since 1.6
     * @var array
     * @access private
     */
    var $number_format;

    /**
     * The text direction of the locale language.
     *
     * Default is left to right 'ltr'.
     *
     * @since 1.6
     * @var string
     * @access private
     */
    var $text_direction = 'ltr';

    /**
     * Sets up the translated strings and object properties.
     *
     * The method creates the translatable strings for various
     * calendar elements. Which allows for specifying locale
     * specific calendar names and text direction.
     *
     * @since 1.6
     * @access private
     * @return void
     */
    function init() {
        // The Weekdays
        $this->weekday[0] = /* //translators: weekday */ yourls__( 'Sunday' );
        $this->weekday[1] = /* //translators: weekday */ yourls__( 'Monday' );
        $this->weekday[2] = /* //translators: weekday */ yourls__( 'Tuesday' );
        $this->weekday[3] = /* //translators: weekday */ yourls__( 'Wednesday' );
        $this->weekday[4] = /* //translators: weekday */ yourls__( 'Thursday' );
        $this->weekday[5] = /* //translators: weekday */ yourls__( 'Friday' );
        $this->weekday[6] = /* //translators: weekday */ yourls__( 'Saturday' );

        // The first letter of each day. The _%day%_initial suffix is a hack to make
        // sure the day initials are unique.
        $this->weekday_initial[yourls__( 'Sunday' )]    = /* //translators: one-letter abbreviation of the weekday */ yourls__( 'S_Sunday_initial' );
        $this->weekday_initial[yourls__( 'Monday' )]    = /* //translators: one-letter abbreviation of the weekday */ yourls__( 'M_Monday_initial' );
        $this->weekday_initial[yourls__( 'Tuesday' )]   = /* //translators: one-letter abbreviation of the weekday */ yourls__( 'T_Tuesday_initial' );
        $this->weekday_initial[yourls__( 'Wednesday' )] = /* //translators: one-letter abbreviation of the weekday */ yourls__( 'W_Wednesday_initial' );
        $this->weekday_initial[yourls__( 'Thursday' )]  = /* //translators: one-letter abbreviation of the weekday */ yourls__( 'T_Thursday_initial' );
        $this->weekday_initial[yourls__( 'Friday' )]    = /* //translators: one-letter abbreviation of the weekday */ yourls__( 'F_Friday_initial' );
        $this->weekday_initial[yourls__( 'Saturday' )]  = /* //translators: one-letter abbreviation of the weekday */ yourls__( 'S_Saturday_initial' );

        foreach ($this->weekday_initial as $weekday_ => $weekday_initial_) {
            $this->weekday_initial[$weekday_] = preg_replace('/_.+_initial$/', '', $weekday_initial_);
        }

        // Abbreviations for each day.
        $this->weekday_abbrev[ yourls__( 'Sunday' ) ]    = /* //translators: three-letter abbreviation of the weekday */ yourls__( 'Sun' );
        $this->weekday_abbrev[ yourls__( 'Monday' ) ]    = /* //translators: three-letter abbreviation of the weekday */ yourls__( 'Mon' );
        $this->weekday_abbrev[ yourls__( 'Tuesday' ) ]   = /* //translators: three-letter abbreviation of the weekday */ yourls__( 'Tue' );
        $this->weekday_abbrev[ yourls__( 'Wednesday' ) ] = /* //translators: three-letter abbreviation of the weekday */ yourls__( 'Wed' );
        $this->weekday_abbrev[ yourls__( 'Thursday' ) ]  = /* //translators: three-letter abbreviation of the weekday */ yourls__( 'Thu' );
        $this->weekday_abbrev[ yourls__( 'Friday' ) ]    = /* //translators: three-letter abbreviation of the weekday */ yourls__( 'Fri' );
        $this->weekday_abbrev[ yourls__( 'Saturday' ) ]  = /* //translators: three-letter abbreviation of the weekday */ yourls__( 'Sat' );

        // The Months
        $this->month['01'] = /* //translators: month name */ yourls__( 'January' );
        $this->month['02'] = /* //translators: month name */ yourls__( 'February' );
        $this->month['03'] = /* //translators: month name */ yourls__( 'March' );
        $this->month['04'] = /* //translators: month name */ yourls__( 'April' );
        $this->month['05'] = /* //translators: month name */ yourls__( 'May' );
        $this->month['06'] = /* //translators: month name */ yourls__( 'June' );
        $this->month['07'] = /* //translators: month name */ yourls__( 'July' );
        $this->month['08'] = /* //translators: month name */ yourls__( 'August' );
        $this->month['09'] = /* //translators: month name */ yourls__( 'September' );
        $this->month['10'] = /* //translators: month name */ yourls__( 'October' );
        $this->month['11'] = /* //translators: month name */ yourls__( 'November' );
        $this->month['12'] = /* //translators: month name */ yourls__( 'December' );

        // Abbreviations for each month. Uses the same hack as above to get around the
        // 'May' duplication.
        $this->month_abbrev[ yourls__( 'January' ) ]   = /* //translators: three-letter abbreviation of the month */ yourls__( 'Jan_January_abbreviation' );
        $this->month_abbrev[ yourls__( 'February' ) ]  = /* //translators: three-letter abbreviation of the month */ yourls__( 'Feb_February_abbreviation' );
        $this->month_abbrev[ yourls__( 'March' ) ]     = /* //translators: three-letter abbreviation of the month */ yourls__( 'Mar_March_abbreviation' );
        $this->month_abbrev[ yourls__( 'April' ) ]     = /* //translators: three-letter abbreviation of the month */ yourls__( 'Apr_April_abbreviation' );
        $this->month_abbrev[ yourls__( 'May' ) ]       = /* //translators: three-letter abbreviation of the month */ yourls__( 'May_May_abbreviation' );
        $this->month_abbrev[ yourls__( 'June' ) ]      = /* //translators: three-letter abbreviation of the month */ yourls__( 'Jun_June_abbreviation' );
        $this->month_abbrev[ yourls__( 'July' ) ]      = /* //translators: three-letter abbreviation of the month */ yourls__( 'Jul_July_abbreviation' );
        $this->month_abbrev[ yourls__( 'August' ) ]    = /* //translators: three-letter abbreviation of the month */ yourls__( 'Aug_August_abbreviation' );
        $this->month_abbrev[ yourls__( 'September' ) ] = /* //translators: three-letter abbreviation of the month */ yourls__( 'Sep_September_abbreviation' );
        $this->month_abbrev[ yourls__( 'October' ) ]   = /* //translators: three-letter abbreviation of the month */ yourls__( 'Oct_October_abbreviation' );
        $this->month_abbrev[ yourls__( 'November' ) ]  = /* //translators: three-letter abbreviation of the month */ yourls__( 'Nov_November_abbreviation' );
        $this->month_abbrev[ yourls__( 'December' ) ]  = /* //translators: three-letter abbreviation of the month */ yourls__( 'Dec_December_abbreviation' );

        foreach ($this->month_abbrev as $month_ => $month_abbrev_) {
            $this->month_abbrev[$month_] = preg_replace('/_.+_abbreviation$/', '', $month_abbrev_);
        }

        // The Meridiems
        $this->meridiem['am'] = yourls__( 'am' );
        $this->meridiem['pm'] = yourls__( 'pm' );
        $this->meridiem['AM'] = yourls__( 'AM' );
        $this->meridiem['PM'] = yourls__( 'PM' );

        // Numbers formatting
        // See http://php.net/number_format

        /* //translators: $thousands_sep argument for http://php.net/number_format, default is , */
        $trans = yourls__( 'number_format_thousands_sep' );
        $this->number_format['thousands_sep'] = ('number_format_thousands_sep' == $trans) ? ',' : $trans;

        /* //translators: $dec_point argument for http://php.net/number_format, default is . */
        $trans = yourls__( 'number_format_decimal_point' );
        $this->number_format['decimal_point'] = ('number_format_decimal_point' == $trans) ? '.' : $trans;

        // Set text direction.
        if ( isset( $GLOBALS['text_direction'] ) )
            $this->text_direction = $GLOBALS['text_direction'];
        /* //translators: 'rtl' or 'ltr'. This sets the text direction for YOURLS. */
        elseif ( 'rtl' == yourls_x( 'ltr', 'text direction' ) )
            $this->text_direction = 'rtl';
    }

    /**
     * Retrieve the full translated weekday word.
     *
     * Week starts on translated Sunday and can be fetched
     * by using 0 (zero). So the week starts with 0 (zero)
     * and ends on Saturday with is fetched by using 6 (six).
     *
     * @since 1.6
     * @access public
     *
     * @param int|string $weekday_number 0 for Sunday through 6 Saturday
     * @return string Full translated weekday
     */
    function get_weekday( $weekday_number ) {
        return $this->weekday[ $weekday_number ];
    }

    /**
     * Retrieve the translated weekday initial.
     *
     * The weekday initial is retrieved by the translated
     * full weekday word. When translating the weekday initial
     * pay attention to make sure that the starting letter does
     * not conflict.
     *
     * @since 1.6
     * @access public
     *
     * @param string $weekday_name
     * @return string
     */
    function get_weekday_initial( $weekday_name ) {
        return $this->weekday_initial[ $weekday_name ];
    }

    /**
     * Retrieve the translated weekday abbreviation.
     *
     * The weekday abbreviation is retrieved by the translated
     * full weekday word.
     *
     * @since 1.6
     * @access public
     *
     * @param string $weekday_name Full translated weekday word
     * @return string Translated weekday abbreviation
     */
    function get_weekday_abbrev( $weekday_name ) {
        return $this->weekday_abbrev[ $weekday_name ];
    }

    /**
     * Retrieve the full translated month by month number.
     *
     * The $month_number parameter has to be a string
     * because it must have the '0' in front of any number
     * that is less than 10. Starts from '01' and ends at
     * '12'.
     *
     * You can use an integer instead and it will add the
     * '0' before the numbers less than 10 for you.
     *
     * @since 1.6
     * @access public
     *
     * @param string|int $month_number '01' through '12'
     * @return string Translated full month name
     */
    function get_month( $month_number ) {
        return $this->month[ sprintf( '%02s', $month_number ) ];
    }

    /**
     * Retrieve translated version of month abbreviation string.
     *
     * The $month_name parameter is expected to be the translated or
     * translatable version of the month.
     *
     * @since 1.6
     * @access public
     *
     * @param string $month_name Translated month to get abbreviated version
     * @return string Translated abbreviated month
     */
    function get_month_abbrev( $month_name ) {
        return $this->month_abbrev[ $month_name ];
    }

    /**
     * Retrieve translated version of meridiem string.
     *
     * The $meridiem parameter is expected to not be translated.
     *
     * @since 1.6
     * @access public
     *
     * @param string $meridiem Either 'am', 'pm', 'AM', or 'PM'. Not translated version.
     * @return string Translated version
     */
    function get_meridiem( $meridiem ) {
        return $this->meridiem[ $meridiem ];
    }

    /**
     * Global variables are deprecated. For backwards compatibility only.
     *
     * @deprecated For backwards compatibility only.
     * @access private
     *
     * @since 1.6
     * @return void
     */
    function register_globals() {
        $GLOBALS['weekday']         = $this->weekday;
        $GLOBALS['weekday_initial'] = $this->weekday_initial;
        $GLOBALS['weekday_abbrev']  = $this->weekday_abbrev;
        $GLOBALS['month']           = $this->month;
        $GLOBALS['month_abbrev']    = $this->month_abbrev;
    }

    /**
     * Constructor which calls helper methods to set up object variables
     *
     * @uses YOURLS_Locale_Formats::init()
     * @uses YOURLS_Locale_Formats::register_globals()
     * @since 1.6
     *
     * @return YOURLS_Locale_Formats
     */
    function __construct() {
        $this->init();
        $this->register_globals();
    }

    /**
     * Checks if current locale is RTL.
     *
     * @since 1.6
     * @return bool Whether locale is RTL.
     */
    function is_rtl() {
        return 'rtl' == $this->text_direction;
    }
}

/**
 * Loads a custom translation file.
 *
 * This function is used to load a translation file for a plugin or theme.
 *
 * @since 1.6
 * @param string $domain The text domain.
 * @param string $path   The full path to the directory containing the MO files.
 * @return bool|void True on success, false on failure, or void if the locale is not set.
 */
function yourls_load_custom_textdomain( $domain, $path ) {
    $locale = yourls_apply_filter( 'load_custom_textdomain', yourls_get_locale(), $domain );
    if( !empty( $locale ) ) {
        $mofile = rtrim( $path, '/' ) . '/'. $domain . '-' . $locale . '.mo';
        return yourls_load_textdomain( $domain, $mofile );
    }
}

/**
 * Checks if the current locale is right-to-left (RTL).
 *
 * @since 1.6
 * @return bool True if the locale is RTL, false otherwise.
 */
function yourls_is_rtl() {
    global $yourls_locale_formats;
    if( !isset( $yourls_locale_formats ) )
        $yourls_locale_formats = new YOURLS_Locale_Formats();

    return $yourls_locale_formats->is_rtl();
}

/**
 * Returns the translated weekday abbreviation.
 *
 * @since 1.6
 * @param string|int $weekday Optional. A full textual weekday (e.g., "Friday"), or an integer (0 = Sunday, 6 = Saturday).
 *                            Default ''.
 * @return string|array The translated weekday abbreviation, or an array of all translated weekday abbreviations if $weekday is empty.
 */
function yourls_l10n_weekday_abbrev( $weekday = '' ){
    global $yourls_locale_formats;
    if( !isset( $yourls_locale_formats ) )
        $yourls_locale_formats = new YOURLS_Locale_Formats();

    if( $weekday === '' )
        return $yourls_locale_formats->weekday_abbrev;

    if( is_int( $weekday ) ) {
        $day = $yourls_locale_formats->weekday[ $weekday ];
        return $yourls_locale_formats->weekday_abbrev[ $day ];
    } else {
        return $yourls_locale_formats->weekday_abbrev[ yourls__( $weekday ) ];
    }
}

/**
 * Returns the translated weekday initial.
 *
 * @since 1.6
 * @param string|int $weekday Optional. A full textual weekday (e.g., "Friday"), or an integer (0 = Sunday, 6 = Saturday).
 *                            Default ''.
 * @return string|array The translated weekday initial, or an array of all translated weekday initials if $weekday is empty.
 */
function yourls_l10n_weekday_initial( $weekday = '' ){
    global $yourls_locale_formats;
    if( !isset( $yourls_locale_formats ) )
        $yourls_locale_formats = new YOURLS_Locale_Formats();

    if( $weekday === '' )
        return $yourls_locale_formats->weekday_initial;

    if( is_int( $weekday ) ) {
        $weekday = $yourls_locale_formats->weekday[ $weekday ];
        return $yourls_locale_formats->weekday_initial[ $weekday ];
    } else {
        return $yourls_locale_formats->weekday_initial[ yourls__( $weekday ) ];
    }
}

/**
 * Returns the translated month abbreviation.
 *
 * @since 1.6
 * @param string|int $month Optional. A full textual month (e.g., "November"), or an integer (1-12). Default ''.
 * @return string|array The translated month abbreviation, or an array of all translated month abbreviations if $month is empty.
 */
function yourls_l10n_month_abbrev( $month = '' ){
    global $yourls_locale_formats;
    if( !isset( $yourls_locale_formats ) )
        $yourls_locale_formats = new YOURLS_Locale_Formats();

    if( $month === '' )
        return $yourls_locale_formats->month_abbrev;

    if( intval( $month ) > 0 ) {
        $month = sprintf('%02d', intval( $month ) );
        $month = $yourls_locale_formats->month[ $month ];
        return $yourls_locale_formats->month_abbrev[ $month ];
    } else {
        return $yourls_locale_formats->month_abbrev[ yourls__( $month ) ];
    }
}

/**
 * Returns an array of translated month names.
 *
 * @since 1.6
 * @return array An array of translated month names.
 */
function yourls_l10n_months(){
    global $yourls_locale_formats;
    if( !isset( $yourls_locale_formats ) )
        $yourls_locale_formats = new YOURLS_Locale_Formats();

    return $yourls_locale_formats->month;
}
