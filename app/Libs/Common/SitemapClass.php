<?php
    namespace App\Libs\Common;


    use Illuminate\Pagination\LengthAwarePaginator;

    /**
     * サイトマップのクラス
     * Class CmsClass
     * @package App\Libs
     */
    class SitemapClass
    {
        public $url;
        public $read;
        public $xml = "";
        public $count = 0;
        public $output = "";

        /**
         * 初期設定
         */
        public function __construct()
        {
            $this->url = env("APP_URL");
            //$this->url = "https://tejima.jp";

            $this->xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<!--  created with free sitemap generation system www.sitemapxml.jp  -->

';


            $this->output = public_path("sitemap.xml");
        }

        /**
         * サイトマップの作成
         */
        public function create() : void
        {
            $this->getContent($this->url);
            $this->xml .="</urlset>";

            file_put_contents($this->output, $this->xml, LOCK_EX);
        }

        public function getContent(string $url) : void
        {

            if (isset($this->read[base64_encode($url)])){
                //既に読み込み済み
                return;
            }
            $this->read[base64_encode($url)] = true;

            $html = file_get_contents($url);
            $urls = explode("/", $url);
            $dir = $urls[1];

            if(isset($html) && $html !== 0){
                preg_match_all('/<a [^>]*?href="([^"]+?)"[^>]*>/i', $html, $anchors);

                //スラッシュの個数でプロパティを決める
                $countPriority = count(explode("/", $url));
                $countPriority = round(1.0 + ((3 - $countPriority) / 10), 1);

                //XMLの生成
                $this->xml.= '<url>
  <loc>' . $url . '</loc>
  <priority>' .  $countPriority . '</priority>
</url>
';

                foreach($anchors[1] as $a) {
                    $anchor = preg_replace('/([^#]+)(#.*){0,1}/', '$1', $a);
                    $key = base64_encode($anchor);

                    //テスト
                    /*if ($this->count >= 100){
                        exit();
                    }*/


                    if (!isset($this->read[$key])){
                        //既に読み込まれていないか？
                        if (strpos($anchor, $this->url) !== FALSE){
                            //自サイトか？
                            $this->count++;
                            $this->getContent($anchor);
                        }
                    }

                    $this->read[$key] = true;
                }

            }
        }

        public function commandStart()
        {
            $command = base_path() . "/artisan command:sitemap";
            $php = env("PHP_PATH");

            exec($php . ' ' . $command . ' > /dev/null &');
        }
    }
