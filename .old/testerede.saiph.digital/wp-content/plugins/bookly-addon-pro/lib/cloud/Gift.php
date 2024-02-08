<?php
namespace BooklyPro\Lib\Cloud;

use Bookly\Lib as BooklyLib;

class Gift extends BooklyLib\Cloud\Product
{
    const ACTIVATE                = '/1.0/users/%token%/products/gift/activate';                  //POST
    const DEACTIVATE_NEXT_RENEWAL = '/1.0/users/%token%/products/gift/deactivate/next-renewal';   //POST
    const DEACTIVATE_NOW          = '/1.0/users/%token%/products/gift/deactivate/now';            //POST
    const REVERT_CANCEL           = '/1.0/users/%token%/products/gift/revert-cancel';             //POST
}