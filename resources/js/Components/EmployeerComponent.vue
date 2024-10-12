<script setup>
import { onMounted, ref, watch, defineEmits,inject, computed } from "vue";
import { employeer_role_names, genders } from "@/constant/ConstantConfig";
import { getCompanyFromBranchData,getLPBranchDatas,getBranchData } from "@/constant/APIManager";
import ImageUploadField from "./ImageUploadField.vue";
import { EmployeerItem } from "@/constant/EmployeerItem";
const props = defineProps({
    type: String,
    employeer_obj: Object,
});

const created_id = ref(props.employeer_obj.created_id);
const updated_id = ref(props.employeer_obj.updated_id);
const company_id = ref(props.employeer_obj.company_id);
const company_branch_id = ref(props.employeer_obj.company_branch_id);
const department = ref(props.employeer_obj.department);
const person_name_second = ref(props.employeer_obj.person_name_second)
const person_name_first = ref(props.employeer_obj.person_name_first)
const person_name_second_kana = ref(props.employeer_obj.person_name_second_kana)
const person_name_first_kana = ref(props.employeer_obj.person_name_first_kana)
const position = ref(props.employeer_obj.position)
const is_representative = ref(props.employeer_obj.is_representative)
const is_board_member = ref(props.employeer_obj.is_board_member)
const tel1_1 = ref('')
const tel1_2 = ref('')
const tel1_3 = ref('')
const tel2_1 = ref('')
const tel2_2 = ref('')
const tel2_3 = ref('')
const tel3_1 = ref('')
const tel3_2 = ref('')
const tel3_3 = ref('')

const gender_items = ref(genders)
const gender = ref(props.employeer_obj.gender)

const role_items = ref(employeer_role_names)
const roles = ref(props.employeer_obj.roles)
const email = ref(props.employeer_obj.email)
const note = ref(props.employeer_obj.note)
const id= computed(()=>props.employeer_obj.id)
const cardImageObjs = ref(props.employeer_obj.cardImageObjs)
const licenseImageObjs = ref(props.employeer_obj.licenseImageObjs)
const removable_card_status = ref(false)
const removable_license_status = ref(false)

const company_datas = inject('company_datas')
const company_branch = ref();
const show_search_dialog = ref(false);
const lp_branches = ref([]);

const emit = defineEmits(['change_employeer']);

onMounted(async() => {
    console.log(' +++  ',props.employeer_obj)

    let tel1 = props.employeer_obj.tel1;
    if(tel1&&tel1.includes('-')){
        tel1.split('-').forEach((element, index) => {
            if(index==0)tel1_1.value = element;
            if(index==1)tel1_2.value = element;
            if(index==2)tel1_3.value = element;
        });
    }
    let tel2 = props.employeer_obj.tel2;
    if(tel2&&tel2.includes('-')){
        tel2.split('-').forEach((element, index) => {
            if(index==0)tel2_1.value = element;
            if(index==1)tel2_2.value = element;
            if(index==2)tel2_3.value = element;
        });
    }
    let tel3 = props.employeer_obj.tel3;
    if(tel3&&tel3.includes('-')){
        tel3.split('-').forEach((element, index) => {
            if(index==0)tel3_1.value = element;
            if(index==1)tel3_2.value = element;
            if(index==2)tel3_3.value = element;
        });
    }
    if(cardImageObjs.value.length==0) {
        cardImageObjs.value.push({id:1, file:null})
    }else{

    }
    if(licenseImageObjs.value.length==0){
        licenseImageObjs.value.push({id:1, file:null})
    }else{

    }

    is_representative.value = props.employeer_obj.is_representative==1?true:false;
    is_board_member.value = props.employeer_obj.is_board_member==1?true:false;

    if(props.employeer_obj.company_branch_id){
        let branch_item = await getBranchData(props.employeer_obj.store_pos,props.employeer_obj.company_branch_id);

    }

    console.log('imageobj... ', company_datas)
    const lp_response = await getLPBranchDatas();
    lp_branches.value = lp_response?lp_response.response:[];
    console.log('lp branches... ', lp_branches.value)
});

watch([ company_branch_id, department,person_name_second,person_name_first,
        person_name_second_kana,person_name_first_kana,position,is_representative,
        is_board_member,tel1_1,tel1_2,tel1_3,tel2_1,tel2_2,tel2_3,
        tel3_1,tel3_2,tel3_3,gender,roles,email,note,cardImageObjs,licenseImageObjs],(newValue, oldValue) => {

        let is_representative_val = is_representative.value==true?1:0;
        let is_board_member_val = is_board_member.value==true?1:0;
        var employeer_obj = new EmployeerItem(
            created_id.value,
            updated_id.value,
            company_id.value,
            company_branch_id.value,
            null,
            department.value,
            null,
            person_name_second.value,
            person_name_first.value,
            person_name_second_kana.value,
            person_name_first_kana.value,
            position.value,
            is_representative_val,
            is_board_member_val,
            tel1_1.value+'-'+tel1_2.value+'-'+tel1_3.value,
            tel2_1.value+'-'+tel2_2.value+'-'+tel2_3.value,
            tel3_1.value+'-'+tel3_2.value+'-'+tel3_3.value,
            gender.value,
            roles.value,
            email.value,
            note.value,
            cardImageObjs.value,
            licenseImageObjs.value,
            id.value,
            props.employeer_obj.store_pos);

    emit('change_employeer',employeer_obj);
})

