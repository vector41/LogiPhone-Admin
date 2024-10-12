<?php
    namespace App\Libs\Common;
    use Illuminate\Support\Facades\DB;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


    class ExcelClass
    {

        function __construct()
        {
        }

        public function export(object $Excel){

            $Excel->loadTemplate();

            $variables = $Excel->getVariable();

            //エクセルの変数の置換と挿入


            $Excel->export($variables);
        }

        public function import(string $fileName, $Excel){
            $Excel->loadTemplate();

            $variables = $Excel->getVariable();


                $Excel->import($fileName, $variables);

                if ($Excel->errors){
                    \Log::info($Excel->errors);
                    //エラーがある場合はコミットさせない
                    //$Excel->Model->sessionClear();
                    DB::rollback();
                }
            //編集モードの保持
            //\Session::put("editType". get_class($Excel), $Excel->editType);
        }

        public function done(object $Excel)
        {

            $Excel->Model->setTransaction("Done時のエラー", function() use($Excel){
                $editType = \Session::get("editType". get_class($Excel));
                $datas = $Excel->Model->sessionGet();
                if ($editType == 4){
                    //全件削除し再登録させる
                    $ModelDelete = clone $Excel->Model;

                    $ModelDelete = $ModelDelete->where("id", "<>", "0");

                    /*$data = array_shift($datas);

                    if (!empty($Excel->reflashDeleteId)){
                        $ModelDelete = $ModelDelete->where($Excel->reflashDeleteId, $data[$Excel->reflashDeleteId]);
                    }

                    $ModelDelete->delete();*/

                    $ModelDelete->truncate();
                }


                foreach ($datas as $data){
                    if ($editType != 3){
                        //配列の場合は削除して別DBに保存する
                        if ($editType == 2) {
	                        if (isset($Excel->children)){
		                        foreach ($Excel->children as $keyChild => $serialCode){
		                            //編集モード(子供の場合は一度削除する必要がある)
		                            if (isset($Excel->ChildModel)){
		                                $ChildModel = clone $Excel->ChildModel;

		                                $deletes = $ChildModel->where($serialCode, $data[$serialCode])->get();

		                                foreach ($deletes as $delete){
		                                    //echo $delete->id . "/" . $delete->pack_code . "<br />";
											$delete->delete();
		                                }
		                            }
		                        }
		                    }


                        }

                        foreach ($data->toArray() as $key => $values){
                            if (isset($Excel->children)){


                                foreach ($Excel->children as $keyChild => $serialCode){
                                    if ($editType == 2) {
	                                    //編集モード(子供の場合は一度削除する必要がある)


                                        foreach ($data->$keyChild as $child){
	                                        //echo "save";
                                            $child->savePlus();

                                        }

                                        if ($data[$serialCode] == "FC2-046-WH"){
	                                        //dd("W");
                                        }

                                    }else{
                                        foreach ($data->$keyChild as $key => $child){

                                            $child->savePlus();
                                        }
                                    }

                                    unset($data->$keyChild);
                                }

                            }
                        }

                        if ($editType == 2){
                            //編集モード
                            if (!empty($data->date_delete)){
                                $data->deleted_at = date("Y-m-d", strtotime($data->date_delete . "/01"));
                            }
                            unset($data->date_delete);

                            $data->updatePlus($Excel->primaryKey);
                        }else{
                            if (!empty($data["date_delete"])){
                                $data->deleted_at = date("Y-m-d", strtotime($data->date_delete . "/01"));
                            }
                            unset($data["date_delete"]);

                            $data->savePlus();
                        }



                    }else{
                        //削除処理
                        foreach ($data->toArray() as $key => $values) {

                            if (is_array($data[$key])) {

                                //子供がある場合はそれも削除
                                foreach ($values as $value){
                                    foreach ($Excel->primaryKey as $primaryKey) {
                                        $value = $value->where($primaryKey, $data->$primaryKey);
                                    }
                                }


                                $value->delete();
                            }



                            $data2 = clone $data;
                            foreach ($Excel->primaryKey as $primaryKey){
                                $data2 = $data2->where($primaryKey, $data->$primaryKey);
                            }

                            $data2->deletePlus();
                        }
                    }
                }
            });

            \Session::put("editType". get_class($Excel), "");
            $Excel->Model->sessionClear();

        }

        /**
         * 編集モードの処理
         * @param $data
         */
        public function update($data){

        }

    }
