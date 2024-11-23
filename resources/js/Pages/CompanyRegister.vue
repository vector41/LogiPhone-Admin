<script setup>
import { onMounted, ref, watch } from "vue";
import CompanyComponent from "../Components/CompanyComponent.vue";
import CompanyBranchComponent from "../Components/CompanyBranchComponent.vue";
import Button from 'primevue/button';
import { BranchItem } from '@/constant/BranchItem';
import { CompanyItem } from "@/constant/CompanyItem";
import { useToast } from "primevue/usetoast";
import { getImageFiles, saveCompanyLPData, updateUnitSetting } from "@/constant/APIManager";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { IMAGE_ROOT_PATH } from "@/constant/ConstantConfig";
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

const is_branch_contain = ref(false);
const branch_show = ref(false);
const active = ref(0);
const add_dialog_visible = ref(false);
const remove_dialog_visible = ref(false);
const confirm_dialog_visible = ref(false);
const branch_name = ref("");

const active_branch_id = ref(1)

const toast = useToast();

const tab_items = ref([
    { id: 1, label: '支店'}
]);

const branches = ref([
]);

const company = ref(null);

onMounted(async() => {
    let temp_branches = [];
    tab_items.value.forEach(item=>{
        var branch_item = new BranchItem();
        branch_item.branch_name = item.label;
        branch_item.keyword = '本社';
        branch_item.branch_type = 1;
        branch_item.phone_1 = '';
        branch_item.phone_2 = '';
        branch_item.phone_3 = '';
        branch_item.fax_1 = '';
        branch_item.fax_2 = '';
        branch_item.fax_3 = '';
        branch_item.zip_1 = '';
        branch_item.zip_2 = '';
        branch_item.pref = '';
        branch_item.city = '';
        branch_item.town = '';
        branch_item.building = '';
        branch_item.memo = '';
        branch_item.infos = '';
        branch_item.imageObjs = [];
        branch_item.id = null;

        temp_branches.push({
            tab_id:item.id,
            label:item.label,
            obj:branch_item
        })
    })
    branches.value = temp_branches;

    var company_obj =new CompanyItem();
    company_obj.personality = null;
    company_obj.personality_position = null;
    company_obj.company_name = 'te';
    company_obj.company_name_kana = '';
    company_obj.keyword = '',
    company_obj.is_company_branch = null,
    company_obj.phone_1 = '';
    company_obj.phone_2 = '';
    company_obj.phone_3 = '';
    company_obj.fax_1 = '';
    company_obj.fax_2 = '';
    company_obj.fax_3 = '';
    company_obj.zip_1 = '';
    company_obj.zip_2 = '';
    company_obj.pref = null;
    company_obj.city=null,
    company_obj.town='',
    company_obj.building = '';
    company_obj.memo = '';
    company_obj.imageObjs = await getCardImageDatas('1',null);
    company_obj.id=null;

    company.value = company_obj;
    console.log('company... ', company.value)
});

const getCardImageDatas = async (store_pos, employee_id) =>{
    var image_files = [];
    const res = await getImageFiles('1','public/company');
    console.log('res ', res);
    return res;
}

const changeBranch = (_item) => {
    active_branch_id.value = _item.id;
    console.log('change branch...', _item);
}

const removeBranch = () => {
    console.log('removeBranch...');
    let temp_tabs = tab_items.value.filter((tab_item) => tab_item.tab_id !== active_branch_id.value);
    tab_items.value = temp_tabs;

    let temp_branchs = branches.value.filter((branch_item) => branch_item.tab_id !== active_branch_id.value);
    branches.value = temp_branchs;
}

