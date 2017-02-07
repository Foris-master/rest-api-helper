<?php
/**
 * Created by PhpStorm.
 * User: evari
 * Date: 2/7/2017
 * Time: 10:16 PM
 */

namespace Foris\RestApiHelper\Facades;

use Illuminate\Support\Facades\Facade;



class RestApiHelperFacade extends Facade
{
    protected static function getFacadeAccessor() {
        return 'foris-rest-api-helper';
    }

}