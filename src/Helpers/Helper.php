<?php

namespace rjmangini\Helpers;

use Countable;

use Carbon\Carbon;

use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use Psr\Http\Message\ResponseInterface;

class Helper
{
    use Macroable;

    private $locale;

    public function __construct()
    {
        $this->locale = [
            'frac_digits'     => 2,
            'currency_symbol' => 'R$',
            'decimal_point'   => '.',
            'thousands_sep'   => '',
        ];
    }

    public function excelFormat( $type )
    {
        switch ($type) {
            case 'currency':
                return '_-[$R$-416]\ * #,##0.00_-;[Red]\-[$R$-416]\ * #,##0.00_-;_-[$R$-416]\ * "-"??_-;_-@_-';
            case 'integer' :
                return '#,##0_ ;[Red]\-#,##0_ ;\-_ ';
            case 'decimal' :
                return '#,##0.00_ ;[Red]\-#,##0.00_ ;\-_ ';
            case 'text' :
                return '@';
            case 'datetime' :
                return 'dd/mm/yyyy hh:mm:ss';
            case 'date' :
                return 'dd/mm/yyyy';
            default:
                return 'General';
        }
    }

    public function formatExcelDatetime( Carbon $value = null )
    {
        if (empty( $value )) {
            return null;
        }

        return number_format( \PHPExcel_Shared_Date::PHPToExcel( $value, true, tz() ), 12, '.', '' );
    }

