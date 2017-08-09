<?php
/**
 * Created by PhpStorm.
 * User: lubchik
 * Date: 6/27/2017
 * Time: 7:32 PM
 */
include_once dirname(dirname(__FILE__)) . '/Logic/genie_GeneralUsage.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DataBase.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_CMSSpecials.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_DynamicCreation.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_MailChimp.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Filters.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_ModelHelper.php';
include_once dirname(dirname(__FILE__)) . '/Logic/genie_Template.php';
//genie_CMSSpecials
class genie_FormHelper
{

    public function CheckFilterEmpty($filter)
    {
        $filterNotEmpty = false;
        if($filter!=null)
        {
            $filterRow=$filter['data'];
            foreach($filter as $key=>$value)
            {
                if(is_string($value) && trim($value)!="")
                {
                    $filterNotEmpty=true;
                }
            }
        }
        return $filterNotEmpty;
    }
}