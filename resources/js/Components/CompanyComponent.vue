<script setup>
import { onMounted, ref, watch, defineEmits } from "vue";
import { IMAGE_ROOT_PATH, legal_personality, legal_personality_position, prefectures } from "@/constant/ConstantConfig";
import { getAddressData } from "@/constant/APIManager";
import Button from "primevue/button";
import Textarea from "primevue/textarea";
import ImageUploadField from "./ImageUploadField.vue";
import { CompanyItem } from "@/constant/CompanyItem";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    type: String,
    company_obj: Object,
});

const emit = defineEmits(['change_company','is_branch_contain'])

const personalities = ref(legal_personality);
const personality = ref();
const personality_positions = ref(legal_personality_position);
const personality_position = ref();
const company_name = ref(props.company_obj.company_name);
const company_name_kana = ref(props.company_obj.company_name_kana);
const keyword = ref();
const is_company_branches = ref([
    { name: '支店無し', status: 0, key: 'A' },
    { name: '支店有り', status: 1, key: 'B' }
]);
const is_company_branch = ref()
const phone_1 = ref(props.company_obj.phone_1)
const phone_2 = ref(props.company_obj.phone_2)
const phone_3 = ref(props.company_obj.phone_3)
const fax_1 = ref(props.company_obj.fax_1)
const fax_2 = ref(props.company_obj.fax_2)
const fax_3 = ref(props.company_obj.fax_3)
const zip_1 = ref(props.company_obj.zip_1)
const zip_2 = ref(props.company_obj.zip_2)
const prefs = ref(prefectures)
const pref = ref()
const all_cities = ref([])
const cities = ref([])
const city = ref()
const town = ref(props.company_obj.town)
const building = ref(props.company_obj.building)
const memo = ref(props.company_obj.memo)
const id=ref(props.company_obj.id)

const imageObjs = ref([])
const removable_status = ref(false)

const toast = useToast();

onMounted(async() => {
    console.log(' +++  ',props.company_obj)
    all_cities.value = JSON.parse(localStorage.getItem('cities'))
    pref.value = prefectures.filter(p=>p.id==props.company_obj.pref)?
                prefectures.filter(p=>p.id==props.company_obj.pref)[0]:null;
    city.value = all_cities.value.filter(c=>c.id==props.company_obj.city)?
                all_cities.value.filter(c=>c.id==props.company_obj.city)[0]:null;
    personality.value = legal_personality.filter(p=>p.id==props.company_obj.personality)?
                legal_personality.filter(p=>p.id==props.company_obj.personality)[0]:null;
    personality_position.value = legal_personality_position.filter(p=>p.id==props.company_obj.personality_position)?
                                legal_personality_position.filter(p=>p.id==props.company_obj.personality_position)[0]:null;
    is_company_branch.value = props.company_obj.is_company_branch??is_company_branches.value[0].status;
    console.log('is_company_branch.value ',is_company_branch.value)
    if(props.company_obj.imageObjs.length==0){
        imageObjs.value.push({id:1, file:null})
    }else{
        imageObjs.value = [];
        props.company_obj.imageObjs.map((item, index)=>{
            imageObjs.value.push({id:index+1, file:IMAGE_ROOT_PATH+props.company_obj.imageObjs[index]});
        })
        // await Promise.all(
        //     props.company_obj.imageObjs.map(async(item, index)=>{
        //         console.log('------',props.company_obj.imageObjs[index])
        //         const file_res = await fetch(IMAGE_ROOT_PATH+props.company_obj.imageObjs[index]);
        //         const blob = await file_res.blob();
        //         const name = 'image'+(index+1)+'.png';
        //         const img_file = new File([blob], name, { type: blob.type });
        //         imageObjs.value.push({id:index+1, file:img_file});
        //     })
        // )
    }
    console.log('imageObjs ... ',imageObjs.value)
});

watch(pref,(newValue, oldValue) => {
    console.log('pref ', newValue, oldValue);
    cities.value = all_cities.value.filter(item=>item.prefecture==newValue.id)
    console.log('cities ', cities.value);
    console.log('imageObjs .000.. ',imageObjs.value)
})

watch(is_company_branch,(newValue, oldValue) => {
    console.log('dfdfdf ', newValue)
    var branch_contain_status =  is_company_branches.value.filter(item=>item.status==newValue)[0].status;
    console.log('is_company_branch ', newValue, oldValue,branch_contain_status);
    emit('is_branch_contain',branch_contain_status);
    console.log('imageObjs .1111.. ',imageObjs.value)
})

