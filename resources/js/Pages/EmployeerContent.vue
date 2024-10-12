<script setup>
import { ref, onMounted, computed, watch } from "vue";
import {
  getEmployeerData,
  getEmployeerDatas,
  getImageFiles,
  updateEmployeerData,
} from "@/constant/APIManager";
import {
  prefectures,
  employeer_role_names,
  genders,
  store_postions,
  table_row_counts,
} from "@/constant/ConstantConfig";

import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import EmployeerComponent from "@/Components/EmployeerComponent.vue";
import { EmployeerItem } from "@/constant/EmployeerItem";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { removeEmployeerData } from "@/constant/APIManager";
import { Head } from "@inertiajs/inertia-vue3";
import HeaderBar from "@/Components/HeaderBar.vue";
import SideMenu from "@/Components/SideMenu.vue";
import TitleBar from "@/Components/TitleBar.vue";
import { updateUnitSetting } from "@/constant/APIManager";

const props = defineProps({
  datas: Array,
  type: String,
  page_unit_count: Number,
});

const is_exchanged = ref(true);

const toast = useToast();
const confirm = useConfirm();

const employeer_datas = ref([]);
const prefs = ref([]);
const pref = ref();
const all_cities = ref([]);
const cities = ref([]);
const city = ref();
const keyword = ref();
const address = ref();
const filtered_employeers = ref([]);
const store_position = ref(store_postions[0]);
const loading = ref(true);
const teble_rows = ref(null);
const show_detail_dialog = ref(false);
const selected_employee = ref(null);
const selected_item = ref(null);
const employee_title = ref(null);

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

  await gettingEmpDatas(1);

  all_cities.value = JSON.parse(localStorage.getItem("cities"));
  loading.value = false;
});

const gettingEmpDatas = async (_page) => {
  employeer_datas.value = [];
  console.log(
    "filterCompanies:: ",
    filter_store_position.value,
    filter_typeCategories.value,
    filter_keyword.value,
    filter_pref.value,
    filter_address.value
  );
  let res_emp = await getEmployeerDatas(
    _page,
    filter_store_position.value,
    filter_typeCategories.value,
    filter_keyword.value,
    filter_pref.value,
    filter_address.value
  );
  if (res_emp) {
    console.log("companudatas ", res_emp);
    let cities = JSON.parse(localStorage.getItem("cities"));
    totalCount.value = res_emp.total;
    res_emp.response.map((item, index) => {
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

      employeer_datas.value.push({
        id: index,
        item_id: item.id,
        checked: false,
        store_pos: item.store_pos,
        register: item.register ?? item.updater ?? "",
        created_at: item.created_at,
        updated_at: item.updated_at,
        person_name: item.person_name,
        person_name_kana: item.person_name_kana,
        tel: item.tel,
        email: item.email,
        position: item.position,
        gender:
          item.gender && genders.filter((g) => g.id == item.gender)[0].name
            ? genders.filter((g) => g.id == item.gender)[0].name
            : "",
        company_name_full_short: item.company_name_full_short,
        branch_name: item.branch_name,
        branch_prefecture: item.branch_prefecture,
        branch_city:
          item.branch_city && cities.filter((p) => p.id == item.branch_city)
            ? cities.filter((p) => p.id == item.branch_city)[0].value
            : "",
        branch_tel: item.branch_tel,
        roles: item.roles
          ? item.roles.map((r) => {
              return employeer_role_names[r - 1].name;
            })
          : [],
      });
    });
  }
  filtered_employeers.value = employeer_datas.value;
};

const filterEmployeers = async () => {
  console.log("filterEmployeers:: ");
  filter_store_position.value = store_position.value.id;
  filter_keyword.value = keyword.value;
  filter_address.value = address.value;
  filter_pref.value = pref.value ? pref.value.id : null;
  filter_typeCategories.value = [];
  console.log(
    "filterCompanies:: ",
    store_position.value,
    keyword.value,
    pref.value,
    address.value
  );

  loading.value = true;
  await gettingEmpDatas(1);
  loading.value = false;
};

