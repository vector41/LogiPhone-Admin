<?php
    namespace App\Libs\Common;

    use Exception;
    use Faker\Provider\Image;

    /**
    *	画像関係の処理
    */
    class ImageClass{
        public $fileName;		    //画像ファイル名
        public $binary;		        //バイナリデータの場合
        public $sizes = [];
        public $extensions = [];

        public $width;
        public $height;
        public $body;
        public $mime;

        private bool $isOk  = true;
        public $isFile = false;
        /**
         * 初期設定
         * @param  string  $fileName    ファイル名
         * @param  string  $binary      又はバイナリデータ
         */
        public function __construct(string $fileName, string $binary="")
        {

            $this->sizes = config("image")["sizes"];
            $this->extensions = config("image")["extensions"];


            $this->fileName = $fileName;
            $this->binary = $binary;

            if ($this->binary){
                //バイナリから情報の取得
                try{
                    $this->body = imagecreatefromstring($this->binary);
                } catch (Exception $e){
                    $this->isOk = false;
                    return;
                }

                $imagesize = getimagesizefromstring($this->binary);


                $this->width = imagesx($this->body );
                $this->height = imagesy($this->body );
                $this->mime = $imagesize["mime"];

                if (!$this->mime){
                    //mimetypeが上記で取れない場合の処理
                    $finfo    = new \finfo(FILEINFO_MIME);
                    $mimetype = $finfo->buffer($this->binary);

                    $mimetypes = explode("; charset", $mimetype);

                    $this->mime = $mimetypes[0];
                }

            }else if ($fileName){
                //画像から情報の取得
                if (!file_exists($fileName)){
                    //画像がない場合
                    return false;
                }


                $this->isFile = true;



                $ImgInfo = getimagesize($fileName);

                $this->width = $ImgInfo[0];
                $this->height = $ImgInfo[1];

                $this->mime = $ImgInfo["mime"];

                if ($this->extensions[$this->mime] == 'gif'){
                    $this->body = imagecreatefromgif($fileName);
                }else if ($this->extensions[$this->mime] == 'png'){
                    $src = imagecreatefrompng($fileName);
                    $this->body = imagecreatetruecolor(imagesx($src),imagesy($src));

                    imagecopy($this->body,$src,0,0,0,0,imagesx($src),imagesy($src));
                }else if ($this->extensions[$this->mime] == 'webp'){
                    $this->body = imagecreatefromwebp($fileName);
                }else{
                    $body = \imagecreatefromjpeg($fileName);
                    $exif = \exif_read_data($fileName, 0, true);
                    $this->body = $this->rotate($body, $exif);
                    $this->width = imagesx($this->body);
                    $this->height = imagesy($this->body);
                }

            }
        }


        public function isOk() : bool
        {
            return $this->isOk;
        }

        public function isFile() : bool
        {
            return $this->isFile;
        }

        /**
         * 画像の拡大、縮小
         * @param  string  $size
         * @return ImageClass
         */
        public function scale($size) : ImageClass
        {
            $size = $this->sizes[$size];

            $ThumInfo = new static("", "");
            list($ThumInfo->width, $ThumInfo->height) = explode('x', $size);


            /*
            if (!$ThumInfo->height){
                if ($this->w > $this->arrayImgInfo[0]){
                    $this->w = $this->arrayImgInfo[0];
                }
            }
            */

            $isJudge = false;
            //片方のサイズを指定しない場合は最大サイズ以下にする
            if (!$ThumInfo->width){
                $isJudge = true;
                $ThumInfo->width = intval($this->width * ($ThumInfo->height / $this->height));
            }
            if (!$ThumInfo->height){
                $isJudge = true;
                $ThumInfo->height = intval($this->height * ($ThumInfo->width / $this->width));
            }



            //サムネイル用のイメージの作成
            if ($this->extensions[$this->mime] == 'gif'){
                $dst = imagecreate($ThumInfo->width, $ThumInfo->height);
            }else if ($this->extensions[$this->mime] == 'png'){
                $dst = imagecreatetruecolor($ThumInfo->width, $ThumInfo->height);
                //ブレンドモードを無効にする
                imagealphablending($dst, false);
                //完全なアルファチャネル情報を保存するフラグをonにする
                imagesavealpha($dst, true);
            }else{
                //それ以外はjpgに
                $dst = imagecreatetruecolor($ThumInfo->width, $ThumInfo->height);
            }

            $gw = $this->width / $ThumInfo->width;
            $gh = $this->height / $ThumInfo->height;

            //リサイズ開始

            if ($isJudge){
                //どちらかが0の場合
                if ($gw < $gh) {
                    $cut = ceil((($gh - $gw) * $ThumInfo->height) / 2);
                    imagecopyresampled($dst, $this->body, 0, 0, 0, $cut, $ThumInfo->width, $ThumInfo->height, $this->width, $this->height - ($cut * 2));
                }else if ($gh < $gw) {
                    $cut = ceil((($gw - $gh) * $ThumInfo->width) / 2);
                    imagecopyresampled($dst, $this->body, 0, 0, $cut, 0, $ThumInfo->width, $ThumInfo->height, $this->width - ($cut * 2), $this->height);
                }else{
                    imagecopyresampled($dst, $this->body, 0, 0, 0, 0, $ThumInfo->width, $ThumInfo->height, $this->width, $this->height);
                }
            }else{
                //両方共指定のある場合
                imagecopyresampled($dst, $this->body, 0, 0, 0, 0, $ThumInfo->width, $ThumInfo->height, $this->width, $this->height);
            }

            $ThumInfo->body = $dst;
            $ThumInfo->mime = $this->mime;

            return $ThumInfo;
        }

        public function extension() : string
        {
            return $this->extensions[$this->mime];
        }



        /**
         *	画像の表示
         */
        function view() : void
        {

            if ($this->extensions[$this->mime] == 'gif'){
                header("Content-type: image/gif");
            }else if ($this->extensions[$this->mime] == 'png') {
                header("Content-type: image/png");
            }else if ($this->extensions[$this->mime] == 'webp') {
                header("Content-type: image/webp");
            }else{
                header("Content-type: image/jpeg");
            }

            if ($this->extensions[$this->mime] == 'gif'){
                imagegif($this->body);
            }else if ($this->extensions[$this->mime] == 'png') {
                imagealphablending($this->body, false);
                //完全なアルファチャネル情報を保存するフラグをonにする
                imagesavealpha($this->body, true);
                imagepng($this->body);
            }else if ($this->extensions[$this->mime] == 'webp') {
                imagewebp($this->body);
            }else{
                imagejpeg($this->body);
            }

            imagedestroy($this->body);


        }

        /**
         * バイナリの取得
         * @return bool
         */
        public function getBinary() : string
        {
            ob_start();

            if ($this->extensions[$this->mime] == 'gif'){
                imagegif($this->body);
            }else if ($this->extensions[$this->mime] == 'png') {
                imagealphablending($this->body, false);
                //完全なアルファチャネル情報を保存するフラグをonにする
                imagesavealpha($this->body, true);
                imagepng($this->body);
            }else if ($this->extensions[$this->mime] == 'webp') {
                imagewebp($this->body);
            }else{
                imagejpeg($this->body);
            }

            $binary = ob_get_contents();
            ob_end_clean();

            return $binary;
        }


        /**
         * 画像を回転させる
         */
        private function rotate($src_image, $exif_data) {
            $degrees = 0;
            $mode    = '';

            if (isset($exif_data["IFD0"]["Orientation"])){
                $exif_data['Orientation'] = $exif_data["IFD0"]["Orientation"];
            }

            if (isset($exif_data['Orientation'])){
                switch($exif_data['Orientation'])
                {
                    case 2: // 水平反転
                        $mode = IMG_FLIP_VERTICAL;
                        break;
                    case 3: // 180度回転
                        $degrees = 180;
                        break;
                    case 4: // 垂直反転
                        $mode = IMG_FLIP_HORIZONTAL;
                        break;
                    case 5: // 水平反転、 反時計回りに270回転
                        $degrees = 270;
                        $mode    = IMG_FLIP_VERTICAL;
                        break;
                    case 6: // 反時計回りに270回転
                        $degrees = 270;
                        break;
                    case 7: // 反時計回りに90度回転（反時計回りに90度回転） 水平反転
                        $degrees = 90;
                        $mode    = IMG_FLIP_VERTICAL;
                        break;
                    case 8: // 反時計回りに90度回転（反時計回りに90度回転）
                        $degrees = 90;
                        break;
                }


                if (!empty($mode))
                {
                    imageflip($src_image, $mode);
                }
                if ($degrees > 0)
                {
                    $src_image = imagerotate($src_image, $degrees, 0);
                }



            }

            return $src_image;
        }


        /**
         * 画像の保存
         * @param  string  $fileName 保存のファイル名
         */
        function save(string $fileName){
            if ($this->extensions[$this->mime] == 'gif'){
                imagegif($this->body, $fileName);
            } else if ($this->extensions[$this->mime] == 'png'){
                imagepng($this->body, $fileName);
            } else if ($this->extensions[$this->mime] == 'webp'){
                imagewebp($this->body, $fileName);
            }else{
                imagejpeg($this->body, $fileName);
            }
        }

        /**
         * 画像種類の変更
         * @param  string  変更の種類
         */
        function change(string $mime){
            ob_start();

            if ($this->extensions[$mime] == 'webp'){
               imagewebp($this->body);
            } else if ($this->extensions[$this->mime] == 'png'){
                //imagepng($this->body, $fileName);
            } else if ($this->extensions[$this->mime] == 'webp'){
                //imagewebp($this->body, $fileName);
            }else{
                //imagejpeg($this->body, $fileName);
            }

            $buffer = ob_get_contents();
            ob_end_clean();

            $this->body = imagecreatefromstring($buffer);
            $this->mime = $mime;

        }

    }

