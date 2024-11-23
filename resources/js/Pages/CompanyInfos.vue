<script setup>
import { onMounted, ref, watch } from "vue";
import CompanyInfoComponent from "../Components/CompanyInfoComponent.vue";
import OfficeInfoComponent from "../Components/OfficeInfoComponent.vue";
import Button from 'primevue/button';
import { CompanyInfoItem } from "@/constant/CompanyInfoItem";
import { getCompanySettingDatas, updateUnitSetting } from "@/constant/APIManager";
import { OfficeItem } from "@/constant/OfficeItem";
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

const company_settings = ref([]);
const active_company = ref()
const tab_items = ref([]);
const officies = ref([]);
const active_office = ref()
const office_tab_items = ref([]);
const title = ref('営業所設定');
const btn_title = ref('営業所設定');

onMounted(async() => {
    let res_datas = await getCompanySettingDatas();
    console.log('filterd ',  res_datas)

    let temp_companies = [];
    let temp_tabs = [];
    let temp_officies = [];
    let temp_office_tabs = [];

    if(res_datas&&res_datas.length>0){
        res_datas.forEach(item=>{
            var company_setting_item = new CompanyInfoItem();
            company_setting_item.created_id = item.created_id;
            company_setting_item.updated_id = item.updated_id;
            company_setting_item.company_base_id = item.company_base_id;
            company_setting_item.personality = item.legal_personality;
            company_setting_item.personality_position = item.legal_personality_position;
            company_setting_item.company_name = item.company_name;
            company_setting_item.is_company_branch = item.is_company_branch;
            company_setting_item.departments = item.departments;
            company_setting_item.zip_1 = item.zip?item.zip.split('-')[0]:'';
            company_setting_item.zip_2 = item.zip?item.zip.split('-')[1]:'';
            company_setting_item.pref = item.prefecture?item.prefecture:null;
            company_setting_item.city = item.city?item.city:null;
            company_setting_item.town = item.other?item.other:'';
            company_setting_item.building = item.building?item.building:'';
            company_setting_item.id = item.id;
            company_setting_item.children = item.children?item.children:[];

            temp_companies.push(company_setting_item);

            temp_tabs.push({
                tab_id:item.id,
                label:item.company_name
            })
        })

        tab_items.value = temp_tabs;
        company_settings.value = temp_companies;
        active_company.value = temp_companies.filter(t=>t.id==temp_tabs[0].tab_id)[0];
        active_company.value.children.forEach(b_item=>{
            var office_item = new OfficeItem();
            office_item.company_id = b_item.company_id;
            office_item.is_main_office = b_item.is_main_office;
            office_item.branch_name = b_item.branch_name;
            office_item.nickname = b_item.nickname;
            office_item.zip_1 = b_item.zip?b_item.zip.split('-')[0]:'';
            office_item.zip_2 = b_item.zip?b_item.zip.split('-')[1]:'';
            office_item.pref = b_item.prefecture;
            office_item.city = b_item.city,
            office_item.town= b_item.other,
            office_item.building = b_item.building;
            office_item.tel = b_item.tel;
            office_item.fax = b_item.fax;
            office_item.id = b_item.id;

            temp_officies.push(office_item);

            temp_office_tabs.push({
                tab_id:b_item.id,
                label:b_item.nickname
            })
        })
        officies.value = temp_officies;
        active_office.value = temp_officies[0];
        office_tab_items.value = temp_office_tabs;
        console.log('company... ',tab_items.value, company_settings.value,active_company.value,officies.value)
    }
});

const changeCompany = (_tab_item) =>{
    console.log('changeCompanyData', _tab_item);
    let temp_officies = [];
    let temp_office_tabs = [];
    active_company.value = company_settings.value.filter(c=>c.id==_tab_item.tab_id)[0];
    active_company.value.children.forEach(b_item=>{
        var office_item = new OfficeItem();
        office_item.company_id = b_item.company_id;
        office_item.is_main_office = b_item.is_main_office;
        office_item.branch_name = b_item.branch_name;
        office_item.nickname = b_item.nickname;
        office_item.zip_1 = b_item.zip?b_item.zip.split('-')[0]:'';
        office_item.zip_2 = b_item.zip?b_item.zip.split('-')[1]:'';
        office_item.pref = b_item.prefecture;
        office_item.city = b_item.city,
        office_item.town= b_item.other,
        office_item.building = b_item.building;
        office_item.tel = b_item.tel;
        office_item.fax = b_item.fax;
        office_item.id = b_item.id;

        temp_officies.push(office_item);

        temp_office_tabs.push({
            tab_id:b_item.id,
            label:b_item.nickname
        })
    })
    officies.value = temp_officies;
    active_office.value = temp_officies[0];
    office_tab_items.value = temp_office_tabs;
    console.log('company... ',tab_items.value, company_settings.value,active_company.value,officies.value)
}

const changeOffice = (_tab_item) =>{
    console.log('changeCompanyData', _tab_item);
    active_office.value = officies.value.filter(o=>o.id==_tab_item.tab_id)[0];

}

const changeShow = () =>{
    if(title.value=='営業所設定'){
        title.value='会社設定'
        btn_title.value='会社設定'
    }else{
        title.value='営業所設定'
        btn_title.value='営業所設定'
    }
    console.log('title.value ',title.value);
}

const addDatas = () =>{
    console.log('addDatas ');
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
    <Head title="会社設定" />

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
                        <div class="flex items-center px-5 justify-start">
                            <div v-show="title=='営業所設定'" class="flex gap-2">
                                <Button v-for="tab_item in tab_items" :key="tab_item.tab_id" :label=tab_item.label severity="success"
                                    :class="['companyinfo-btn w-48 !h-10',tab_item.tab_id==active_company.id?'text-white':'border']" :outlined="tab_item.tab_id!==active_company.id" @click="changeCompany(tab_item)"/>
                            </div>
                            <div v-show="title=='会社設定'" class="flex gap-2">
                                <Button v-for="tab_item in office_tab_items" :key="tab_item.tab_id" :label=tab_item.label severity="success"
                                    :class="['companyinfo-btn w-48 !h-10',tab_item.tab_id==active_office.id?'text-white':'border']"  :outlined="tab_item.tab_id!==active_office.id" @click="changeOffice(tab_item)"/>
                            </div>
                        </div>
                        <div v-show="title=='営業所設定'">
                            <CompanyInfoComponent v-for="company_setting in company_settings" :key="company_setting.id" :tab_id="company_setting.id"
                                v-show="company_setting.id === active_company.id" :company_info_obj="company_setting" />
                        </div>
                        <div v-show="title=='会社設定'">
                            <OfficeInfoComponent v-for="office in officies" :key="office.id" :tab_id="office.id"
                                v-show="office.id === active_office.id" :office_info_obj="office" />
                        </div>
                        <div v-show="active_company&&active_company.is_company_branch==1" class="flex justify-center mt-5 gap-4">
                            <Button type="button" :label="btn_title" class="text-white w-32" @click="changeShow" />
                        </div>

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

.w-fill {
    width: -webkit-fill-available;
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