watch([ personality,personality_position,company_name,company_name_kana,keyword,
        is_company_branch,phone_1,phone_2,phone_3,fax_1,fax_2,fax_3,
        zip_1,zip_2,pref,city,town,building,memo,imageObjs],(newValue, oldValue) => {
    var company_obj = new CompanyItem(personality.value,
        personality_position.value,
        company_name.value,company_name_kana.value,
        keyword.value,
        is_company_branch.value,
        phone_1.value,
        phone_2.value,
        phone_3.value,
        fax_1.value,
        fax_2.value,
        fax_3.value,
        zip_1.value,
        zip_2.value,
        pref.value,
        city.value,
        town.value,
        building.value,
        memo.value,
        imageObjs.value,
        id.value);
    emit('change_company',company_obj);
    console.log('imageObjs ..2222. ',imageObjs.value)
})


const checkPhone = (event)=>{
    if (event.key.length === 1 && isNaN(parseInt(event.key))) {
        console.log('number key ... ')
        event.preventDefault();
    }
}

const searchAddress = async () =>{
    if(zip_1.value==''||zip_2.value=='') {
        toast.add({ severity: 'warn', summary: 'お知らせ', detail: 'zipコードを入力してください。', life: 2000 });
        return;
    }
    let zip_param = zip_1.value+'-'+zip_2.value;
    let address_data = await getAddressData(zip_param);
    if(address_data==null||address_data.area==null)  {
        toast.add({ severity: 'warn', summary: 'お知らせ', detail: '住所データがありません。', life: 2000 });
        return;
    }
    pref.value = prefs.value.filter(item=>item.id== address_data.area.prefecture)[0];
    cities.value = all_cities.value.filter(item=>item.prefecture==address_data.area.prefecture)
    city.value = all_cities.value.filter(item=>item.id==address_data.area.city)[0];
    town.value = address_data.area.town_name;
}

const addImgField = () =>{
    let max_obj = imageObjs.value.reduce((prev, current)=>{
        return (prev.id>current.id)?prev:current;
    })
    let temp_objs = [];
    imageObjs.value.map(it=>temp_objs.push(it));
    let new_id = max_obj.id+1;
    temp_objs.push({id:new_id,file:null});
    imageObjs.value = temp_objs;
    console.log('imgobjects ', imageObjs.value);
    removable_status.value = imageObjs.value.length>1?true:false;
}

const removeImgField = (_id) =>{
    imageObjs.value = imageObjs.value.filter(it=>it.id!=_id);
    console.log('imgobjects ', imageObjs.value,_id);
    removable_status.value = imageObjs.value.length>1?true:false;
}

const assignFile = (assign_data) =>{
    let tempobj = [];
    imageObjs.value.map(it=>{
        if(assign_data.id==it.id) it.file = assign_data.file;
        tempobj.push(it);
    });
    imageObjs.value= tempobj;
    console.log('assignFile',assign_data,imageObjs.value)
}

</script>

