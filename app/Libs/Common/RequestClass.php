<?php

	namespace App\Libs\Common;

    use Illuminate\Foundation\Http\FormRequest;

    /**
	*	リクエストの処理の基本
	*/
	class RequestClass extends FormRequest
    {

        /**
         * 住所の整形
         * @param array|null $address
         * @return array
         */
        public function address(?array $address) : array
        {
            $default = [
                "zip" => "000-0000",
                "prefecture" => 0,
                "city" => 0,
                "other" => "",
                "building" => "",
            ];

            if (!$address){
                return $default;
            }

            $result = [];

            if (array_key_exists("zip", $address)){
                $result["zip"] = $address["zip"] ? strval($address["zip"]) : $default["zip"];
            }
            if (array_key_exists("prefecture", $address)){
                $result["prefecture"] = $address["prefecture"] ? intval($address["prefecture"]) : $default["prefecture"];
            }
            if (array_key_exists("city", $address)){
                $result["city"] = $address["city"] ? intval($address["city"]) : $default["city"];
            }

            if (array_key_exists("other", $address)){
                $result["other"] = $address["other"] ? strval($address["other"]) : $default["other"];
            }
            if (array_key_exists("building", $address)){
                $result["building"] = $address["building"] ? strval($address["building"]) : $default["building"];
            }

            return $result;

        }

        /**
         * ファイルの整形
         * @param array|null $address
         * @return array
         */
        public function files(?array $files) : array
        {

            $default = [
                "file" => "",
                "origin" => "",
                "mode" => "",
                "old" => "",
            ];

            if (!$files){
                return $default;
            }

            $results = [];

            $results["file"] = array_key_exists("file", $files) ? $files["file"] : "";
            $results["origin"] = array_key_exists("origin", $files) ? $files["origin"] : "";
            $results["mode"] = array_key_exists("mode", $files) ? $files["mode"] : "";
            $results["old"] = array_key_exists("old", $files) ? $files["old"] : "";

            return $results;

        }

	}
