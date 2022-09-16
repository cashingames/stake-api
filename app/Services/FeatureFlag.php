<?php

namespace App\Services;

use App\Exceptions\UnknownFeatureException;

class FeatureFlag{

    /**
     * @param string $storage The different places where we could have stored the features to be toggled.
     * Values could include `config` i.e config file, `database` i.e a database table
     * 
     * @TODO implementation for other storage types aside config
     */
    public static $storage = "config";

    public static $features;
    
    public static function init($storage="config"){
        if ($storage == "config"){
            self::$features = config('features');
        }
        else{
            self::$features = config('features');
        }
    }

    public static function initialize($storage = "config")
    {
        if (self::$features == null) {
            self::init($storage);
        }
    }

    public static function isEnabled($feature){
        self::initialize();
        self::exists($feature);
        return config("features.{$feature}.enabled");
    }

    public static function isAnyEnabled(array $features_list){
        self::initialize();
        foreach ($features_list as $key => $feature) {
            if (self::isEnabled($feature)){
                return true;
            }
        }
    }

    public static function isAllEnabled(array $features_list){
        self::initialize();
        foreach ($features_list as $key => $feature) {
            if (!self::isEnabled($feature)) {
                return false;
            }
        }
        return true;
    }

    public static function enable($feature){
        self::initialize();
        self::exists($feature);
        config(["features.{$feature}.enabled" => true]);
    }

    public static function disable($feature){
        self::initialize();
        self::exists($feature);
        config(["features.{$feature}.enabled" => false]);
    }

    public static function exists($feature){
        // this will also be dependent of storage type later in the future
        if (array_key_exists($feature, self::$features)){
            return true;
        }
        throw new UnknownFeatureException("Unknow feature `{$feature}` specified", 1);
    }

}