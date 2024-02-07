<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @package Loco Automatic Translate Addon
 */
class Helpers{
    public static function proInstalled(){
        if (defined('ATLT_PRO_FILE')) {
            return true;
        }else{
            return false;
        }
    }
    // return user type
    public static function userType(){
        $type='';
      if(get_option('atlt-type')==false || get_option('atlt-type')=='free'){
            return $type='free';
        }else if(get_option('atlt-type')=='pro'){
            return $type='pro';
        }  
    }
   
}
