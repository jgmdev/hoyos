<?php
/** 
 * @author Jefferson González
 * @license MIT
*/

namespace Deficiencies;

/**
 * List of deficiency status.
 */
class DeficiencyStatus
{

    const UNFIXED = 0;
    const ASSIGNED = 1;
    const IN_PROCESS = 2;
    const FIXED = 3;

    public static function getAll()
    {
        $def_status = array(
            self::UNFIXED => t('Unfixed'),
            self::ASSIGNED => t('Assigned'),
            self::IN_PROCESS => t('In process'),
            self::FIXED => t('Fixed')
        );

        return $def_status;
    }

    public static function getStatus($id)
    {
        $def_status = self::getAll();
        return (isset($def_status[$id])) ? $def_status[$id] : null;
    }

}

?>
