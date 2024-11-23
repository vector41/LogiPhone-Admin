<?php

namespace App\Libs;
use App\Models\Dispatches\DispatchBoardChild;
use App\Models\Loads\Load;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoadClass
{
    public bool $isContract = false;
    public array $mapTypes = ["receptions", "waiting_products", "loadings", "rests", "waiting_arrivals", "landings"];

    public function getWithMapAll($relation="", $fromRelation="")
    {
        $results = [];

        foreach ($this->mapTypes as $map){
            $item = $map;

            if ($relation){
                $item .= "." . $relation;
            }

            if ($fromRelation){
                $item = $fromRelation . "." . $item;
            }

            $results[] = $item;
        }

        return $results;
    }

    public function addInvoiceDetail($Model, $data, $parent, $user)
    {
        if ($data){
            $Group = new GroupClass();
            $Group->notDelete($Model, $data, $parent, $user);

            foreach ($data as $invoice){
                $ModelUpdate = $Model::firstOrNew(["id" => getVariable($invoice, "id")]);

                foreach ($parent as $index => $value){
                    $ModelUpdate->$index = $value;
                }

                $ModelUpdate->price = $invoice["price"];
                $ModelUpdate->comment = getVariable($invoice, "comment");
                $ModelUpdate->save();
            }
        }
    }

}
