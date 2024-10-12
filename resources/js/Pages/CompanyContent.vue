<script setup>
import { ref, onMounted, watch, defineEmits, computed } from "vue";
import {
  getCompanyDatas,
  getEmployyFromBranch,
  getBranchData,
  updateBranchData,
  removeBranchData,
  updateUnitSetting,
} from "@/constant/APIManager";

import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import {
  employeer_role_names,
  genders,
  prefectures,
  store_postions,
  table_row_counts,
  typesCategories,
} from "@/constant/ConstantConfig";
import CompanyBranchComponent from "@/Components/CompanyBranchComponent.vue";
import { BranchItem } from "@/constant/BranchItem";
import { Head } from "@inertiajs/inertia-vue3";
import HeaderBar from "@/Components/HeaderBar.vue";
import SideMenu from "@/Components/SideMenu.vue";
import TitleBar from "@/Components/TitleBar.vue";

const props = defineProps({
  datas: Array,
  type: String,
  page_unit_count: Number,
});

const is_exchanged = ref(true);
const selected_item = ref(null);

const toast = useToast();
const confirm = useConfirm();

const company_datas = ref([]);
const prefs = ref([]);
const pref = ref();
const all_cities = ref([]);
const cities = ref([]);
const city = ref();
const keyword = ref(null);
const address = ref(null);
const filtered_companies = ref([]);
const store_position = ref(store_postions[0]);
const isTypeShow = ref(false);
const currentCategories = ref([]);
const loading = ref(true);
const table_rows = ref(null);

const branch_header = ref("社員一覧");
const show_employee_dialog = ref(false);
const is_getting = ref(false);
const employees = ref([]);
const selected_company = ref(null);
const selected_branch = ref(null);
const show_detail_dialog = ref(false);
const selected_company_full_name = ref();
const updatingBranchData = ref(null);

const currentPage = ref(1);
const totalCount = ref(0);

const filter_store_position = ref(null);
const filter_typeCategories = ref([]);
const filter_keyword = ref(null);
const filter_pref = ref(null);
const filter_address = ref(null);

onMounted(async () => {
  prefs.value.push({ id: 0, value: "すべて" });
  prefectures.forEach((p) => {
    prefs.value.push(p);
  });
  await gettingComDatas(1);
  console.log("company_datas", company_datas.value, table_rows.value);
  all_cities.value = JSON.parse(localStorage.getItem("cities"));
  loading.value = false;

  currentCategories.value = typesCategories;
});

const gettingComDatas = async (_page) => {
  company_datas.value = [];
  console.log(
    "filterCompanies:: ",
    filter_store_position.value,
    filter_typeCategories.value,
    filter_keyword.value,
    filter_pref.value,
    filter_address.value
  );
  let res_com = await getCompanyDatas(
    _page,
    filter_store_position.value,
    filter_typeCategories.value,
    filter_keyword.value,
    filter_pref.value,
    filter_address.value
  );
  if (res_com) {
    console.log("companudatas ", res_com);
    let cities = JSON.parse(localStorage.getItem("cities"));
    totalCount.value = res_com.total;
    res_com.response.map((item, index) => {
      let pref = item.prefecture ? prefectures[item.prefecture].value : "";
      let other = item.other ? item.other : "";
      let building = item.building ? item.building : "";
      let address = other + building;

      if (cities) {
        let address_part =
          cities.filter((c) => c.id == item.city).length > 0
            ? cities.filter((c) => c.id == item.city)[0].value
            : null;
        if (address_part) address = address_part + address;
      }
      company_datas.value.push({
        id: index,
        item_id: item.id,
        store_pos: item.store_pos,
        register: item.register,
        checked: false,
        branch_name: item.branch_name,
        company_name_full_short: item.company_name_full_short,
        // company_name: item.company_name,
        tel: item.tel,
        fax: item.fax,
        pref: pref,
        keyword: item.keyword,
        address: address,
      });
    });
  }
  filtered_companies.value = company_datas.value;
};

