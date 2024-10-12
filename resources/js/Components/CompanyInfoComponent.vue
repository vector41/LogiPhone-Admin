<script setup>
import { onMounted, ref, watch, defineEmits } from "vue";
import { legal_personality, legal_personality_position, prefectures } from "@/constant/ConstantConfig";
import { getAddressData } from "@/constant/APIManager";
import Button from "primevue/button";
import { DepartmentItem } from "@/constant/DepartmentItem";

const props = defineProps({
    type: String,
    company_info_obj: Object,
});

const emit = defineEmits(['change_company','is_branch_contain'])

const created_id = ref(props.company_info_obj.created_id)
const updated_id = ref(props.company_info_obj.updated_id)
const company_base_id = ref(props.company_info_obj.company_base_id)
const personalities = ref(legal_personality);
const personality = ref();
const personality_positions = ref(legal_personality_position);
const personality_position = ref();
const company_name = ref(props.company_info_obj.company_name);
const is_company_branch = ref(props.company_info_obj.is_company_branch);
const departments = ref()
const is_company_branches = ref([
    { name: '営業所なし', value: 0, key: 'A' },
    { name: '営業所あり', value: 1, key: 'B' }
]);
const zip_1 = ref(props.company_info_obj.zip_1)
const zip_2 = ref(props.company_info_obj.zip_2)
const prefs = ref(prefectures)
const pref = ref()
const all_cities = ref([])
const cities = ref([])
const city = ref()
const town = ref(props.company_info_obj.town)
const building = ref(props.company_info_obj.building)
const id=ref(props.company_info_obj.id)

const active_depart = ref()

onMounted(() => {
    all_cities.value = JSON.parse(localStorage.getItem('cities'))
    pref.value = prefectures.filter(p=>p.id==props.company_info_obj.pref)[0];
    city.value = all_cities.value.filter(c=>c.id==props.company_info_obj.city)[0];
    personality.value = legal_personality.filter(p=>p.id==props.company_info_obj.personality)[0];
    personality_position.value = legal_personality_position.filter(p=>p.id==props.company_info_obj.personality_position)[0];

    if(props.company_info_obj.departments){
        var temp_departs = [];
        props.company_info_obj.departments.forEach(element => {
            var depart = new DepartmentItem();
            depart.id = element.id;
            depart.company_branch_id = element.company_branch_id;
            depart.department_name = element.department_name;        
            var children = [];
            element.children.forEach(ch=>{
                children.push({
                    id:ch.id,
                    name:ch.child_name
                })
            });
            depart.children = children;

            temp_departs.push(depart);
        });
        departments.value = temp_departs;
    }

    if(departments.value&&departments.value.length>0)active_depart.value = departments.value[0]
    console.log(' +++  ',props.company_info_obj,departments.value)

});

watch(pref,(newValue, oldValue) => {
    console.log('pref ', newValue, oldValue);
    cities.value = all_cities.value.filter(item=>item.prefecture==newValue.id)
})

const changeDepartment = (_depart_item)=>{
    console.log('depart_item ', _depart_item);
    active_depart.value = _depart_item;
}

</script>

<template>
    <div class="w-full h-full flex m-2 px-4 mx-auto overflow-auto">
        <div class="flex justify-start my-2 w-full overflow-auto rounded-sm bg-white overflow-y-auto">
            <table class="table margin-top w-fill" >
                <tr>
                    <th style="width: 11em;"> 法人形態 </th>
                    <td class="flex gap-2">
                        <Dropdown v-model="personality" :options="personalities"  option-label="name" placeholder="選択してください"
                            class="w-60" />                   
                        <Dropdown v-model="personality_position" :options="personality_positions" option-label="name"
                            placeholder="選択してください" class="w-60" />
                    </td>
                </tr>
                <tr>
                    <th> 会社名 </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="company_name" placeholder="例）東京運輸株式会社 ⇒ 東京運輸" />
                    </td>
                </tr>                
                <tr>
                    <th> 営業所有無 </th>
                    <td colspan="3">
                        <div class="flex flex-row p-1 gap-6">
                            <div v-for="is_item in is_company_branches" :key="is_item.key"
                                class="flex flex-row gap-2 align-items-center">
                                <RadioButton v-model="is_company_branch" :inputId="is_item.key" name="dynamic"
                                    :value="is_item.value" />
                                <label :for="is_item.key" class="ml-2">{{ is_item.name }}</label>
                            </div>
                        </div>
                    </td>
                </tr>                
                <tr v-show="is_company_branch==0">
                    <th> 住所 </th>
                    <td >
                        <div class="flex flex-col gap-2" >
                            <div class="flex flex-row items-center gap-2">
                                <InputText class="w-28" input-class="!w-28" v-model="zip_1" placeholder="100" />
                                <span> - </span>
                                <InputText class="w-28" input-class="!w-28" v-model="zip_2" placeholder="0005" />
                                <Button class="w-20 h-[34px] flex items-center justify-center">〒検索</Button>
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
                <tr v-show="is_company_branch==0">
                    <th> 部署設定 </th>
                    <td colspan="3">
                        <div class="flex items-center pt-3 gap-2">
                            <Button v-for="depart_item in departments" :key="depart_item.id" :label=depart_item.department_name severity="success" 
                                class="department-info-btn" :outlined="depart_item!==active_depart" @click="changeDepartment(depart_item)"/> 
                        </div>
                        <table v-if="active_depart&&active_depart.children" class="w-fill my-5">
                            <thead class="">
                                <th class="text-center py-2">
                                    部門名
                                </th>
                                <th class="w-[35%]">
                                </th>
                            </thead>
                            <tbody>
                                <tr v-for="sub_depart in active_depart.children" :key="sub_depart.id">
                                    <td>
                                        <InputText type="text" class="w-fill text-center" v-model="sub_depart.name" placeholder="例）東京運輸株式会社 ⇒ 東京運輸" />
                                    </td>
                                    <td>
                                        <div class="flex gap-2 justify-center items-center">
                                            <i class="pi pi-plus-circle" style="color: tomato;font-size: 1.5rem"></i>
                                            <i class="pi pi-minus-circle" style="color: green;font-size: 1.5rem"></i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

</style>
