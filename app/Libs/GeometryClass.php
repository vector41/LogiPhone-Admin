<?php
    namespace App\Libs;

    class GeometryClass{

        public function changeAddressToZip(string $address) : string
        {
            $url = 'https://map.yahooapis.jp/search/zip/V1/zipCodeSearch?appid=' . env("YAHOO_API_ID") . '&query=' . urlencode($address) . "&output=json";

            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
            );
            $ch = curl_init($url);
            curl_setopt_array($ch, $options);
            $json    = curl_exec($ch);
            $info    = curl_getinfo($ch);
            $errorNo = curl_errno($ch);
            curl_close($ch);

            if (!$errorNo){
                $json = json_decode($json, true);

                if (!empty($json["Feature"][0]["Name"])){
                    return str_replace("〒", "", $json["Feature"][0]["Name"]);
                }
            }

            return "";
        }


    }
?>