const filterCompanies = async () => {
  filter_store_position.value = store_position.value.id;
  filter_keyword.value = keyword.value;
  filter_address.value = address.value;
  filter_pref.value = pref.value ? pref.value.id : null;
  filter_typeCategories.value = [];
  currentCategories.value.forEach((item) => {
    // if (item.checked)
    filter_typeCategories.value.push(item.id);
  });
  console.log(
    "filterCompanies:: ",
    store_position.value,
    keyword.value,
    pref.value,
    address.value
  );

  loading.value = true;
  await gettingComDatas(1);
  loading.value = false;

  // let temp_datas = company_datas.value;
  // if(keyword.value&&keyword.value.trim()!=="") temp_datas = temp_datas.filter(d=>d.keyword&&d.keyword.includes(keyword.value))
  // if(pref.value&&pref.value.id!=0) {
  //     console.log('pref.value:: ',pref.value)
  //     temp_datas = temp_datas.filter(d=>d.pref==pref.value.value)
  // }
  // if(address.value) temp_datas = temp_datas.filter(d=>d.address&&d.address.includes(address.value))
  // filtered_companies.value = temp_datas;
};

const showEmployee = async (_data) => {
  console.log("showEmployee ", _data);
  let res_data = await getEmployyFromBranch(_data.type, _data.item_id);
  console.log("employy value ", res_data);
  let temp_items = [];
  res_data.forEach((item, index) => {
    let item_roles = item.roles.map((r) => {
      return employeer_role_names[r - 1];
    });
    temp_items.push({
      no: index + 1,
      is_retirement: item.is_retirement == 1 ? true : false,
      email: item.email,
      gender: genders[item.gender],
      person_name: item.person_name,
      person_name_kana: item.person_name_kana,
      tel: item.tel,
      roles: item_roles,
    });
  });
  branch_header.value =
    "社員一覧: [" + _data.company_name_full_short + " - " + _data.branch_name + "]";
  employees.value = temp_items;
  show_employee_dialog.value = true;
  is_getting.value = true;
};

const toggleTypePanel = () => {
  isTypeShow.value = !isTypeShow.value;
};

watch(show_employee_dialog, (newVal, oldVal) => {
  if (newVal == false) is_getting.value = false;
});

const detailCompanyBranch = async () => {
  updatingBranchData.value = null;
  var selected_items = filtered_companies.value.filter((f) => f.checked == true);
  console.log("detailCompanyBranch:: ", selected_items);
  if (selected_items.length > 1) {
    toast.add({
      severity: "warn",
      summary: "お知らせ",
      detail: "1名の担当者をお選びください。",
      life: 2000,
    });
    return;
  }
  selected_company.value = selected_items[0];
  selected_company_full_name.value = selected_items[0].company_name_full_short;
  // employee_title.value = "担当者詳細   "+selected_items[0].company_name_full_short+"  : "+selected_items[0].branch_name;
  let res_branch = await getBranchData(
    selected_items[0].store_pos,
    selected_items[0].item_id
  );
  if (res_branch) {
    var branch_obj = new BranchItem();
    branch_obj.created_id = res_branch.created_id;
    branch_obj.updated_id = res_branch.updated_id;
    branch_obj.deleted_id = res_branch.deleted_id;
    branch_obj.branch_name = res_branch.branch_name;
    branch_obj.keyword = res_branch.keyword;
    branch_obj.branch_type = res_branch.is_main_office;
    branch_obj.phone_1 = res_branch.tel ? res_branch.tel.split("-")[0] : "";
    branch_obj.phone_2 = res_branch.tel ? res_branch.tel.split("-")[1] : "";
    branch_obj.phone_3 = res_branch.tel ? res_branch.tel.split("-")[2] : "";
    branch_obj.fax_1 = res_branch.fax ? res_branch.fax.split("-")[0] : "";
    branch_obj.fax_2 = res_branch.fax ? res_branch.fax.split("-")[1] : "";
    branch_obj.fax_3 = res_branch.fax ? res_branch.fax.split("-")[2] : "";
    branch_obj.zip_1 = res_branch.zip ? res_branch.zip.split("-")[0] : "";
    branch_obj.zip_2 = res_branch.zip ? res_branch.zip.split("-")[1] : "";
    branch_obj.pref = res_branch.prefecture;
    branch_obj.city = res_branch.city;
    branch_obj.town = res_branch.other;
    branch_obj.building = res_branch.building;
    branch_obj.memo = res_branch.memo;
    branch_obj.infos = res_branch.info;
    branch_obj.memo = res_branch.memo;
    branch_obj.imageObjs = [];
    branch_obj.id = res_branch.id;
    branch_obj.store_pos = selected_items[0].store_pos;

    selected_branch.value = branch_obj;
    console.log("selected branch ... ", selected_branch.value);
  }
  show_detail_dialog.value = true;
};