const addBranch = () => {

    if (branch_name.value.trim() == "") return;

    let max_tab_item = tab_items.value.reduce((prev, current) => {
        return (prev.id > current.id) ? prev : current;
    })

    console.log('removeBranch...', max_tab_item, branch_name.value);
    let new_tabitem_id = max_tab_item.id + 1;

    let temp_tabs = [];
    tab_items.value.forEach((tab_item) => temp_tabs.push(tab_item));
    temp_tabs.push({
        id: new_tabitem_id, label: branch_name.value, icon: 'pi pi-home'
    });

    let temp_branches = [];
    branches.value.forEach((branch_item) => temp_branches.push(branch_item));

    var branch_item = new BranchItem();
    branch_item.branch_name = branch_name.value;
    branch_item.keyword = '本社';
    branch_item.branch_type = '本社';
    branch_item.phone_1 = '';
    branch_item.phone_2 = '';
    branch_item.phone_3 = '';
    branch_item.fax_1 = '';
    branch_item.fax_2 = '';
    branch_item.fax_3 = '';
    branch_item.zip_1 = '';
    branch_item.zip_2 = '';
    branch_item.pref = '';
    branch_item.city = '';
    branch_item.town = '';
    branch_item.building = '';
    branch_item.memo = '';
    branch_item.infos = '';
    branch_item.imageObjs = [];
    branch_item.id = null;

    temp_branches.push({
        tab_id:new_tabitem_id,
        label:branch_name.value,
        obj:branch_item
    })

    setTimeout(() => {
        tab_items.value = temp_tabs;
        branches.value = temp_branches;
        console.log('branches ', branches.value)
        branch_name.value = "";
        add_dialog_visible.value = false;
    }, 200);
}

const changeBranchData = (_params) =>{

    branches.value.map(b_item=>{
        if(b_item.tab_id==_params.tab_id)b_item.obj = _params.obj;
        return b_item;
    })
    console.log('changeBranchData', _params,branches.value);
}

const changeCompanyData = (_params) =>{
    console.log('changeCompanyData', _params);
    company.value =_params;
    // console.log('changeBranchData', _params,branches.value);
}

const changeBranchContain = (_params)=>{
    console.log('changeBranchContain ', _params);
    is_branch_contain.value = _params;
}

const clearData = () =>{
    console.log('clearData ');
}

const checkInfos=()=>{
    console.log('addDatas ',company.value, branches.value);
    var error_msg = "";
    if(company.value.company_name==""){
        toast.add({ severity: 'warn', summary: 'お知らせ', detail: '会社名をご入力ください。', life: 2000 });
        return;
    }
    if(company.value.personality==null){
        toast.add({ severity: 'warn', summary: 'お知らせ', detail: '法人形態を選択してください。', life: 2000 });
        return;
    }
    if(company.value.personality_position==null){
        toast.add({ severity: 'warn', summary: 'お知らせ', detail: '法人形態の位置を選択してください。', life: 2000 });
        return;
    }

    confirm_dialog_visible.value = true
}