<template>
    <div class="w-full flex m-2 px-4 mx-auto overflow-auto">
        <div class="flex justify-start my-2 w-full overflow-auto rounded-sm bg-white overflow-y-auto">
            <table class="table margin-top w-fill" >
                <tr>
                    <th style="width: 11em;"> 法人形態 </th>
                    <td >
                        <Dropdown v-model="personality" :options="personalities"  option-label="name" placeholder="選択してください"
                            class="w-fill !relative" />
                    </td>
                    <th style="width: 9em;">法人形態の位置</th>
                    <td class="w-[35%]">
                        <Dropdown v-model="personality_position" :options="personality_positions" option-label="name"
                            placeholder="選択してください" class="w-fill" />
                    </td>
                </tr>
                <tr>
                    <th> 会社名 </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="company_name" placeholder="例）東京運輸株式会社 ⇒ 東京運輸" />
                    </td>
                    <th> 会社名(かな) </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="company_name_kana" placeholder="例）とうきょううんゆ" />
                    </td>
                </tr>
                <tr>
                    <th> 検索キーワード </th>
                    <td colspan="3">
                        <InputText type="text" class="w-full" v-model="keyword" placeholder="例）東京　運輸 スペース区切りで複数登録可能" />
                    </td>
                </tr>
                <tr>
                    <th> 支店 </th>
                    <td colspan="3">
                        <div class="flex flex-row p-1 gap-6">
                            <div v-for="is_item in is_company_branches" :key="is_item.key"
                                class="flex flex-row gap-2 align-items-center">
                                <RadioButton v-model="is_company_branch" :inputId="is_item.key" name="dynamic"
                                    :value="is_item.status" />
                                <label :for="is_item.key" class="ml-2">{{ is_item.name }}</label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr v-show="is_company_branch==false">
                    <th> 電話 </th>
                    <td>
                        <div class="flex flex-row items-center gap-2">
                            <InputText class="w-28" input-class="!w-28" v-model="phone_1" @keydown="checkPhone($event)"/>
                            <span> - </span>
                            <InputText class="w-28" input-class="!w-28" v-model="phone_2" @keydown="checkPhone($event)" />
                            <span> - </span>
                            <InputText class="w-28" input-class="!w-28" v-model="phone_3" @keydown="checkPhone($event)" />
                        </div>
                    </td>
                </tr>
                <tr v-show="is_company_branch==false">
                    <th> FAX </th>
                    <td>
                        <div class="flex flex-row items-center gap-2">
                            <InputText class="w-28" input-class="!w-28" v-model="fax_1" @keydown="checkPhone($event)" />
                            <span> - </span>
                            <InputText class="w-28" input-class="!w-28" v-model="fax_2" @keydown="checkPhone($event)" />
                            <span> - </span>
                            <InputText class="w-28" input-class="!w-28" v-model="fax_3" @keydown="checkPhone($event)" />
                        </div>
                    </td>
                </tr>
                <tr v-show="is_company_branch==false">
                    <th> 所在地 </th>
                    <td >
                        <div class="flex flex-col gap-2" >
                            <div class="flex flex-row items-center gap-2">
                                <InputText class="w-28" input-class="!w-28" v-model="zip_1" placeholder="100" @keydown="checkPhone($event)"/>
                                <span> - </span>
                                <InputText class="w-28" input-class="!w-28" v-model="zip_2" placeholder="0005" @keydown="checkPhone($event)"/>
                                <Button class="w-20 h-[34px] flex items-center justify-center text-white" @click="searchAddress">〒検索</Button>
                            </div>
                            <div class="flex flex-row items-center gap-2">
                                <Dropdown v-model="pref" :options="prefs" option-label="value"
                                    placeholder="都道府県を選択" class="w-56" />
                                <span> - </span>
                                <Dropdown v-model="city" :options="cities" option-label="value"
                                    placeholder="市区町村を選択" class="w-56" />
                            </div>
                            <div class="flex flex-row items-center gap-2">
                                <InputText class="w-fill" v-model="town" placeholder="それ以降の住所"/>
                            </div>
                            <div class="flex flex-row items-center gap-2">
                                <InputText class="w-fill" v-model="building" placeholder="建物名"/>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th> 法人メモ </th>
                    <td>
                        <Textarea v-model="memo" class="w-fill" rows="4" />
                    </td>
                </tr>
                <tr>
                    <th> 法人資料 </th>
                    <td colspan="3">
                        <div class="flex gap-4">
                            <ImageUploadField v-for="img_item in imageObjs" :key="img_item.id" :field_id="img_item.id" :img_file="img_item.file" :one_status="removable_status" @assign_file = "assignFile" @add_obj="addImgField" @remove_obj="removeImgField"/>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</template>
<style scoped>
.w-fill{
    width: -webkit-fill-available;
}
th{
    background-color: #ebebeb
}
th,
td {
    border-left: solid 2px white;
    border-right: solid 2px white;
    white-space: nowrap;
}

tbody {
    /* border: solid 2px rgb(59 130 246 / 0.5); */
}

tr {
    border-bottom: dotted 1px rgba(212, 212, 212, 0.5);
}

.border-1 {
    border-width: 1px;
}

.custom-row{
    margin: -15px 0;
}

@media only screen and (max-width: 860px) {
    #main-title {
        letter-spacing: 0px !important;
    }

    table {
        font-size: 13px !important;
    }

    #detail-result th,
    #detail-result td {
        min-width: 80px;
        padding: 0px 0px;
    }

    #detail-result th:first-child,
    #detail-result td:first-child {
        min-width: 92px;
    }

    .col-span-1 p {
        font-size: 14px !important;
        margin: 5px 15px !important;
    }
}

@media only screen and (max-width: 600px) {

    table {
        font-size: 12px !important;
    }

    #detail-result th,
    #detail-result td {
        min-width: 80px;
        padding: 0px 0px;
    }

    #detail-result th:first-child,
    #detail-result td:first-child {
        min-width: 92px;
    }

    .col-span-1 p {
        font-size: 14px !important;
        margin: 5px 0px !important;
    }
}
</style>
