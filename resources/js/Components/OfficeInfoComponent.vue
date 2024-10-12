<script setup>
import { onMounted, ref, watch, defineEmits } from "vue";
import {  prefectures } from "@/constant/ConstantConfig";
import { getAddressData } from "@/constant/APIManager";
import Button from "primevue/button";
import { DepartmentItem } from "@/constant/DepartmentItem";

const props = defineProps({
    type: String,
    office_info_obj: Object,
});

const emit = defineEmits(['change_company','is_branch_contain'])

const company_id = ref(props.office_info_obj.company_id)
const is_main_office = ref(props.office_info_obj.is_main_office)
const branch_name = ref(props.office_info_obj.branch_name)
const nickname = ref(props.office_info_obj.nickname);
const branch_types = ref([
    { name: '本社', id: 1, key:1 },
    { name: '支店', id: 0, key:2}
]);
const branch_type = ref();
const zip_1 = ref(props.office_info_obj.zip_1)
const zip_2 = ref(props.office_info_obj.zip_2)
const prefs = ref(prefectures)
const pref = ref()
const all_cities = ref([])
const cities = ref([])
const city = ref()
const town = ref(props.office_info_obj.town)
const building = ref(props.office_info_obj.building)
const tel = ref(props.office_info_obj.tel)
const fax = ref(props.office_info_obj.fax)
const id=ref(props.office_info_obj.id)
const departments = ref([]);

const active_depart = ref()

onMounted(() => {
    all_cities.value = JSON.parse(localStorage.getItem('cities'))
    pref.value = prefectures.filter(p=>p.id==props.office_info_obj.pref)[0];
    city.value = all_cities.value.filter(c=>c.id==props.office_info_obj.city)[0];
    console.log('is main office ', props);
    branch_type.value = branch_types.value.filter(b=>b.id==props.office_info_obj.is_main_office)[0].id;

    if(props.office_info_obj.departments){
        var temp_departs = [];
        props.office_info_obj.departments.forEach(element => {
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
    console.log(' office params  ',props.office_info_obj,departments.value,branch_type.value)

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
                    <th> 種 類 </th>
                    <td colspan="3">
                        <div class="flex flex-row p-1 gap-6">
                            <div v-for="branch_item in branch_types" :key="branch_item.key"
                                class="flex flex-row gap-2 align-items-center">
                                <RadioButton v-model="branch_type" :inputId="branch_item.key" name="dynamic"
                                    :value="branch_item.id" />
                                <label :for="branch_item.key" class="ml-2">{{ branch_item.name }}</label>
                            </div>
                        </div>
                    </td>
                </tr>   
                <tr>
                    <th> 営業所名 </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="branch_name" placeholder="" />
                    </td>
                </tr>       
                <tr>
                    <th> 社内通称名 </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="nickname" placeholder="" />
                    </td>
                </tr>            
                <tr>
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
                    <th> 電話 </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="tel" placeholder="" />
                    </td>
                </tr>   
                <tr>
                    <th> FAX </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="fax" placeholder="" />
                    </td>
                </tr>              
                <tr>
                    <th> 部 署 </th>
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
