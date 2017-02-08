<?php
/**
 * Created by PhpStorm.
 * User: evari
 * Date: 2/8/2017
 * Time: 11:06 AM
 */

namespace Foris\RestApiHelper;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FactoryHelper
{

    public static function getOrCreate($M, $new = false)
    {
        $ms = $M::get();
        $lenms = count($ms);
        if ($lenms == 0 || $new) {
            $m = factory($M)->create();
        } else {
            $m = $ms[rand(0, $lenms - 1)];
        }
        return $m;
    }
    public static function fakeFile( $faker, $src)
    {
        $dst = "storage/app/img/" . $src;
        if (!is_dir($dst))
            mkdir($dst, 0777, true);
        $path = $faker->file("public/seeds/" . $src . "/", $dst);
        $path = explode("storage/app", $path)[1];
        return $path;
    }
    public static function NewGuid($ext)
    {
        $s = strtoupper(md5(uniqid(rand(), true)));
        $guidText =
            substr($s, 0, 8) . '-' .
            substr($s, 8, 4) . '-' .
            substr($s, 12, 4) . '-' .
            substr($s, 16, 4) . '-' .
            substr($s, 20);
        return $guidText . '.' . $ext;
    }
    public static function getOrCreateMorph($array)
    {
        return self::getOrCreate($array[rand(0,count($array)-1)]);
    }
    public static function clear()
    {
        $directory = "storage/app/img";
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tableNames = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tableNames as $name) {
            //if you don't want to truncate migrations
            if ($name == 'migrations') {
                continue;
            }
//            DB::table($name)->delete();
            DB::table($name)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        self::recursiveRemoveDirectory($directory);
    }
    private static function recursiveRemoveDirectory($directory)
    {
        foreach (glob("{$directory}/*") as $file) {
            if (is_dir($file)) {
                self::recursiveRemoveDirectory($file);
            } else {
                unlink($file);
            }
        }
//        rmdir($directory);
    }
    public static function recurse_copy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

}