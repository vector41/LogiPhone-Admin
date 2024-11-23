<?php
    namespace App\Libs;
    use App\Libs\Common\DateClass;
    use Illuminate\Support\Str;
    use App\Models\Chats\ChatOffice;
    use App\Models\Chats\ChatUnit;
    use App\Models\Companies\Company;
    use App\Models\Companies\CompanyBase;
    use App\Models\Companies\CompanyBranch;
    use App\Models\Companies\CompanyDepartment;

    class GroupClass{
        public $models = [];

        public function update($model, int $companyId, $data, array $parent=[], $officeWith = "", $Company = null)
        {
            $this->models = [];

            $this->updateAll($model,$companyId, $parent,$data,$officeWith, $Company);
        }

        public function updateAll($model, int $companyId, array $parent, $data, $officeWith = "", $Company = null)
        {


            $delete = $model::make()->newInstance();

            if ($companyId){
                $delete = $delete->where("company_branch_id", $companyId);
            }
            //存在しないデータの削除
            if ($parent){
                $delete = $delete->where($parent[0], $parent[1]);

                if (!empty($parent[2])){
                    $delete = $delete->where($parent[2], $parent[3]);
                }
            }


            if ($data){
                $delete->notDelete($data);
            }else{
                //空の場合は全削除
                $delete->delete();
            }

            /*if($officeWith == "office"){
                $this->notOfficeDelete(ChatOffice::class, $data);
            }

            if($officeWith == "unit"){
                $this->notUnitDelete(ChatUnit::class, $data);
            }*/

            $sort = 0;

            if ($data){
                foreach ($data as $item){
                    if ($item){
                        $id = 0;
                        if (!empty($item["id"])){
                            $id = $item["id"];
                        }

                        $modelUpdate = $model::make()->newInstance();
                        $modelUpdate = $modelUpdate->firstOrNew(["id" => $id]);


                        $attributes = [];

                        foreach ($item as $key => $value){
                            $attribute = "set" . ucfirst(Str::camel($key)) . "Attribute";

                            if (method_exists($modelUpdate, $attribute)) {
                                $attributes[] = [
                                    "attribute" => $key,
                                    "value" => $value,
                                ];
                            }else{
                                $modelUpdate->$key = $value;
                            }
                        }


                        //紐付け
                        if ($companyId){
                            $modelUpdate->company_id = $companyId;
                        }
                        if ($parent){
                            $parentKey = $parent[0];
                            $modelUpdate->$parentKey = $parent[1];

                            if (!empty($parent[2])){
                                $parentKey2 = $parent[2];
                                $modelUpdate->$parentKey2 = $parent[3];
                            }
                        }

                        $modelUpdate->sort = $sort;
                        $modelUpdate->surgeryModel();
                        $modelUpdate->save();

                        $this->models[class_basename(get_class($modelUpdate))][] = ["model" => $modelUpdate, "data" => $item];

                        if ($attributes){
                            foreach ($attributes as $attribute){
                                $attributeKey = $attribute["attribute"];
                                $modelUpdate->$attributeKey = $attribute["value"];
                            }
                            $modelUpdate->save();
                        }


                        if (isset($item["children_type"])){
                            $item["children"] = [];

                            foreach ($item["children_type"] as $types){
                                foreach ($types as $type){
                                    $item["children"][] = $type;
                                }
                            }
                        }

                        /*$chatOfficeModel = ChatOffice::firstOrNew(['xid'=>$modelUpdate->id]);
                        if($officeWith == "office"){
                            $chatOfficeModel->xid = $modelUpdate->id;
                            $chatOfficeModel->company = $Company->company_base_id;
                            $chatOfficeModel->name = $modelUpdate->branch_name;
                            $chatOfficeModel->companyName = $Company->company_name;
                            $chatOfficeModel->companyNameIndex = $Company->id;
                            $chatOfficeModel->save();
                        }

                        if($officeWith == "unit"){
                            $chatunitModel = ChatUnit::firstOrNew(['xid'=>$modelUpdate->id]);
                            $chatunitModel->xid = $modelUpdate->id;
                            $branchModel = CompanyBranch::where('id', $Company->company_branch_id)->first();
                            if($branchModel){
                                $companyModel = Company::where('id', $branchModel->company_id)->first();
                                $chatunitModel->company = $companyModel->company_base_id;
                                $chatunitModel->office = $Company->company_branch_id;
                                $chatunitModel->name = $modelUpdate->child_name;
                                $chatunitModel->save();
                            }
                        }*/

                        if (isset($item["children"])){
                            //下の階層がある場合は再実行
                            $modelChild = $model::make()->newInstance();
                            if($modelUpdate->department_name){
                                $this->updateAll($modelChild->children()->getModel(), 0, [Str::snake(class_basename($model)).'_id', $modelUpdate->id], $item["children"], "unit", $modelUpdate);
                            }else{
                            $this->updateAll($modelChild->children()->getModel(), 0, [Str::snake(class_basename($model)).'_id', $modelUpdate->id], $item["children"]);
                        }
                        }

                        $sort++;

                    }
                }
            }


        }


        public function getContact($Model)
        {
            $contact = [];

            if ($Model->contact_name_kana){
                $contact["contact_name_kana"] = $Model->contact_name_kana;
                $contact["contact_name"] = $Model->contact_name;
                $contact["contact_tel"] = $Model->contact_tel;
                $contact["contact_fax"] = $Model->contact_fax;
                $contact["contact_mobile"] = $Model->contact_mobile;
            }

            $Model->contact = $contact;

            return $Model;
        }
        public function saveContact($Model, $data)
        {

            if (!empty($data["contact"])){
                $Model->contact_name_kana = $data["contact"]["contact_name_kana"];
                $Model->contact_name = $data["contact"]["contact_name"];
                $Model->contact_tel = $data["contact"]["contact_tel"];
                $Model->contact_fax = $data["contact"]["contact_fax"];
                $Model->contact_mobile = $data["contact"]["contact_mobile"];

                $Model->save();
            }
        }

        /**
         * マップの更新
         * @param $Model
         * @param  array  $types
         * @param $data
         * @param  array  $parent
         * @param $user
         * @return void
         */
        public int $sortMap = 0;
        public function addMaps($Model,array $types, $data, array $parent, $user)
        {
            foreach ($types as $type){
                if (!empty($data[$type])){
                    $this->updateMap($Model, $type,$data[$type], $parent, $user);
                }
            }
        }

        public function updateMap($Model, string $type, $data, array $parent, $user)
        {
            //日付しか入っていないものは消す
            $types = ['min','time','time_range', 'prefecture', 'city', 'other', 'building','comment', 'map','company_name','person_name', 'tel', "perth"];

            $dataDeletes = $data;
            foreach ($dataDeletes as $index => $dataDelete){
                $isOk = false;
                foreach ($types as $typeData){
                    if (!empty($dataDelete[$typeData])){
                        $isOk = true;
                    }
                }

                if (!$isOk){
                    unset($data[$index]);
                }
            }

            //存在しないものは削除する
            $parent["type"] = $type;

            $this->notDelete($Model, $data, $parent, $user);


            if ($data){
                foreach ($data as $item){
                    if ($item){

                        $ModelUpdate = $Model::whereCompanyEmployeeBranch($user)->where("id", getVariable($item, "id"))->firstOrNew();

                        $attributes = [];

                        foreach ($item as $key => $value){
                            $attribute = "set" . ucfirst(Str::camel($key)) . "Attribute";

                            if (method_exists($ModelUpdate, $attribute)) {
                                $attributes[] = [
                                    "attribute" => $key,
                                    "value" => $value,
                                ];
                            }else{
                                $ModelUpdate->$key = $value;
                            }
                        }

                        //紐付け
                        $ModelUpdate->company_branch_id = $user->company_branch_id;
                        foreach ($parent as $index => $value){
                            $ModelUpdate->$index = $value;
                        }

                        if (!empty($item["map"])){
                            $map = $item["map"];

                            $ModelUpdate->prefecture = getVariable($map, "prefecture");
                            $ModelUpdate->city = getVariable($map, "city");
                            $ModelUpdate->other = getVariable($map, "other");
                            $ModelUpdate->building = getVariable($map, "building");
                            $ModelUpdate->comment = getVariable($map, "comment");
                            $ModelUpdate->map = getVariable($map, "map");
                            $ModelUpdate->company_name = getVariable($map, "company_name");
                            $ModelUpdate->person_name = getVariable($map, "person_name");
                            $ModelUpdate->tel = getVariable($map, "tel");
                        }

                        $ModelUpdate->type = $type;
                        $ModelUpdate->sort = $this->sortMap;

                        //$user

                        if ($ModelUpdate->time){
                            $time = arraySearchId(config("customs.delivery_time"), $ModelUpdate->time);
                            $key = "worktime_" . $type;

                            $DateClass = new DateClass();
                            $endTime = $DateClass->plus(substr($time["time"], 0, 2) . ":" . $ModelUpdate->min, $user->companyBranch->$key);

                            $ModelUpdate->end_time = date("H", strtotime($endTime));
                            $ModelUpdate->end_min = date("i", strtotime($endTime));

                        }

                        $ModelUpdate->surgeryModel();
                        $ModelUpdate->save();

                        $this->sortMap++;
                    }
                }
            }
        }

        public function notDelete($Model, $data, $parent, $user = null)
        {
            $Delete = $Model::make()->newInstance();

            if ($parent){
                foreach ($parent as $index => $value){
                    $Delete = $Delete->where($index, $value);
                }
            }

            if ($user){
                $Delete = $Delete->whereCompanyEmployeeBranch($user);
            }

            //存在しないデータの削除
            $Delete->notDelete($data);
        }

        public function notUnitDelete($Model, $data)
        {
            $Delete = $Model::make()->newInstance();
            $departmentId = "";
            foreach ($data as $model){
                if(isset($model['id'])){
                    $Delete = $Delete->where("xid", '<>', $model['id']);
                    $departmentId = $model['company_department_id'];
                }
            }

            $companyDepartment = CompanyDepartment::where('id', $departmentId)->first();
            if($departmentId != ""){
                $Delete->where('office', $companyDepartment->company_branch_id)->delete();
            }

        }

        public function notOfficeDelete($Model, $data)
        {
            $Delete = $Model::make()->newInstance();
            $companyId = "";
            foreach ($data as $model){
                if(isset($model['id'])){
                    $Delete = $Delete->where("xid", '<>', $model['id']);
                    $companyId = $model['company_id'];
                }
            }

            $companyDepartment = Company::where('id', $companyId)->first();
            if($companyId != ""){
                $Delete->where('company', $companyDepartment->company_base_id)
                       ->where('name', '<>', '本社')->delete();
            }

        }
    }
?>
