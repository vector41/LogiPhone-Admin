<script setup>
import { onMounted, ref, watch } from "vue";
import EmployeerComponent from "../Components/EmployeerComponent.vue";
import Button from 'primevue/button';
import { EmployeerItem } from "@/constant/EmployeerItem";
import { saveEmployeeLPData } from "@/constant/APIManager";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head } from "@inertiajs/inertia-vue3";
import HeaderBar from "@/Components/HeaderBar.vue";
import SideMenu from "@/Components/SideMenu.vue";
import TitleBar from "@/Components/TitleBar.vue";

const props = defineProps({
    base_datas: Array,
    type: String
});

const is_exchanged = ref(true);
const selected_item = ref(null)

const confirm_dialog_visible = ref(false);

const employeer = ref(null);

onMounted(() => {

    var employeer_obj =new EmployeerItem();
    employeer_obj.created_id = null;
    employeer_obj.updated_id = null;
    employeer_obj.company_id = 1;
    employeer_obj.company_branch_id = 1;
    employeer_obj.company_department_id = 2,
    employeer_obj.department = '',
    employeer_obj.company_department_child_id = null;
    employeer_obj.person_name_second = '';
    employeer_obj.person_name_first = '';
    employeer_obj.person_name_second_kana = '';
    employeer_obj.person_name_first_kana = '';
    employeer_obj.position = '';
    employeer_obj.is_representative = 1;
    employeer_obj.is_board_member = 0;
    employeer_obj.tel1='1256-232-3443',
    employeer_obj.tel2 = '324-342';
    employeer_obj.tel3 = '';
    employeer_obj.gender = 3;
    employeer_obj.roles = [1,3];
    employeer_obj.email = '';
    employeer_obj.note = '';
    employeer_obj.cardImageObjs = [];
    employeer_obj.licenseImageObjs = [];
    employeer_obj.id=null;

    employeer.value = employeer_obj;

    console.log('company... ', employeer.value)
});

const changeEmployeerData = (_params) =>{
    console.log('changeBranchData', _params);
    employeer.value = _params;
}

const clearData = () =>{
    console.log('clear... ')
}

const addEmployeer = async () =>{
    console.log('addEmployeer', employeer.value);
    const empolyee_id = await saveEmployeeLPData(employeer.value);
    confirm_dialog_visible.value = false;
    if(empolyee_id){
        employeer.value.id = empolyee_id;
        console.log('new employee ', employeer.value)
    }else{

    }
}

const settingMenuItem = (item) => {
    selected_item.value = item;
}

const settingExchanged = (val)=>{
    is_exchanged.value = val;
}

const settingUnitCount = async (val)=>{
    console.log('val ', val);
    let update_res = await updateUnitSetting(val);
}

</script>

<template>

    <Head title="担当者追加" />

    <div class="flex flex-col h-screen overflow-hidden " >
        <HeaderBar :item="selected_item" :exchanged="is_exchanged" @setting_exchanged = "settingExchanged"/>
        <div class="flex w-full h-[calc(100vh-48px)]">
            <div class="shrink items-center">
                <SideMenu @seleted_menu_item="settingMenuItem" :exchanged = "is_exchanged"/>
            </div>
            <div class="flex flex-col items-center content-part">
                <TitleBar :item="selected_item" @setting_unit_count = "settingUnitCount"/>
                <Toast position="center"/>
                <div class="w-fill px-10 min-h-[500px]">
                    <div class="w-full h-full flex flex-col m-2 py-4 mx-auto">
                        <div class="flex bg-gray-100 mt-4 mx-4 mb-2">
                            <label class="text-[24px] text-gray-600 font-bold py-2 px-4">担当者登録</label>
                        </div>
                        <div v-if="employeer">
                            <EmployeerComponent :employeer_obj="employeer" @change_employeer="changeEmployeerData"/>
                        </div>
                        <div class="flex justify-center mt-5 gap-4">
                            <Button type="button" label="クリア" class="border w-32" severity="secondary" outlined @click="clearData" />
                            <Button type="button" label="確 認" class="text-white w-32" @click="confirm_dialog_visible = true" />
                        </div>
                        <Dialog v-model:visible="confirm_dialog_visible" modal header="担当者登録" :style="{ width: '25rem' }">
                            <div class="flex align-items-center gap-3 mb-3">
                                <label>データを登録しますか？</label>
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button type="button" label="キャンセル" severity="secondary" outlined @click="confirm_dialog_visible = false" />
                                <Button type="button" label="登録/更新" class="text-white" @click="addEmployeer" />
                            </div>
                        </Dialog>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.content-part {
    width: inherit;
}

th {
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

.custom-row {
    margin: -15px 0;
}
</style>
