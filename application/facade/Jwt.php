<?php
/**
 * Created by PhpStorm.
 * User: shaoguo
 * Date: 2018/4/9
 * Time: 下午4:05
 */

namespace app\facade;
use think\Facade;

class Jwt extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\plugins\jwt\JWT';
    }
}