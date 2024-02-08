<?php
namespace BooklyPro\Backend\Modules\Staff\Forms;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

/**
 * @method Lib\Entities\StaffCategory save()
 */
class Category extends BooklyLib\Base\Form
{
    protected static $entity_class = 'StaffCategory';

    protected static $namespace = '\BooklyPro\Lib\Entities';

    /**
     * Configure the form.
     */
    public function configure()
    {
        $this->setFields( array( 'name' ) );
    }

}