const detailEmployeer = async () => {
  var selected_items = filtered_employeers.value.filter((f) => f.checked == true);
  console.log("detailEmployeer:: ", selected_items);
  if (selected_items.length > 1) {
    toast.add({
      severity: "warn",
      summary: "お知らせ",
      detail: "1名の担当者をお選びください。",
      life: 2000,
    });
    return;
  }
  selected_item.value = selected_items[0];
  employee_title.value =
    "担当者詳細   " +
    selected_items[0].company_name_full_short +
    "  : " +
    selected_items[0].branch_name;
  let res_employee = await getEmployeerData(
    selected_items[0].store_pos,
    selected_items[0].item_id
  );
  if (res_employee) {
    var employeer_obj = new EmployeerItem();
    employeer_obj.created_id = res_employee.created_id;
    employeer_obj.updated_id = res_employee.updated_id;
    employeer_obj.company_id = res_employee.company_id;
    employeer_obj.company_branch_id = res_employee.company_branch_id;
    employeer_obj.company_department_id = null;
    employeer_obj.department = res_employee.department;
    employeer_obj.company_department_child_id = null;
    employeer_obj.person_name_second = res_employee.person_name_second;
    employeer_obj.person_name_first = res_employee.person_name_first;
    employeer_obj.person_name_second_kana = res_employee.person_name_second_kana;
    employeer_obj.person_name_first_kana = res_employee.person_name_first_kana;
    employeer_obj.position = "";
    employeer_obj.is_representative = res_employee.is_representative;
    employeer_obj.is_board_member = res_employee.is_board_member;
    employeer_obj.tel1 = res_employee.tel1;
    employeer_obj.tel2 = res_employee.tel2;
    employeer_obj.tel3 = res_employee.tel3;
    employeer_obj.gender = res_employee.gender;
    employeer_obj.roles = res_employee.employment_roles;
    employeer_obj.email = res_employee.email;
    employeer_obj.note = res_employee.note;

    employeer_obj.cardImageObjs = getCardImageDatas(
      selected_items[0].store_pos,
      res_employee.id
    );
    employeer_obj.licenseImageObjs = [];

    employeer_obj.id = res_employee.id;
    employeer_obj.store_pos = selected_items[0].store_pos;

    selected_employee.value = employeer_obj;
  }
  show_detail_dialog.value = true;
};

const getCardImageDatas = async (store_pos, employee_id) => {
  const res = await getImageFiles("1", "public/company");
  console.log("res ", res);
};

const getLicenseImageDatas = async (store_pos, employee_id) => {};

const changeEmployeerData = (_params) => {
  console.log("changeEmployeerData ... ", _params);
  selected_employee.value = _params;
};

const updateEmployeer = async () => {
  console.log("addEmployeer", selected_employee.value);

  const empolyee_id = await updateEmployeerData(selected_employee.value);

  if (empolyee_id) {
    console.log("new employee ", selected_employee.value);
    filtered_employeers.value[selected_item.value.id].email =
      selected_employee.value.email;
    filtered_employeers.value[selected_item.value.id].gender =
      selected_employee.value.gender &&
      genders.filter((g) => g.id == selected_employee.value.gender)[0].name
        ? genders.filter((g) => g.id == selected_employee.value.gender)[0].name
        : "";
    filtered_employeers.value[selected_item.value.id].person_name =
      selected_employee.value.person_name_second +
      " " +
      selected_employee.value.person_name_first;
    filtered_employeers.value[selected_item.value.id].person_name_kana =
      selected_employee.value.person_name_second_kana +
      " " +
      selected_employee.value.person_name_first_kana;
    filtered_employeers.value[selected_item.value.id].position =
      selected_employee.value.position;
    filtered_employeers.value[
      selected_item.value.id
    ].roles = selected_employee.value.roles.map((r) => {
      return employeer_role_names[r - 1].name;
    });

    show_detail_dialog.value = false;
  } else {
  }
  selected_employee.value = null;
  selected_item.value = null;
};

// sms dialog control
const sms_dialog_title = ref("smsを送信");
const show_sms_dialog = ref(false);
const sms_content = ref("");

watch(sms_content, (oldVal, newVal) => {
    alert(oldVal, newVal);
})

const smsEmployeer = () => {
  let selected_items = filtered_employeers.value.filter((f) => f.checked == true);
  console.log("smsEmployeer:: ", selected_items);
  if (selected_items.length) {
    show_sms_dialog.value = true;
  }
};

const sendToApp = () => {
    alert('send to app ');
}