    public function parseDecimal( $value, $decimalDigits = 5, $decimal = false, $thousands = false )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return 0.0;
        }

        // no leading zeroes
        $value = preg_replace( '/^0*?([1-9])/', '$1', $value );

        if ($decimal === false) {
            $decimal = config( 'helpers.request_decimal_point', $this->locale[ 'decimal_point' ] );
        }

        if ($thousands === false) {
            $thousands = config( 'helpers.request_thousands_separator', $this->locale[ 'thousands_sep' ] );
        }

        $value = str_replace( $thousands, '', $value );
        $value = str_replace( $decimal, $this->locale[ 'decimal_point' ], $value );

        $pattern = sprintf( '/[^0-9\%s]/', $this->locale[ 'decimal_point' ] );
        $value   = preg_replace( $pattern, '', $value );

        if (empty( $value )) {
            return 0.0;
        }

        $value = str_replace( $this->locale[ 'decimal_point' ], '.', $value );

        return (float)number_format( $value, $decimalDigits, '.', '' );
    }

    public function parseInteger( $value )
    {
        return (int)$this->parseDecimal( $value, 0 );
    }

    public function parseDigitsString( $value, $keepLeadingZeroes = true )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        $value = preg_replace( '/\D/', '', $value );

        if (!$keepLeadingZeroes) {
            $value = preg_replace( '/^0+/', '', $value );
        }

        return empty( $value ) ? null : $value;
    }

    public function parsePhoneNumber( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        if (strrpos( $value, '*' ) !== false) {
            $value = preg_replace( '/[^0-9\*]/', '', $value );
        } else {
            $value = $this->parseDigitsString( $value, false );
        }

        if (empty( $value )) {
            return null;
        } else {
            return $value;
        }
    }

    public function parseUpperString( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        return mb_strtoupper( $value );
    }

    public function parseDate( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        if (preg_match( '/^\d{2}\/\d{2}\/\d{4}$/', $value )) {
            return Carbon::createFromFormat( 'd/m/Y', $value, tz() )->setTime( 0, 0 );
        }

        if (preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value )) {
            return Carbon::createFromFormat( 'Y-m-d', $value, tz() )->setTime( 0, 0 );
        }

        return $value;
    }

    public function parseDatetime( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        if (preg_match( '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/', $value )) {
            return Carbon::createFromFormat( 'd/m/Y H:i', $value, tz() );
        }

        if (preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value )) {
            return Carbon::createFromFormat( 'Y-m-d H:i:s', $value, tz() );
        }

        return $value;
    }

    public function parseSeconds( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        $matches = [ ];

        preg_match( '/^(\d{2}):(\d{2})$/', $value, $matches );

        $minutes = $matches[ 1 ];
        $seconds = $matches[ 2 ];

        return $seconds + $minutes * 60;
    }

    public function parseNumericList( $value )
    {
        $unique = array_unique(
            array_map(
                function ( $item ) {
                    return trim( $item );
                },
                array_filter( explode( ',', preg_replace( '/[^0-9,]/', '', $value ) ) )
            )
        );

        sort( $unique );

        return $unique;
    }

    public function formatStateRegion( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        switch ($value) {
            case 'CO':
                return 'Centro-Oeste';
            case 'N':
                return 'Norte';
            case 'NE':
                return 'Nordeste';
            case 'S':
                return 'Sul';
            case 'SE':
                return 'Sudeste';
            default:
                return 'Não Informada';
        }
    }

    public function formatPhoneNumber( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        if (preg_match( '/^(\d{2})(\d{4,5})(\d{4})$/', $value, $matches )) {
            return sprintf( '(%s) %s-%s', $matches[ 1 ], $matches[ 2 ], $matches[ 3 ] );
        }

        return $value;
    }

    public function formatDocument( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        if (preg_match( '/^(\d{3})(\d{3})(\d{3})(\d{2})$/', $value, $matches )) {
            return sprintf( '%s.%s.%s-%s', $matches[ 1 ], $matches[ 2 ], $matches[ 3 ], $matches[ 4 ] );
        }

        if (preg_match( '/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', $value, $matches )) {
            return sprintf(
                '%s.%s.%s/%s-%s', $matches[ 1 ], $matches[ 2 ], $matches[ 3 ], $matches[ 4 ], $matches[ 5 ]
            );
        }

        return $value;
    }

    public function formatPostalCode( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        if (preg_match( '/^(\d{5})(\d{3})$/', $value, $matches )) {
            return sprintf( '%s-%s', $matches[ 1 ], $matches[ 2 ] );
        }

        return $value;
    }

    public function formatDate( Carbon $value = null, $iso = false )
    {
        if (empty( $value )) {
            return null;
        }

        return $value->format( $iso ? 'Y-m-d' : 'd/m/Y' );
    }

    public function formatDatetime( Carbon $value = null, $iso = false )
    {
        if (empty( $value )) {
            return null;
        }

        return $value->format( $iso ? 'Y-m-d H:i:s' : 'd/m/Y H:i' );
    }

    public function formatSeconds( $value )
    {
        if (( $value = $this->checkValue( $value ) ) === false) {
            return null;
        }

        $minutes = floor( $value / 60 );
        $seconds = $value % 60;

        return sprintf( '%02d:%0d2', $minutes, $seconds );
    }

    public function formatDecimal( $value, $decimals = false, $canonical = false, $dashOnZero = false )
    {
        if ($this->checkValue( $value, is_numeric( $value ) ) === false) {
            $value = 0.0;
        }

        $value = floatval( $value );

        if ($value === 0.0 && $dashOnZero === true) {
            return '-';
        }

        if ($decimals === false) {
            $decimals = $this->locale[ 'frac_digits' ];
        }

        if ($canonical) {
            return number_format( $value, $decimals, '.', '' );
        }

        return number_format(
            $value,
            $decimals,
            config( 'helpers.frontend_decimal_point', $this->locale[ 'decimal_point' ] ),
            config( 'helpers.frontend_thousands_separator', $this->locale[ 'thousands_sep' ] )
        );
    }

    public function formatCurrency( $value, $decimals = false, $unit = false, $dashOnZero = false )
    {
        $formatted = $this->formatDecimal( $value, $decimals, false, $dashOnZero );

        if ($unit !== false) {
            $unit = ' / ' . $unit;
        }

        if ($formatted === '-') {
            return '-' . $unit;
        }

        return sprintf( '%s %s%s', $this->locale[ 'currency_symbol' ], $formatted, $unit );
    }

    public function formatPercentage( $value, $decimals = false, $dashOnZero = false )
    {
        $formatted = $this->formatDecimal( $value, $decimals, false, $dashOnZero );

        if ($formatted === '-') {
            return '-';
        }

        return sprintf( '%s%%', $formatted );
    }

    public function formatInteger( $value, $dashOnZero = false )
    {
        return $this->formatDecimal( $value, 0, false, $dashOnZero );
    }

    public function formatBoolean( $value, $trueValue = 'Sim', $falseValue = 'Não' )
    {
        return !!$value ? $trueValue : $falseValue;
    }

    public function formatReferenceMonth( $referenceMonth )
    {
        $year  = substr( $referenceMonth, 0, 4 );
        $month = substr( $referenceMonth, -2 );

        $date = Carbon::createFromDate( $year, $month, 1, tz() );

        return ucfirst( $date->formatLocalized( '%B de %Y' ) );
    }

    public function generateReferenceMonth()
    {
        $results = [ ];

        $reference = Carbon::createFromDate( null, null, 1, tz() );

        for ($i = 0; $i < 3; $i++) {
            $key = $reference->format( 'Ym' );

            $results[ $key ] = $this->formatReferenceMonth( $key );

            $reference->addMonth( 1 );
        }

        return $results;
    }

    public function fixName( $name )
    {
        $romans_regexp = '/\b(M{1,3}(?:CM|CD|D?C{0,3})(?:XC|XL|L?X{0,3})(?:IX|IV|V?I{0,3})|M{0,3}(?:CM|CD|D?C{1,3})(?:XC|XL|L?X{0,3})(?:IX|IV|V?I{0,3})|M{0,3}(?:CM|CD|D?C{0,3})(?:XC|XL|L?X{1,3})(?:IX|IV|V?I{0,3})|M{0,3}(?:CM|CD|D?C{0,3})(?:XC|XL|L?X{0,3})(?:IX|IV|V?I{1,3}))\b/i';

        $conjunctions_regexp = sprintf( '/\b(?:%s)\b/i', implode( '|',
            [
                'de',
                'di',
                'do',
                'da',
                'dos',
                'das',
                'dello',
                'della',
                'dalla',
                'dal',
                'del',
                'e',
                'em',
                'na',
                'no',
                'nas',
                'nos',
                'van',
                'von',
                'y',
            ]
        ) );

        $abbreviations_regexp = '/\b[a-z]+\. /i';

        $name = mb_strtolower( trim( $name ) );
        $name = preg_replace( '/\./', '. ', $name );
        $name = preg_replace( '/\s+/', ' ', $name );
        $name = ucwords( $name );

        // conjunções
        $name = preg_replace_callback( $conjunctions_regexp, function ( $matches ) {
            return mb_strtolower( $matches[ 0 ] );
        }, $name );

        // algarismos romanos
        $name = preg_replace_callback( $romans_regexp, function ( $matches ) {
            return mb_strtoupper( $matches[ 0 ] );
        }, $name );

        // abreviações
        $name = preg_replace_callback( $abbreviations_regexp, function ( $matches ) {
            return ucfirst( $matches[ 0 ] );
        }, $name );

        return $name;
    }

    public function removeAccents( $word )
    {
        /** @see http://stackoverflow.com/a/3373364/1211472 */

        $unwanted = [
            'Š' => 'S',
            'š' => 's',
            'Ž' => 'Z',
            'ž' => 'z',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y',
            'Ğ' => 'G',
            'İ' => 'I',
            'Ş' => 'S',
            'ğ' => 'g',
            'ı' => 'i',
            'ş' => 's',
            'ü' => 'u',
            'ă' => 'a',
            'Ă' => 'A',
            'ș' => 's',
            'Ș' => 'S',
            'ț' => 't',
            'Ț' => 'T',
        ];

        return strtr( $word, $unwanted );
    }

    public function makeId( $name, $appends = '' )
    {
        $name    = preg_replace( '/[\[\]\.\s]/', '', $name );
        $appends = preg_replace( '/[\[\]\.\s]/', '', $appends );

        return sprintf( '%s-%s-%s', $name, md5( uniqid( $name, true ) ), $appends );
    }

    public function isActivePath( &$route, $strict = false )
    {
        if (is_array( $route )) {
            foreach ($route as &$current) {
                if ($this->isActivePath( $current, $strict ) === true) {

                    return true;
                }
            }

            return false;
        }

        $url = app( \Illuminate\Http\Request::class )->url();

        if ($route === true || $route === $url) {
            $route = $url;

            return true;
        }

        if (str_is( sprintf( '%s%s', $route, ( $strict ? '' : '*' ) ), $url )) {
            return true;
        }

        return false;
    }

    public function paginator( Paginator $collection )
    {
        return $collection->appends( app( 'request' )->query() )->render();
    }

    public function inflect( $count, $singular, $plural, $none = 'nenhum', $one = 'um' )
    {
        if ($count === 0) {
            return sprintf( '%s %s', $none, $singular );
        }

        if ($count === 1) {
            return sprintf( '%s %s', $one, $singular );
        }

        if (is_numeric( $count )) {
            return sprintf( '%s %s', $this->formatInteger( $count ), $plural );
        }

        return sprintf( '%s %s', $count, $plural );
    }

    public function inflectCollection( Countable $countable, $singular, $plural, $none = 'nenhum', $one = 'um' )
    {
        $count = count( $countable );

        if (
            $countable instanceof LengthAwarePaginator
            && ( $total = $countable->total() ) > config( 'app.pagination', 30 )
        ) {
            $count = sprintf( '%s de %s', $this->formatInteger( $count ), $this->formatInteger( $total ) );
        }

        return $this->inflect( $count, $singular, $plural, $none, $one );
    }

    private function checkValue( $value, $condition = true )
    {
        if (empty( $value ) || !$condition) {
            return false;
        }

        return trim( $value );
    }

    /**
     * Wrapper for JSON decode that implements error detection with helpful
     * error messages.
     *
     * @param string $json    JSON data to parse
     * @param bool   $assoc   When true, returned objects will be converted
     *                        into associative arrays.
     * @param int    $depth   User specified recursion depth.
     * @param int    $options Bitmask of JSON decode options.
     *
     * @return mixed
     * @throws \InvalidArgumentException if the JSON cannot be parsed.
     * @link http://www.php.net/manual/en/function.json-decode.php
     * @link https://raw.githubusercontent.com/guzzle/guzzle/5.3/src/Utils.php
     */
    public function jsonDecode( $json, $assoc = false, $depth = 512, $options = 0 )
    {
        static $jsonErrors = [
            JSON_ERROR_DEPTH          => 'JSON_ERROR_DEPTH - Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR      => 'JSON_ERROR_CTRL_CHAR - Unexpected control character found',
            JSON_ERROR_SYNTAX         => 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON',
            JSON_ERROR_UTF8           => 'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        $data = \json_decode( $json, $assoc, $depth, $options );

        if (JSON_ERROR_NONE !== json_last_error()) {
            $last = json_last_error();
            throw new \InvalidArgumentException(
                'Unable to parse JSON data: '
                . ( isset( $jsonErrors[ $last ] )
                    ? $jsonErrors[ $last ]
                    : 'Unknown error' )
            );
        }

        return $data;
    }

    public function jsonFromResponse( ResponseInterface $response )
    {
        $headers = $response->getHeader( 'Content-Type' );

        $json = array_filter( $headers, function ( $header ) {
            return preg_match( '/json/', $header );
        } );

        if (empty( $json )) {
            throw new \InvalidArgumentException( 'Response type must be json' );
        }

        return $this->jsonDecode(
            (string)$response->getBody()->getContents(),
            true,
            512,
            0 //JSON_BIGINT_AS_STRING
        );
    }
}