const changeBranchData = (_data) => {
  console.log("changecontentBranchData", _data);
  updatingBranchData.value = {
    id: _data.obj.id,
    branch_name: _data.obj.branch_name,
    tel: _data.obj.phone_1 + "-" + _data.obj.phone_2 + "-" + _data.obj.phone_3,
    is_main_office: _data.obj.branch_type,
    fax: _data.obj.fax_1 + "-" + _data.obj.fax_2 + "-" + _data.obj.fax_3,
    memo: _data.obj.memo,
    zip: _data.obj.zip_1 + "-" + _data.obj.zip_2,
    prefecture: _data.obj.pref ? _data.obj.pref.id : null,
    city: _data.obj.city ? _data.obj.city.id : null,
    other: _data.obj.town,
    building: _data.obj.building,
    info: _data.obj.infos,
    keyword: _data.obj.keyword,
    updated_id: 1,
    store_pos: _data.obj.store_pos,
  };
  console.log("updatingBranchData", updatingBranchData.value);
};

const updateBranch = async () => {
  if (updatingBranchData.value == null) return;
  console.log(
    "company_datas.value[updatingBranchData.value.id] ",
    company_datas.value[selected_company.value.id]
  );

  const res_branch_up = await updateBranchData(updatingBranchData.value);
  if (res_branch_up) {
    let pref = updatingBranchData.value.prefecture
      ? prefectures[updatingBranchData.value.prefecture].value
      : "";
    let other = updatingBranchData.value.other ? updatingBranchData.value.other : "";
    let building = updatingBranchData.value.building
      ? updatingBranchData.value.building
      : "";
    let address = other + building;

    company_datas.value[selected_company.value.id].branch_name =
      updatingBranchData.value.branch_name;
    company_datas.value[selected_company.value.id].tel = updatingBranchData.value.tel;
    company_datas.value[selected_company.value.id].fax = updatingBranchData.value.fax;
    company_datas.value[selected_company.value.id].pref = pref;
    company_datas.value[selected_company.value.id].address = address;

    is_getting.value = false;
    show_detail_dialog.value = false;
    selected_company.value = null;
    setTimeout(() => {
      toast.add({
        severity: "success",
        summary: "お知らせ",
        detail: "データが更新されました。",
        life: 2000,
      });
    }, 200);
  } else {
    toast.add({
      severity: "danger",
      summary: "お知らせ",
      detail: "データ保存が失敗しました。",
      life: 2000,
    });
  }
};

const delteBranches = async () => {
  if (
    filtered_companies.value.filter((f) => f.checked == true && f.store_pos == "2").length
  ) {
    toast.add({
      severity: "info",
      summary: "お知らせ",
      detail: "この取引先は削除できません。\nロジスコープで削除して下さい。",
      life: 2500,
    });
    return;
  }
  var selected_items = filtered_companies.value.filter((f) => f.checked == true);
  var selected_ids = [];
  if (selected_items.length == 0) {
    return;
  } else {
    selected_items.forEach((item) => {
      selected_ids.push(item.item_id);
    });
  }

  const res_branch_del = await removeBranchData(selected_ids);
  if (res_branch_del) {
    filtered_companies.value = filtered_companies.value.filter((f) => f.checked == false);

    setTimeout(() => {
      toast.add({
        severity: "success",
        summary: "お知らせ",
        detail: "データが削除されました。",
        life: 2500,
      });
    }, 200);
  } else {
    toast.add({
      severity: "danger",
      summary: "お知らせ",
      detail: "データ削除が失敗しました。",
      life: 2500,
    });
  }
};

const settingMenuItem = (item) => {
  selected_item.value = item;
};

const settingExchanged = (val) => {
  is_exchanged.value = val;
};

