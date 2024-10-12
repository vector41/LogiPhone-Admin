<script setup>
import { onMounted, computed, ref, watch, defineEmits } from "vue";
import { prefectures } from "@/constant/ConstantConfig";
import { getAddressData } from "@/constant/APIManager";
import Button from "primevue/button";
import Textarea from "primevue/textarea";
import ImageUploadField from "./ImageUploadField.vue";
import { BranchItem } from "@/constant/BranchItem";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    branch_obj: Object,
    tab_id: Number,
    type: String
});

const emit = defineEmits('change_branch')
const branch_name = ref(props.branch_obj.branch_name);
const keyword = ref();
const branch_types = ref([
    { name: '本社', status: 1, key: 'A' },
    { name: '支店', status: 0, key: 'B' }
]);
const branch_type = ref(props.branch_obj.branch_type)
const phone_1 = ref(props.branch_obj.phone_1)
const phone_2 = ref(props.branch_obj.phone_2)
const phone_3 = ref(props.branch_obj.phone_3)
const fax_1 = ref(props.branch_obj.fax_1)
const fax_2 = ref(props.branch_obj.fax_2)
const fax_3 = ref(props.branch_obj.fax_3)
const zip_1 = ref(props.branch_obj.zip_1)
const zip_2 = ref(props.branch_obj.zip_2)
const prefs = ref(prefectures)
const pref = ref()
const all_cities = ref([])
const cities = ref([])
const city = ref()
const town = ref(props.branch_obj.town)
const building = ref(props.branch_obj.building)
const memo = ref(props.branch_obj.memo)
const infos = ref(props.branch_obj.infos)

const imageObjs = ref(props.branch_obj.imageObjs)
const removable_status = ref(false)
const id = ref(props.branch_obj.id)
const store_pos = ref(props.branch_obj.store_pos)

const toast = useToast();

onMounted(() => {
    all_cities.value = JSON.parse(localStorage.getItem('cities'))
    console.log('branch obj... ', props.branch_obj)
    branch_type.value = props.branch_obj.branch_type??branch_types.value[0];
    if(imageObjs.value.length==0) imageObjs.value.push({id:1, file:null})
    if(props.branch_obj.pref){
        pref.value = prefs.value.filter(p=>p.id==props.branch_obj.pref)[0];
        cities.value = all_cities.value.filter(item=>item.prefecture==props.branch_obj.pref)
    }
    if(props.branch_obj.city){
        city.value = all_cities.value.filter(c=>c.id==props.branch_obj.city)[0];
        console.log('branch city ', city.value)
    }
});

watch(pref,(newValue, oldValue) => {
    console.log('pref ', newValue, oldValue);
    cities.value = all_cities.value.filter(item=>item.prefecture==newValue.id)
    console.log('cities ', cities.value);
})


watch([branch_name,keyword,branch_type,phone_1,phone_2,phone_3,fax_1,fax_2,fax_3,
        zip_1,zip_2,pref,city,town, building,memo,infos,imageObjs, store_pos],(newValue, oldValue) => {
    var branch_obj = new BranchItem(branch_name.value,
        keyword.value,
        branch_type.value,
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
        infos.value,
        imageObjs.value,
        id.value,
        store_pos.value);
    emit('change_branch',{tab_id:props.tab_id,obj:branch_obj});
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
            <table :class="['table margin-top w-fill ', props.type=='update'?'branch-update':'']"  >
                <tr>
                    <th> 種 類 </th>
                    <td colspan="3">
                        <div class="flex flex-row p-1 gap-6">
                            <div v-for="type_item in branch_types" :key="type_item.key"
                                class="flex flex-row gap-2 align-items-center">
                                <RadioButton v-model="branch_type" :inputId="type_item.key" name="dynamic"
                                    :value="type_item.status" />
                                <label :for="type_item.key" class="ml-2">{{ type_item.name }}</label>
                            </div>
                        </div>
                    </td>
                </tr>
               <tr>
                    <th> 検索キーワード </th>
                    <td colspan="3">
                        <InputText type="text" class="w-full" v-model="keyword" placeholder="例）東京　運輸 スペース区切りで複数登録可能" />
                    </td>
                </tr>
                <tr>
                    <th> 支店名 </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="branch_name" placeholder="例）東京運輸株式会社 ⇒ 東京運輸" />
                    </td>
                </tr>
                <tr>
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
                <tr>
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
                <tr>
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
                                    placeholder="市区郡を選択" class="w-56" />
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
                    <th> 支店メモ </th>
                    <td>
                        <Textarea v-model="memo" class="w-fill" rows="4" />
                    </td>
                </tr>
                <tr>
                    <th> 店情報 </th>
                    <td>
                        <Textarea v-model="infos" class="w-fill" rows="4" />
                    </td>
                </tr>
                <tr>
                    <th> 法人資料 </th>
                    <td colspan="3">
                        <div class="flex gap-4">
                            <ImageUploadField v-for="img_item in imageObjs" :key="img_item.id" :field_id="img_item.id" :one_status="removable_status" @assign_file = "assignFile" @add_obj="addImgField" @remove_obj="removeImgField"/>
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
    background-color: #ebebeb;
    width: 200px;
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
