<?php

namespace App\Http\ResponseHelpers;

use App\Enums\ClientPlatform;
use stdClass;

class CommonDataResponse{

    public function transform($result, $platform){

        // perform check on platform and return the co-responding asset

        if( $platform == ClientPlatform::GameArkMobile){
            // ignore since it's already returning for gameark
        }else{
            $newBoost = [];
            foreach ($result->boosts as $boostItem) {
                $d = new stdClass;
                $d->id = $boostItem->id;
                $d->name = $boostItem->name;
                $d->description = $boostItem->description;
                $d->point_value = $boostItem->point_value;
                $d->pack_count = $boostItem->pack_count;
                $d->currency_value = $boostItem->currency_value;
                $d->icon = str_ireplace("icons", "icons/cashingames_boosts", $boostItem->icon);

                $newBoost[] = $d;
            }

            $result->boosts = $newBoost;
        }

        return $result;
    }
}
