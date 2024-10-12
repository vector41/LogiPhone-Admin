<?php
    namespace App\Libs\Common;


    use Illuminate\Support\Facades\Storage;
    use Matrix\Exception;

    /**
     * ファイルのクラス
     * Class CmsClass
     * @package App\Libs
     */
    class FileClass
    {
        public string $fileName = "";

        public function __construct(string $fileName="", string $uploadType="")
        {
            $this->fileName = $fileName;
            if (!$fileName){
                //空の場合はランダムファイル名
                $this->fileName = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 30);
            }

            if ($uploadType){
                $this->fileName = Storage::disk($uploadType)->path($this->fileName);
            }else{
                $this->fileName = $fileName;
            }
        }

        public function save(string $data)
        {
            $fp = fopen($this->fileName, "w");
            fwrite($fp, $data);
            fclose($fp);
        }

        public function getCsv()
        {
            $fp = new \SplFileObject($this->fileName);
            $fp->setFlags(\SplFileObject::READ_CSV);

            return $fp;
        }

        public array $results = [];

        public function getDirAll()
        {

            $this->getDir($this->fileName);

            return $this->results;
        }

        public function getDir(string $dir)
        {
            $results = [];

            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if (($file != ".") && ($file != "..") && ($file != ".DS_Store")){
                            if (is_dir($dir . "/" . $file)) {
                                $this->getDir($dir . "/" . $file);
                            }else{
                                $this->results[] = $dir . "/" . $file;
                            }
                        }
                    }
                    closedir($dh);
                }
            }

            return $results;
        }

        public function delete()
        {
            unlink($this->fileName);
        }

        public function base64Split(string $base64)
        {
            $data = explode("data:", $base64);
            list($mime, $base64) = explode(";base64,", $data[1]);

            return [$mime, $base64];
        }
    }