const settingUnitCount = async (val) => {
  console.log("company_content_val ", val);
  let update_res = await updateUnitSetting(val);
  if (update_res) {
    console.log(update_res, "updated_res");

    table_rows.value = table_row_counts.filter((t) => t.count == update_res)[0].count;
    await gettingComDatas(1);
  }
};

const handlePageChange = async (event) => {
  console.log("change page...", event.page);
  loading.value = true;
  await gettingComDatas(event.page + 1);
  loading.value = false;
};
</script>

<template>
  <Head title="取引先一覧" />

  <div class="flex flex-col h-screen overflow-hidden">
    <HeaderBar
      :item="selected_item"
      :exchanged="is_exchanged"
      @setting_exchanged="settingExchanged"
    />
    <div class="flex w-full h-[calc(100vh-48px)]">
      <div class="shrink items-center">
        <SideMenu @seleted_menu_item="settingMenuItem" :exchanged="is_exchanged" />
      </div>
      <div class="flex flex-col items-center content-part">
        <TitleBar :item="selected_item" @setting_unit_count="settingUnitCount" />
        <Toast position="center" />
        <div class="w-fill px-10 min-h-[500px]">
          <div class="w-full h-full flex flex-col m-2 py-4 mx-auto">
            <div class="flex items-center justify-between pt-4">
              <div class="flex items-center gap-4">
                <Dropdown
                  v-model="store_position"
                  :options="store_postions"
                  option-label="name"
                  placeholder="全て表示"
                  class="w-52 !"
                />
                <!-- <div>
                  <InputText
                    type="text"
                    class="w-40 relative"
                    placeholder="業種"
                    @click="toggleTypePanel"
                  />
                  <div
                    v-show="isTypeShow"
                    class="absolute rounded-md z-50 bg-white border shadow-md mt-1 w-auto py-1 px-2"
                  >
                    <div
                      v-for="typeCategory in currentCategories"
                      :key="typeCategory.id"
                      class="flex items-center justify-between py-1 pl-1"
                    >
                      <span>{{ typeCategory.name }}</span>
                      <Checkbox
                        v-model="typeCategory.checked"
                        :binary="true"
                        class="mx-3"
                      />
                    </div>
                  </div>
                </div> -->
                <InputText
                  type="text"
                  class="w-64"
                  v-model="keyword"
                  placeholder="キーワード 例: ドラボックス"
                />
                <div class="flex items-center justify-center gap-1">
                  <Dropdown
                    v-model="pref"
                    :options="prefs"
                    option-label="value"
                    placeholder="都道府県"
                    class="w-36"
                  />
                  <InputText
                    type="text"
                    class="w-60"
                    v-model="address"
                    placeholder="市区町村"
                  />
                </div>
                <Button
                  icon="pi pi-search"
                  label="検索"
                  class="text-white"
                  @click="filterCompanies"
                />
              </div>
              <div class="flex items-center gap-4">
                <Button
                  icon="pi pi-ellipsis-h"
                  severity="info"
                  label="詳細"
                  class="h-[34px] text-white"
                  @click="detailCompanyBranch"
                />
                <Button
                  icon="pi pi-trash"
                  severity="danger"
                  label="削除"
                  class="h-[34px] text-white"
                  @click="delteBranches"
                />
              </div>
            </div>

            <Paginator
              class="pt-2 main-paginator"
              :rows="table_rows"
              :totalRecords="totalCount"
              :first="1"
              @page="handlePageChange"
            >
              <template #start="slotProps">
                <div class="flex justify-center gap-3">
                  <label class="font-bold"
                    >表示順:
                    <span class="text-green-500">{{ slotProps.state.rows }}</span>
                    件</label
                  >
                  <label class="font-bold"
                    >検索結果: <span class="text-green-500">{{ totalCount }}</span> 件
                  </label>
                  <label class="font-bold"
                    >ページ:
                    <span v-show="loading == false" class="text-red-500">{{
                      slotProps.state.page + 1
                    }}</span>
                  </label>
                </div>
              </template>
              <template #end>
                <div class="w-[200px]"></div>
              </template>
            </Paginator>
            <div
              class="flex justify-center mx-auto my-2 w-full overflow-x-auto rounded-sm bg-white overflow-y-auto h-fill"
            >
              <div v-show="loading" class="card flex mt-40 justify-content-center">
                <ProgressSpinner
                  style="width: 50px; height: 50px"
                  strokeWidth="8"
                  fill="var(--surface-ground)"
                  animationDuration="2s"
                  aria-label="Custom ProgressSpinner"
                />
              </div>
              <DataTable
                v-show="loading == false"
                :value="company_datas"
                stripedRows
                :rows="table_rows"
                :class="`p-datatable-sm `"
                tableStyle="min-width: 50rem;"
                style="width: -webkit-fill-available"
              >
                <Column field="" header="" class="col-data">
                  <template #body="{ data }">
                    <Checkbox
                      name="remember"
                      class="text-center w-auto"
                      v-model="data.checked"
                      binary
                    />
                  </template>
                </Column>
                <Column field="" header="" class="col-data">
                  <template #body="{ data }">
                    <i
                      v-if="data.store_pos == 2"
                      class="pi pi-share-alt rounded-full p-1 bg-gray-700"
                      style="font-size: 1rem; color: white"
                    >
                    </i>
                  </template>
                </Column>
                <Column field="register" header="登録者" class="col-data"></Column>
                <Column
                  field="company_name_full_short"
                  header="会社名"
                  class="col-data"
                ></Column>
                <Column field="branch_name" header="支店名" class="col-data"></Column>
                <!-- <Column field="company_name" header="かな" class="col-data"></Column> -->
                <Column field="tel" header="電話" class="col-data"></Column>
                <Column field="fax" header="FAX" class="col-data"></Column>
                <Column field="pref" header="都道府県" class="col-data"></Column>
                <Column field="address" header="住所" class="col-data"></Column>
                <Column field="" header="担当者" class="col-data">
                  <template #body="{ data }">
                    <a
                      class="text-center w-auto cursor-pointer text-blue-400 underline underline-offset-2"
                      @click="showEmployee(data)"
                    >
                      社員
                    </a>
                  </template>
                </Column>
              </DataTable>
            </div>
            <Dialog
              v-model:visible="show_employee_dialog"
              :header="branch_header"
              :style="{ width: '1250px', height: '810px' }"
              position="center"
              :modal="true"
              :draggable="false"
            >
              <DataTable
                v-if="is_getting == true"
                :value="employees"
                paginator
                :paginatorPosition="both"
                :rows="20"
                :rowsPerPageOptions="[20, 35, 50, 80]"
                :class="`p-datatable-sm`"
                style="width: -webkit-fill-available"
              >
                <Column field="no" header="No" class="col-data"></Column>
                <Column field="" header="退職" class="col-data">
                  <template #body="{ data }">
                    <span
                      v-if="data.is_retirement"
                      class="text-red-500 font-bold text-[12px]"
                    >
                      退職
                    </span>
                  </template>
                </Column>
                <Column field="person_name" header="氏名" class="col-data"></Column>
                <Column field="person_name_kana" header="かな" class="col-data"></Column>
                <Column field="tel" header="直通電話" class="col-data"></Column>
                <Column field="email" header="メールアドレス" class="col-data"></Column>
                <Column field="" header="役割" class="col-data">
                  <template #body="{ data }">
                    <span
                      v-for="(emp_role, emp_index) in data.roles"
                      :key="emp_index"
                      class="label-circle accent"
                    >
                      {{ emp_role.name.charAt(0) }}
                    </span>
                  </template>
                </Column>
              </DataTable>
            </Dialog>
            <Dialog
              v-model:visible="show_detail_dialog"
              :header="selected_company_full_name"
              :style="{ width: '1250px', height: '810px' }"
              position="center"
              :modal="true"
              :draggable="false"
              :pt="{ root: 'border-none', content: 'flex flex-col' }"
            >
              <CompanyBranchComponent
                :type="`update`"
                :branch_obj="selected_branch"
                @change_branch="changeBranchData"
              />
              <div class="flex justify-center gap-2">
                <Button
                  type="button"
                  label="キャンセル"
                  class="w-32 h-8"
                  severity="secondary"
                  outlined
                  @click="show_detail_dialog = false"
                ></Button>
                <Button
                  type="button"
                  label="更 新"
                  class="text-white w-32 h-8"
                  @click="updateBranch"
                ></Button>
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
