<?php

namespace App\Http\ResponseHelpers;

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
            $d->pack_count = $boostItem->pack_count;
            $d->currency_value = $boostItem->price;
            $d->count = is_null($boostItem->count) ? 0 : $boostItem->count;
            $d->icon = str_ireplace("icons", "icons", $boostItem->icon);

            $newBoost[] = $d;
        }

        $result->boosts = $newBoost;

        return $result;
    }
}