const removeEmployeer = async () => {
  console.log("removeEmployeer:: ");

  if (
    filtered_employeers.value.filter((f) => f.checked == true && f.store_pos == "2")
      .length
  ) {
    toast.add({
      severity: "info",
      summary: "お知らせ",
      detail: "この取引先は削除できません。\nロジスコープで削除して下さい。",
      life: 2500,
    });
    return;
  }
  var selected_items = filtered_employeers.value.filter((f) => f.checked == true);
  var selected_ids = [];
  if (selected_items.length == 0) {
    return;
  } else {
    selected_items.forEach((item) => {
      selected_ids.push(item.item_id);
    });
  }

  const res_employee_del = await removeEmployeerData(selected_ids);
  if (res_employee_del) {
    filtered_employeers.value = filtered_employeers.value.filter(
      (f) => f.checked == false
    );

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
  console.log("val ", val);
  let update_res = await updateUnitSetting(val);
  if (update_res) {
    teble_rows.value = table_row_counts.filter((t) => t.count == update_res)[0].count;
  }
};

const handlePageChange = async (event) => {
  console.log("change page...", event.page);
  loading.value = true;
  await gettingEmpDatas(event.page + 1);
  loading.value = false;
};
</script>

<template>
  <Head title="担当者一覧" />

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
                  class="w-52"
                />
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
                    placeholder="住所"
                  />
                </div>
                <Button
                  icon="pi pi-search"
                  label="検索"
                  class="h-[34px] text-white"
                  @click="filterEmployeers"
                />
              </div>
              <div class="flex items-center gap-4">
                <Button
                  icon="pi pi-ellipsis-h"
                  severity="info"
                  label="詳細"
                  class="h-[34px] text-white"
                  @click="detailEmployeer"
                />
                <Button
                  icon="pi pi-comments"
                  severity="info"
                  label="SMS"
                  class="h-[34px] text-white"
                  @click="smsEmployeer"
                />
                <Button
                  icon="pi pi-trash"
                  severity="danger"
                  label="削除"
                  class="h-[34px] text-white"
                  @click="removeEmployeer"
                />
              </div>
            </div>
            <Paginator
              class="pt-2 main-paginator"
              :rows="teble_rows"
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
              class="flex justify-center mx-auto my-2 pt-6 w-full overflow-x-auto rounded-sm bg-white overflow-y-auto h-fill"
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
                :value="filtered_employeers"
                stripedRows
                :rows="teble_rows"
                :class="`p-datatable-sm`"
                tableStyle="min-width: 50rem; "
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
                    ></i>
                  </template>
                </Column>
                <Column field="register" header="登録者" class="col-data"></Column>
                <Column field="" header="役割" class="col-data">
                  <template #body="{ data }">
                    <Tag
                      v-for="(role, index) in data.roles"
                      :key="index"
                      :value="role.charAt(0)"
                      rounded
                      severity="info"
                    ></Tag>
                    <!-- <span v-for="role, index in data.roles" :key="index"
                                            class="label-circle accent">
                                            {{ role.charAt(0) }}
                                        </span> -->
                  </template>
                </Column>
                <Column field="person_name" header="氏名" class="col-data"></Column>
                <Column field="person_name_kana" header="かな" class="col-data"></Column>
                <Column field="tel" header="携帯" class="col-data"></Column>
                <Column field="email" header="メールアドレス" class="col-data"></Column>
                <Column field="position" header="役職" class="col-data"></Column>
                <Column field="gender" header="性" class="col-data"></Column>
                <Column
                  field="company_name_full_short"
                  header="会社名"
                  class="col-data"
                ></Column>
                <Column field="branch_name" header="支店" class="col-data"></Column>
                <Column field="branch_tel" header="電話" class="col-data"></Column>
                <Column field="branch_city" header="都道府県" class="col-data"></Column>
              </DataTable>
            </div>
            <Dialog
              v-model:visible="show_detail_dialog"
              :header="employee_title"
              :style="{ width: '1250px' }"
              position="center"
              :modal="true"
              :draggable="false"
              :pt="{ root: 'border-none', content: { style: 'overflow:unset' } }"
            >
              <EmployeerComponent
                :type="`update`"
                :employeer_obj="selected_employee"
                @change_employeer="changeEmployeerData"
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
                  @click="updateEmployeer"
                ></Button>
              </div>
            </Dialog>

            <div id="sms_send_dialog">
              <Dialog
                v-model:visible="show_sms_dialog"
                :header="sms_dialog_title"
                :style="{ width: '1250px' }"
                position="center"
                :modal="true"
                :draggable="false"
                :pt="{ root: 'border-none', content: { style: 'overflow:unset' } }"
              >
                <div class="card flex justify-center">
                  <Textarea v-model="sms_content" autoResize rows="5" cols="100" />
                </div>
                <div class="flex justify-center gap-2 mt-5">
                  <Button
                    type="button"
                    label="アプリに転送"
                    class="w-64 h-8"
                    severity="secondary"
                    outlined
                    @click="sendToApp"
                  ></Button>
                </div>
              </Dialog>
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
