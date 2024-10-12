<?php

	namespace App\Libs\Common;

	use App\Models\Users\User;

    /**
	*	Lineの処理
	*/
	class LineClass{
		public $channelMessageId = "1655168206";
		public $secretMessage = "f33d9c43a7414f55352c70a571cb700b";


		/**
		 * アクセストークン取得
		 * @param  string  $type
		 * @return string
		 */
		public function getAccessToken(string $type="login") : string
		{
			$channelId = $this->channelMessageId;
			$secret = $this->secretMessage;


			$data = array(
				'grant_type' => 'client_credentials',
				'client_id' => $channelId,
				'client_secret' => $secret,
			);
			$header = array(
				"Content-Type: application/x-www-form-urlencoded",
			);
			$options = array('http' => array(
				'method' => 'POST',
				'header'  => implode("\r\n", $header),
				'content' => http_build_query($data)
			));

			$response = file_get_contents(
				"https://api.line.me/v2/oauth/accessToken",
				false,
				stream_context_create($options)
			);

			return json_decode($response)->access_token;
		}


        /**
         * メッセージ送信
         * @param $to
         * @param $message
         * @param bool $isSns
         */
		public function sendMessage($to, $message, $isSns=false, $isJudg=true)
		{
			$message = strip_tags($message);

            /*
			if ($isJudg){
			    if ((!$isSns)){
			        //SNの特殊処理
			        $User = new User();
                    $User->company_id
	                $CompanyClass = app("CompanyClass");
			        $customer = $Customer->whereSns($to)->first();
	                $company = $CompanyClass->getCompany($customer->id)->first();

	                if ($company){
	                    if ($CompanyClass->isSnGroup($company->id)){
	                        //SNには送付させない
	                        return;
	                    }
	                }
	            }

			}*/

			$access = $this->getAccessToken("message");

			//タグで削除{{\/messagePreview}}
			if (strpos($message, '{{messagePreview}}') !== FALSE){

				$message = preg_replace_callback("/{{messagePreview}}(.*?){{\/messagePreview}}/", function($data){
					return "";
				}, str_replace("\n", "\n", $message));

				$text = str_replace("{{messagePreview}}", "", $message);
				$message = str_replace("{{/messagePreview}}", "", $text);

			}

			//変換
			$message = preg_replace_callback('/(https?|http)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/', function($datas){
				$addUrl = "openExternalBrowser=1";

				if (strpos($datas[0], "?")){
					$addUrl = "&" . $addUrl;
				}else{
					$addUrl = "?" . $addUrl;
				}

				return $datas[0] . $addUrl;
			}, $message);

			$datas = [
				"to" => [$to],
				"messages" => [
					[
						"type" => "text",
						"text" => $message
					]
				]
			];



			$json = json_encode($datas);

			$curl = "curl -v -X POST https://api.line.me/v2/bot/message/multicast -H 'Content-Type: application/json' -H 'Authorization: Bearer " .
				$access . "' -d '" . $json . "'";



			exec($curl . " > /dev/null &");

		}

	}