const checkPhone = (event)=>{
    if (event.key.length === 1 && isNaN(parseInt(event.key))) {
        console.log('number key ... ')
        event.preventDefault();
    }
}

const addCardImgField = () =>{
    let max_obj = cardImageObjs.value.reduce((prev, current)=>{
        return (prev.id>current.id)?prev:current;
    })
    let temp_objs = [];
    cardImageObjs.value.map(it=>temp_objs.push(it));
    let new_id = max_obj.id+1;
    temp_objs.push({id:new_id,file:null});
    cardImageObjs.value = temp_objs;
    console.log('imgobjects ', cardImageObjs.value);
    removable_card_status.value = cardImageObjs.value.length>1?true:false;
}

const removeCardImgField = (_id) =>{
    cardImageObjs.value = cardImageObjs.value.filter(it=>it.id!=_id);
    console.log('imgobjects ', cardImageObjs.value,_id);
    removable_card_status.value = cardImageObjs.value.length>1?true:false;
}

const assignCardFile = (assign_data) =>{
    let tempobj = [];
    cardImageObjs.value.map(it=>{
        if(assign_data.id==it.id) it.file = assign_data.file;
        tempobj.push(it);
    });
    cardImageObjs.value= tempobj;
    console.log('assignFile',assign_data,cardImageObjs.value)
}

const addLicenseImgField = () =>{
    let max_obj = licenseImageObjs.value.reduce((prev, current)=>{
        return (prev.id>current.id)?prev:current;
    })
    let temp_objs = [];
    licenseImageObjs.value.map(it=>temp_objs.push(it));
    let new_id = max_obj.id+1;
    temp_objs.push({id:new_id,file:null});
    licenseImageObjs.value = temp_objs;
    console.log('imgobjects ', licenseImageObjs.value);
    removable_license_status.value = licenseImageObjs.value.length>1?true:false;
}

const removeLicensImgField = (_id) =>{
    licenseImageObjs.value = licenseImageObjs.value.filter(it=>it.id!=_id);
    console.log('imgobjects ', licenseImageObjs.value,_id);
    removable_license_status.value = licenseImageObjs.value.length>1?true:false;
}

const assignLicensFile = (assign_data) =>{
    let tempobj = [];
    licenseImageObjs.value.map(it=>{
        if(assign_data.id==it.id) it.file = assign_data.file;
        tempobj.push(it);
    });
    licenseImageObjs.value= tempobj;
    console.log('assignFile',assign_data,licenseImageObjs.value)
}

const selectCompany = async (data) =>{
    console.log('selected data ', data)
    show_search_dialog.value = false;
    company_branch.value = data;
    company_branch_id.value = data.id;

    company_id.value = await getCompanyFromBranchData(data.id);
    console.log('cccccccc ',company_id)
    let is_representative_val = is_representative.value==true?1:0;
    let is_board_member_val = is_board_member.value==true?1:0;
    var employeer_obj = new EmployeerItem(
            created_id.value,
            updated_id.value,
            company_id.value,
            company_branch_id.value,
            null,
            department.value,
            null,
            person_name_second.value,
            person_name_first.value,
            person_name_second_kana.value,
            person_name_first_kana.value,
            position.value,
            is_representative_val,
            is_board_member_val,
            tel1_1.value+'-'+tel1_2.value+'-'+tel1_3.value,
            tel2_1.value+'-'+tel2_2.value+'-'+tel2_3.value,
            tel3_1.value+'-'+tel3_2.value+'-'+tel3_3.value,
            gender.value,
            roles.value,
            email.value,
            note.value,
            cardImageObjs.value,
            licenseImageObjs.value,
            id.value);

    emit('change_employeer',employeer_obj);
}

</script>

