<?php
/**
 * Created by PhpStorm.
 * User: evari
 * Date: 2/7/2017
 * Time: 10:14 PM
 */

namespace Foris\RestApiHelper\Providers;
use Foris\RestApiHelper\RestApiHelper;
use Illuminate\Support\ServiceProvider;

class RestApiHelperServiceProvider  extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('foris-rest-api-helper', function() {
            return new RestApiHelper();
        });
    }

}