<?php
/**
 * Created by PhpStorm.
 * User: evari
 * Date: 2/8/2017
 * Time: 11:02 AM
 */

namespace Foris\RestApiHelper;


class FormRequestHelper
{

    public static function get_rules($method,$rules,$ignoreid=[]){
        switch ($method) {
            case 'GET': {
                return $rules;
            }
            case 'DELETE': {
                return [];
            }
            case 'POST': {
                return $rules;
            }
            case 'PUT': {
                $prules=[];
                foreach ($rules as $key=>$rule) {
                    $tmp= str_replace('required|','',$rule);
                    $tmp= str_replace('|required','',$tmp);
                    $tmp= str_replace('required','',$tmp);
                    if (strpos($tmp, 'unique') !== false) {
                        //commence par | (\ pour echaper) ensuite nimporte koi apres la suite unique ensuite nimporte koi fini
                        //par |
                        /*$tmp = preg_replace('/\|(.*)unique(.*)\|/','|unique:'.$ignoreid[$key].'|',$tmp);
                        $tmp = preg_replace('/(.*[^\|])unique(.*)\|/','unique:'.$ignoreid[$key].'|',$tmp);
                        $tmp = preg_replace('/\|(.*)unique(.*[^\|])/','|unique:'.$ignoreid[$key],$tmp);*/
                        if(preg_match('/\|(.*)unique(.*[^\|])/', $tmp)){
                            $tab = explode('|',$tmp);
                            $tab[count($tab)-1]='|unique:'.$ignoreid[$key];
                            $tmp = implode('|',$tab);
                        }elseif(preg_match('/(.*[^\|])unique(.*)\|/', $tmp)){
                            $tab = explode('|',$tmp);
                            $tab[0]='unique:'.$ignoreid[$key].'|';
                            $tmp = implode('|',$tab);
                        }else{
                            $tmp = preg_replace('/\|(.*)unique(.*)\|/','|unique:'.$ignoreid[$key].'|',$tmp);
                        }
                    }
                    $prules[$key]= $tmp;
                }
                return $prules;
            }
            case 'PATCH': {
                return [
                ];
            }
            default:
                return [
                ];
        }
    }
}