const addDatas = async () =>{
   /*
    API Input Sample ....
    {
    "legal_personality": 1,
    "legal_personality_position": 2,
    "company_name": "小前田物流サービス",
    "company_name_kana": "test kana",
    "keyword": "rrw",
    "is_company_branch": 0,
    "children":[
        {"branch_name":"testb",
        "tel": "201-2301-5624",
        "is_main_office":0,
        "fax": "201-2301-5624",
        "memo": "test branch memo",
        "zip": "060-0042",
        "prefecture": 1,
        "city": 1101,
        "other": "test other",
        "building": "test building",
        "info": "test info"}
        ]
    }
    */
    let children = [];
    console.log('---- ',branches.value)
    branches.value.forEach(b_item=>{
        let child_item = {
            branch_name:b_item.obj.branch_name,
            tel: b_item.obj.phone_1+'-'+b_item.obj.phone_2+'-'+b_item.obj.phone_3,
            is_main_office:b_item.obj.branch_type,
            fax: b_item.obj.fax_1+'-'+b_item.obj.fax_2+'-'+b_item.obj.fax_3,
            memo: b_item.obj.memo,
            zip: b_item.obj.zip_1+'-'+b_item.obj.zip_2,
            prefecture: b_item.obj.pref?b_item.obj.pref.id:null,
            city: b_item.obj.city?b_item.obj.city.id:null,
            other: b_item.obj.town,
            building: b_item.obj.building,
            info: b_item.obj.infos
        }
        children.push(child_item);
    });
    let company_data = {
        legal_personality: company.value.personality.id??null,
        legal_personality_position: company.value.personality_position.id??null,
        company_name: company.value.company_name??'',
        company_name_kana: company.value.company_name_kana??'',
        keyword: company.value.keyword??'',
        is_company_branch: company.value.is_company_branch.status??0,
        children:children
    }
    console.log('children ', company_data);
    const save_res = await saveCompanyLPData(company_data);
    confirm_dialog_visible.value = false;
    if(save_res){
       company.value.id = save_res;
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

    <Head title="取引先追加" />

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
                        <div class="w-fill flex gap-5 px-5">
                            <Button label="法人情報" severity="success" :class="['companyinfo-btn !h-10',branch_show?'border !bg-white':'text-white']"
                                        :outlined="branch_show" @click="branch_show=false"/>
                            <Button v-show="is_branch_contain" severity="success" label="支店情報"  :outlined="branch_show==false"
                                    :class="['companyinfo-btn !h-10',branch_show?'text-white':'border !bg-white']" @click="branch_show=true"/>
                        </div>
                        <div v-show="branch_show" class="flex items-center px-5 justify-between">
                            <TabMenu v-model="active" :model="tab_items" class="py-2 !w-fit">
                                <template #item="{ item, props }">
                                    <a v-ripple v-bind="props.action" class="flex align-items-center gap-2" @click="changeBranch(item)">
                                        <span class="font-bold">{{ item.label }}</span>
                                        <i class="pi pi-minus-circle text-[22px]" style="color: red;"
                                            @click="add_dialog_visible = true" />
                                    </a>
                                </template>
                            </TabMenu>
                            <Button icon="pi pi-plus" severity="success" text raised rounded aria-label="Plus"
                                @click="add_dialog_visible = true" />
                        </div>
                        <div v-show="branch_show==false" class="flex bg-gray-100 mt-4 mx-4 mb-2">
                            <label class="text-[24px] text-gray-600 font-bold py-2 px-4">法人情報</label>
                        </div>
                        <div class="overflow-auto" v-show="branch_show==false">
                            <CompanyComponent v-if="company" @is_branch_contain="changeBranchContain" :company_obj="company" @change_company="changeCompanyData"/>
                        </div>
                        <div class="overflow-auto" v-show="branch_show">
                            <CompanyBranchComponent v-for="branch in branches" :key="branch.tab_id" :tab_id="branch.tab_id"
                                v-show="branch.tab_id === active_branch_id" :branch_obj="branch.obj" @change_branch="changeBranchData"/>
                        </div>
                        <div class="flex justify-center mt-5 gap-4">
                            <Button type="button" label="クリア" class="w-32 h-8 border border-gray-300" severity="secondary" outlined @click="clearData" />
                            <Button type="button" label="確 認" class="text-white w-32 h-8" @click="checkInfos" />
                        </div>

                        <Dialog v-model:visible="add_dialog_visible" modal header="支店の追加" :style="{ width: '25rem' }">
                            <div class="flex align-items-center gap-3 mb-3">
                                <InputText id="branch_name" class="flex-auto" v-model="branch_name" autocomplete="off"
                                    aria-placeholder="支店名" />
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button type="button" label="キャンセル" severity="secondary" outlined @click="add_dialog_visible = false" />
                                <Button type="button" label="登録/更新" class="text-white" @click="addBranch" />
                            </div>
                        </Dialog>
                        <Dialog v-model:visible="remove_dialog_visible" modal header="支店の追加" :style="{ width: '25rem' }">
                            <div class="flex align-items-center gap-3 mb-3">
                                <InputText id="branch_name" class="flex-auto" v-model="branch_name" autocomplete="off"
                                    aria-placeholder="支店名" />
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button type="button" label="キャンセル" severity="secondary" outlined
                                    @click="remove_dialog_visible = false" />
                                <Button type="button" label="登録/更新" class="text-white" @click="removeBranch()" />
                            </div>
                        </Dialog>

                        <Dialog v-model:visible="confirm_dialog_visible" modal header="取引先登録" :style="{ width: '25rem' }">
                            <div class="flex align-items-center gap-3 mb-3">
                                <label>データを登録しますか？</label>
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button type="button" label="キャンセル" severity="secondary" outlined @click="confirm_dialog_visible = false" />
                                <Button type="button" label="登録/更新" class="text-white" @click="addDatas" />
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
