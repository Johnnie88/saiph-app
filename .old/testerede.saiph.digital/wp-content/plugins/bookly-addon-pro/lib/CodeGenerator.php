<?php
namespace BooklyPro\Lib;

abstract class CodeGenerator
{
    const ERROR_FULLY_GENERATED  = 1;
    const ERROR_NOT_ENOUGH_CODES = 2;

    /** @var string */
    protected static $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
    /** @var int */
    protected static $chars_count = 36;

    /**
     * Generate unique code for given mask.
     *
     * @param string $class
     * @param string $mask
     * @return string
     * @throws \Exception
     */
    public static function generateUniqueCode( $class, $mask )
    {
        $existing_codes = static::_getExistingCodes( $class, $mask );

        if ( count( $existing_codes ) >= static::_getNumberOfPossibleCodes( $mask ) ) {
            throw new \Exception( '', static::ERROR_FULLY_GENERATED );
        }

        return static::_generate( $mask, $existing_codes );
    }

    /**
     * Generate unique code series for given mask.
     *
     * @param string $class
     * @param string $mask
     * @param int $amount
     * @return array
     * @throws \Exception
     */
    public static function generateUniqueCodeSeries( $class, $mask, $amount )
    {
        $existing_codes = static::_getExistingCodes( $class, $mask );

        if ( ( $remaining = static::_getNumberOfPossibleCodes( $mask ) - count( $existing_codes ) ) < $amount  ) {
            throw new \Exception( $remaining, static::ERROR_NOT_ENOUGH_CODES );
        }

        $res = array();
        do {
            $res[] = static::_generate( $mask, $existing_codes + array_flip( $res ) );
        } while ( count( $res ) < $amount );

        return $res;
    }

    /**
     * Generate mask.
     *
     * @param string $mask
     * @param array $existing_codes
     * @return string
     */
    private static function _generate( $mask, array $existing_codes )
    {
        // Generate.
        $len = strlen( $mask );
        do {
            $res = '';
            for ( $i = 0; $i < $len; ++ $i ) {
                if ( $mask[ $i ] == '*' ) {
                    $rand = mt_rand( 0, static::$chars_count - 1 );
                    $res .= static::$chars[ $rand ];
                } else {
                    $res .= $mask[ $i ];
                }
            }
        } while ( $len && isset ( $existing_codes[ $res ] ) );

        return $res;
    }

    /**
     * Get number of possible codes for the mask.
     *
     * @param string $mask
     * @return int
     */
    private static function _getNumberOfPossibleCodes( $mask )
    {
        $asterisks_count = substr_count( $mask, '*' );

        if ( $asterisks_count > 3 ) {
            // There are more than 1679616 possible codes.
            return PHP_INT_MAX;
        }

        return (int) pow( static::$chars_count, $asterisks_count );
    }

    /**
     * Get existing codes that match given mask.
     *
     * @param string $class
     * @param string $mask
     * @return array
     */
    private static function _getExistingCodes( $class, $mask )
    {
        return $class::query()
            ->select( 'DISTINCT `code`' )
            ->whereLike( 'code', str_replace( '*', '_', $mask ) )
            ->indexBy( 'code' )
            ->fetchArray();
    }
}