<?php

namespace App\Http\ResponseHelpers;

use App\Enums\ClientPlatform;
use stdClass;

class CommonDataResponse
{

    public function transform($result)
    {
        $newBoost = [];
        foreach ($result->boosts as $boostItem) {
            $d = new stdClass;
            $d->id = $boostItem->id;
            $d->name = $boostItem->name;
            $d->description = $boostItem->description;
            $d->point_value = $boostItem->point_value;
            $d->pack_count = $boostItem->pack_count;
            $d->currency_value = $boostItem->currency_value;
            $d->count = is_null($boostItem->count) ? 0 : $boostItem->count;
            $d->icon = str_ireplace("icons", "icons/cashingames_boosts", $boostItem->icon);

            $newBoost[] = $d;
        }

        $result->boosts = $newBoost;

        return $result;
    }
}