<template>
    <div class="w-full h-full flex m-2 px-4 mx-auto overflow-auto">
        <div class="flex justify-start my-2 w-full overflow-auto rounded-sm bg-white overflow-y-auto">
            <table :class="['table margin-top w-fill ', props.type=='update'?'emp-update':'']" >
                <tr v-if="props.type!='update'">
                    <th> 会社情報 </th>
                    <td>
                        <div class="flex justify-start items-center gap-2 min-h-8">
                            <Button type="button" label="連絡先検索" size="small" class="text-white h-8" @click="show_search_dialog = true" />
                            <span v-if="company_branch">{{ company_branch.company_name_full_short }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th> 担当者氏名 </th>
                    <td>
                        <div>
                            <span>姓：</span>
                            <InputText type="text" class="" v-model="person_name_second" placeholder="" />
                            <span class="pl-4">名：</span>
                            <InputText type="text" class="" v-model="person_name_first" placeholder="" />
                        </div>
                    </td>
                    <th> 性別 </th>
                    <td class="w-[40%]">
                        <div class="flex flex-row p-1 gap-6">
                            <div v-for="gender_item in gender_items" :key="gender_item.key"
                                class="flex flex-row gap-2 align-items-center">
                                <RadioButton v-model="gender" :inputId="gender_item.id" name="dynamic"
                                    :value="gender_item.id" />
                                <label :for="gender_item.id" class="ml-2">{{ gender_item.name }}</label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th> ふりがな </th>
                    <td>
                        <div>
                            <span>姓：</span>
                            <InputText type="text" class="" v-model="person_name_second_kana" placeholder="" />
                            <span class="pl-4">名：</span>
                            <InputText type="text" class="" v-model="person_name_first_kana" placeholder="" />
                        </div>
                    </td>
                    <th> 所属部署 </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="department" placeholder="" />
                    </td>
                </tr>
                <tr>
                    <th> 携帯 </th>
                    <td>
                        <div>
                            <InputText type="text" class="" v-model="tel1_1" placeholder="" />
                            <span> - </span>
                            <InputText type="text" class="" v-model="tel1_2" placeholder="" />
                            <span> - </span>
                            <InputText type="text" class="" v-model="tel1_3" placeholder="" />
                        </div>
                    </td>
                    <th> 役職 </th>
                    <td>
                        <div class="flex items-center gap-5">
                            <InputText type="text" class="w-[50%]" v-model="position" placeholder="" />
                            <div class="flex gap-1">
                                <Checkbox name="remember" v-model="is_representative" binary/>
                                <label  class="ml-2 text-sm text-gray-600 mr-2">役員</label>
                                <Checkbox name="remember" v-model="is_board_member" binary/>
                                <label  class="ml-2 text-sm text-gray-600">代表者</label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th> 携帯2 </th>
                    <td>
                        <div>
                            <InputText type="text" class="" v-model="tel2_1" placeholder="" />
                            <span> - </span>
                            <InputText type="text" class="" v-model="tel2_2" placeholder="" />
                            <span> - </span>
                            <InputText type="text" class="" v-model="tel2_3" placeholder="" />
                        </div>
                    </td>
                    <th> 役割 </th>
                    <td>
                        <div class="flex gap-3">
                            <div v-for="role_item of role_items" :key="role_item.id" class="flex align-items-center gap-1">
                                <Checkbox v-model="roles" :inputId="role_item.id" name="category" :value="role_item.id" />
                                <label :for="role_item.id">{{ role_item.name }}</label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th> 直通電話 </th>
                    <td>
                        <div>
                            <InputText type="text" class="" v-model="tel3_1" placeholder="" />
                            <span> - </span>
                            <InputText type="text" class="" v-model="tel3_2" placeholder="" />
                            <span> - </span>
                            <InputText type="text" class="" v-model="tel3_3" placeholder="" />
                        </div>
                    </td>
                    <th> メモ </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="note" placeholder="" />
                    </td>
                </tr>

                <tr>
                    <th> メールアドレス </th>
                    <td>
                        <InputText type="text" class="w-fill" v-model="email" placeholder="" />
                    </td>
                    <th></th>
                    <td></td>
                </tr>

                <tr>
                    <th> 名刺 </th>
                    <td colspan="3">
                        <div class="flex gap-4 min-h-[100px]">
                            <ImageUploadField v-for="img_item in cardImageObjs" :key="img_item.id" :field_id="img_item.id" :one_status="removable_card_status" @assign_file = "assignCardFile"
                                @add_obj="addCardImgField" @remove_obj="removeCardImgField"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th> 免許証 </th>
                    <td colspan="3">
                        <div class="flex gap-4 min-h-[100px]">
                            <ImageUploadField v-for="img_item in licenseImageObjs" :key="img_item.id" :field_id="img_item.id" :one_status="removable_license_status" @assign_file = "assignLicensFile"
                                @add_obj="addLicenseImgField" @remove_obj="removeLicensImgField"/>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <Dialog v-model:visible="show_search_dialog" header="連絡先選択" :style="{ width: '1250px' }" position="center" :modal="true" :draggable="false">
            <DataTable :value="lp_branches" paginator :paginatorPosition="both" :rows="15"
                :rowsPerPageOptions="[10, 15, 20, 35, 50]" class="p-datatable-sm" tableStyle="min-width: 50rem; "
                style="width:-webkit-fill-available;">
                <Column field="" header="" class="col-data">
                    <template  #body="{ data }">
                        <Button type="button" class="employeer-dialog-btn" label="選択" size="small" @click="selectCompany(data)"></Button>
                    </template>
                </Column>
                <Column field="company_name_full_short" header="会社名" class="col-data"></Column>
                <Column field="branch_name" header="支店名" class="col-data"></Column>
                <Column field="tel" header="電話" class="col-data"></Column>
                <Column field="fax" header="FAX" class="col-data"></Column>
                <Column field="pref" header="都道府県" class="col-data"></Column>
                <Column field="address" header="住所" class="col-data"></Column>
            </DataTable>
        </Dialog>
    </div>
</template>
<style scoped>
.w-fill{
    width: -webkit-fill-available;
}
th{
    background-color: #ebebeb;
    width: 10%;
}
td{
    padding-inline: 12px;
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
