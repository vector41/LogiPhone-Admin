<script setup>
import { ref, onMounted, computed} from "vue";
import { getCompanyEmployeerDatas, updateUnitSetting } from "@/constant/APIManager";
import { employeer_role_names, genders, table_row_counts } from "@/constant/ConstantConfig";

import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head } from "@inertiajs/inertia-vue3";
import HeaderBar from "@/Components/HeaderBar.vue";
import SideMenu from "@/Components/SideMenu.vue";
import TitleBar from "@/Components/TitleBar.vue";

const props = defineProps({
    datas: Array,
    type: String,
    page_unit_count:Number
});

const is_exchanged = ref(true);
const selected_item = ref(null)

const employeer_datas = ref([]);
// const filtered_employeers = ref([])
const loading = ref(true)
const table_rows = ref(null)
const totalCount = ref(0);

onMounted( async() => {

    await gettingComEmpDatas(1);

    console.log('comany_employeer_datas',employeer_datas.value)
    loading.value = false
});

const gettingComEmpDatas = async (_page)=>{
    employeer_datas.value = [];

    let res_com = await getCompanyEmployeerDatas(_page);
    if (res_com) {
        let cities = JSON.parse(localStorage.getItem('cities'));
        totalCount.value = res_com.total;
        res_com.response.map((item, index) => {
            let other = item.other ? item.other : '';
            let building = item.building ? item.building : '';
            let address = other + building;

            if (cities) {
                let address_part = cities.filter(c => c.id == item.city).length > 0 ? cities.filter(c => c.id == item.city)[0].value : null;
                if (address_part) address = address_part + address;
            }

            employeer_datas.value.push({
                id:item.id,
                person_name: item.person_name,
                person_name_kana: item.person_name_kana,
                person_nickname: item.person_nickname,
                tel: item.tel,
                email: item.email,
                position: item.position,
                department: item.department,
                hire_date:item.hire_date,
                is_retirement:item.is_retirement,
                gender: item.gender&&genders.filter(g=>g.id==item.gender)[0].name?genders.filter(g=>g.id==item.gender)[0].name:'',
                company_name_full_short: item.company_name_full_short,
                branch_name: item.company_branch.branch_name,
                branch_tel: item.company_branch.tel,
                branch_id: item.company_branch.id,
                roles:item.roles.map(r => {return employeer_role_names[r-1].name})
            })
        })
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
    if(update_res){
        table_rows.value = table_row_counts.filter(t=>t.id==update_res)[0].count;
        await gettingComEmpDatas(1);
    }
}

const handlePageChange = async (event)=>{
    console.log('change page...', event.page)
    loading.value = true
    await gettingComEmpDatas(event.page+1);
    loading.value = false
}


</script>

<template>
        <Head title="取引先一覧" />

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
                            <Paginator class="pt-2 main-paginator" :rows="table_rows" :totalRecords="totalCount" :first="1"  @page="handlePageChange">
                                <template #start="slotProps">
                                    <div class="flex justify-center gap-3 ">
                                        <label class="font-bold">表示順: <span class="text-green-500">{{ slotProps.state.rows }}</span> 件</label>
                                        <label class="font-bold">検索結果: <span class="text-green-500">{{ totalCount }}</span> 件 </label>
                                        <label class="font-bold">ページ: <span v-show="loading==false" class="text-red-500">{{ slotProps.state.page+1 }}</span> </label>
                                    </div>
                                </template>
                                <template #end>
                                    <div class="w-[200px]"></div>
                                </template>
                            </Paginator>
                            <div class="flex justify-center mx-auto my-2 pt-6 w-full overflow-x-auto rounded-sm bg-white overflow-y-auto h-fill">
                                <div v-show="loading" class="card flex mt-40 justify-content-center">
                                    <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)"
                                        animationDuration="2s" aria-label="Custom ProgressSpinner" />
                                </div>
                                <DataTable v-show="loading==false" :value="employeer_datas" stripedRows :rows="table_rows" :class="`p-datatable-sm`" tableStyle="min-width: 50rem; "
                                    style="width:-webkit-fill-available">
                                    <Column field="" header="退職" class="col-data">
                                        <template  #body="{ data }">
                                            <span v-if="data.is_retirement" class="text-red-500 font-bold text-[12px]">
                                                退職
                                            </span>
                                        </template>
                                    </Column>
                                    <Column field="person_name" header="氏名" class="col-data"></Column>
                                    <Column field="person_nickname" header="表示名" class="col-data"></Column>
                                    <Column field="person_name_kana" header="かな" class="col-data"></Column>
                                    <Column field="tel" header="直通電話" class="col-data"></Column>
                                    <Column field="email" header="メールアドレス" class="col-data"></Column>
                                    <Column field="company_name_full_short" header="会社名" class="col-data"></Column>
                                    <Column field="branch_name" header="営業所" class="col-data"></Column>
                                    <Column field="department" header="部署" class="col-data"></Column>
                                    <Column field="hire_date" header="入社年月日" class="col-data"></Column>
                                    <Column field="" header="役割" class="col-data">
                                        <template  #body="{ data }">
                                            <Tag  v-for="role, index in data.roles" :key="index" :value="role.charAt(0)" rounded severity="info"></Tag>
                                        </template>
                                    </Column>
                                    <Column field="register" header="権限" class="col-data"></Column>
                                </DataTable>
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
    border-bottom: dotted 1px rgb(59 130 246 / 0.5);
}

.border-1 {
    border-width: 1px;
}

</style>
