<?php
namespace BooklyPro\Lib\Cloud;

use Bookly\Lib as BooklyLib;

class Square extends BooklyLib\Cloud\Product
{
    const ACTIVATE                = '/1.0/users/%token%/products/square/activate';                  //POST
    const DEACTIVATE_NEXT_RENEWAL = '/1.0/users/%token%/products/square/deactivate/next-renewal';   //POST
    const DEACTIVATE_NOW          = '/1.0/users/%token%/products/square/deactivate/now';            //POST
    const REVERT_CANCEL           = '/1.0/users/%token%/products/square/revert-cancel';             //POST
}