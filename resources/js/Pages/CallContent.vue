<script setup>
import { usePage, useForm, Head } from "@inertiajs/inertia-vue3";
import { ref, onMounted, watch, defineEmits, computed } from "vue";
import moment from "moment";
import {
  getCompanySettingDatas,
  updateUnitSetting,
  getCallHistoriesOfDay,
  getCallHistoriesOfPeriod,
  getCallDetailsOfDay,
  getCallDetailOfPeriod,
  getBranches,
  getBranchEmployees,
  getSearchCallOfDay,
  getSearchCallOfPeriod,
  callControl,
} from "@/constant/APIManager";

import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import {
  call_data,
  prefectures,
  table_row_counts,
  call_data_detail,
} from "@/constant/ConstantConfig";

import { ja } from "date-fns/locale";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import HeaderBar from "@/Components/HeaderBar.vue";
import SideMenu from "@/Components/SideMenu.vue";
import TitleBar from "@/Components/TitleBar.vue";

const props = defineProps({
  base_datas: Array,
  type: String,
});

const is_exchanged = ref(true);
const selected_item = ref(null);

const loading = ref(false);

const toast = useToast();
const confirm = useConfirm();

const outgoing_incoming = ref([]);
const filterDate = ref();
const select_type = ref("日計");
const select_options = ref(["日計", "詳細"]);

const companies = ref([]);
const company = ref();
const branches = ref([]);
const branch = ref();
const employers = ref([]);
const employer = ref();

const table_rows = ref(50);

const totalCount = ref(0);

// detail(period) start / end
const startDate = ref(null);
const endDate = ref(null);

watch(startDate, async (newVal, oldVal) => {
  console.log(startDate.value, "startDate");
  if (startDate.value != null && endDate.value != null) {
    let res = await getCallHistoriesOfPeriod(2, 1, startDate, endDate);
    if (typeof res == "string") {
      alert("period is more than 1 month");
    } else {
      outgoing_incoming.value = res;
    }
  }
});

watch(endDate, async (newVal, oldVal) => {
  //   loading.value = true;
  console.log(endDate.value, "endDate");
  if (startDate.value != null && endDate.value != null) {
    let res = await getCallHistoriesOfPeriod(2, 1, startDate, endDate);
    if (typeof res == "string") {
      alert("period is more than 1 month");
    } else {
      outgoing_incoming.value = res;
    }
  }
});

watch(filterDate, async (newVal, oldVal) => {
  if (select_type.value == "日計" && filterDate._value.length > 0) {
    let res = await getCallHistoriesOfDay(1, 1, filterDate);
    outgoing_incoming.value = res;
  }
});

watch(employer, async (newVal, oldVal) => {
  //   alert(employer._value.name);
  if (employer._value.name.length) {
    if (select_type.value == "日計") {
      let res = await getSearchCallOfDay(1, employer._value.name, filterDate._value);
      outgoing_incoming.value = res;
    } else {
      if ((startDate.value != null) & (endDate.value != null)) {
        let res = await getSearchCallOfPeriod(
          2,
          employer._value.name,
          startDate.value,
          endDate.value
        );
        outgoing_incoming.value = res;
      }
    }
  }
});

onMounted(async () => {
  outgoing_incoming.value = call_data;

  // call detail
  details.value = call_data_detail;
  filterDate.value = moment().format("YYYY/MM/DD");

  let res_data = await getCompanySettingDatas();
  console.log("filtered ", res_data);

  //   //   console.log("selectType", select_type);
  if (select_type.value === "日計") {
    let callRes = await getCallHistoriesOfDay(1, 1, filterDate);
    console.log("callRes", callRes);
    loading.value = false;
    outgoing_incoming.value = callRes;
  } else {
    let callRes = await getCallHistoriesOfPeriod(2, 1, startDate, endDate);
    outgoing_incoming.value = callRes;
  }

  if (res_data.length > 0) {
    let temp_data = [];
    res_data.forEach((element) =>
      temp_data.push({
        id: element.id,
        company_name: element.company_name,
      })
    );
    companies.value = temp_data;
  }
});

watch(company, async (newVal, oldVal) => {
  //   alert(company._value.company_name);
  if (company._value.company_name.length > 0) {
    let res = await getBranches(company._value.id);
    console.log(res, "branchres");
    if (res.length) branches.value = res;
  }
});

watch(branch, async (newVal, oldVal) => {
  if (branch._value.office.length > 0) {
    console.log(branch);
    let res = await getBranchEmployees(branch._value.id);
    if (res.length > 0) {
      employers.value = res;
    }
  }
});

const preMonth = () => {
  filterDate.value = moment(filterDate.value).subtract(1, "days").format("yyyy/MM/DD");
  console.log("pre ", filterDate.value);
};

const nextMonth = () => {
  filterDate.value = moment(filterDate.value).add(1, "days").format("yyyy/MM/DD");
  console.log("pre ", filterDate.value);
};

const settingMenuItem = (item) => {
  selected_item.value = item;
};

const settingExchanged = (val) => {
  is_exchanged.value = val;
};

const show_detail_dialog = ref(false);

const settingUnitCount = async (val) => {
  console.log("val ", val);
  let update_res = await updateUnitSetting(val);
  if (update_res) {
    table_rows.value = table_row_counts.filter((t) => t.count == update_res)[0].count;
    // await gettingComDatas(1);
  }
};

const showDetail = async (data) => {
  //   alert(data);
  if (select_type.value == "日計") {
    let res = await getCallDetailsOfDay(data, filterDate);
    details.value = res.response;
    show_detail_dialog.value = true;
  } else {
    if (startDate.value != null && endDate.value != null) {
      let res = await getCallDetailOfPeriod(data, startDate.value, endDate.value);
      //   alert(res.response);
      if (res.response == "period error") {
        // alert("period");
        toast.add({
          severity: "warn",
          summary: "Warn Message",
          detail: "period is more than 1 month",
          life: 3000,
        });
      } else {
        details.value = res.response;
        show_detail_dialog.value = true;
      }
    }
  }
};

// call content details
const details = ref();
const expandedRowGroups = ref();
const call_detail = ref("詳細情報");

const onRowGroupExpand = (event) => {
  //   toast.add({
  //     severity: "info",
  //     summary: "Row Group Expanded",
  //     detail: "Value: " + event.data,
  //     life: 3000,
  //   });
};

const onRowGroupCollapse = (event) => {
  //   toast.add({
  //     severity: "success",
  //     summary: "Row Group Collapsed",
  //     detail: "Value: " + event.data,
  //     life: 3000,
  //   });
};

const handlePageChange = async (event) => {
  console.log("change page...", event.page);
  //   loading.value = true;
  //   alert(event.page + 1);
  if (select_type.value == "日計") {
    outgoing_incoming.value = await getCallHistoriesOfDay(1, event.page + 1, filterDate);
  } else {
    if (startDate.value != null && endDate.value != null) {
      let res = await getCallHistoriesOfPeriod(2, event.page + 2, startDate, endDate);
      if (typeof res == "string") {
        alert("period is more than 1 month");
      } else {
        outgoing_incoming.value = res;
      }
    }
  }
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
        <Toast />
        <div class="w-fill px-10 min-h-[500px]">
          <div class="w-full h-full flex flex-col m-2 py-4 mx-auto">
            <div class="flex items-center mx-10 gap-5">
              <div v-if="select_type == '日計'" class="flex items-center">
                <Button
                  icon="pi pi-chevron-left"
                  class="h-[34px] pre-day-btn"
                  outlined
                  @click="preMonth"
                />
                <VueDatePicker
                  v-model="filterDate"
                  locale="ja"
                  :format-locale="ja"
                  format="yyyy年MM月dd日 E"
                  modelType="yyyy/MM/dd"
                  auto-apply
                  :clearable="false"
                  class="!w-fit"
                  week-start="0"
                  input-class-name="!w-[228px] !py-1 !border-green-600 !no-radius !placeholder:text-red-400 text-left !text-[18px] !text-bold w-full"
                  placeholder="日付を選択!"
                />
                <Button
                  icon="pi pi-chevron-right"
                  class="h-[34px] next-day-btn"
                  outlined
                  @click="nextMonth"
                />
              </div>
              <div v-else class="flex items-center mx-10 gap-5">
                <VueDatePicker
                  v-model="startDate"
                  locale="ja"
                  :format-locale="ja"
                  format="yyyy年MM月dd日 E"
                  modelType="yyyy/MM/dd"
                  auto-apply
                  :clearable="false"
                  class="!w-fit"
                  week-start="0"
                  input-class-name="!w-[228px] !py-1 !border-green-600 !no-radius !placeholder:text-red-400 text-left !text-[18px] !text-bold w-full"
                  placeholder="日付を選択!"
                />
                <VueDatePicker
                  v-model="endDate"
                  locale="ja"
                  :format-locale="ja"
                  format="yyyy年MM月dd日 E"
                  modelType="yyyy/MM/dd"
                  auto-apply
                  :clearable="false"
                  class="!w-fit"
                  week-start="0"
                  input-class-name="!w-[228px] !py-1 !border-green-600 !no-radius !placeholder:text-red-400 text-left !text-[18px] !text-bold w-full"
                  placeholder="日付を選択!"
                />
              </div>
              <div class="card flex justify-content-center">
                <SelectButton
                  v-model="select_type"
                  :options="select_options"
                  class="h-[34px]"
                  aria-labelledby="basic"
                />
              </div>
            </div>

            <div class="flex items-center mx-10 pt-4 gap-5">
              <Dropdown
                v-model="company"
                :options="companies"
                option-label="company_name"
                placeholder="所属会社名い"
                class="w-56"
              />
              <Dropdown
                v-model="branch"
                :options="branches"
                option-label="office"
                placeholder="所属営業所名"
                class="w-56"
              />
              <Dropdown
                v-model="employer"
                :options="employers"
                option-label="name"
                placeholder="氏 名"
                class="w-48"
              />
            </div>

            <div>
              <!-- <Paginator
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
              </Paginator> -->
            </div>

            <div
              class="flex justify-center mx-auto my-2 w-full overflow-x-auto rounded-sm bg-white overflow-y-auto"
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
                :value="outgoing_incoming"
                :paginatorPosition="both"
                :rows="table_rows"
                tableStyle="min-width: 50rem; "
                style="width: -webkit-fill-available"
              >
                <Column field="full_name" header="氏名" class="col-data">
                  <template #body="{ data }">
                    <a
                      class="text-center w-auto cursor-pointer text-blue-400 underline underline-offset-2"
                      @click="showDetail(data.id)"
                    >
                      {{ data.full_name }}
                    </a>
                  </template>
                </Column>
                <Column field="outgoing_call" header="発信" class="col-data"></Column>
                <Column
                  field="call_time_outgoing"
                  header="通話時間"
                  class="col-data"
                ></Column>
                <Column field="incoming_call" header="着信" class="col-data"></Column>
                <Column
                  field="call_time_incoming"
                  header="通話時間"
                  class="col-data"
                ></Column>
              </DataTable>
            </div>

            <div>
              <Dialog
                v-model:visible="show_detail_dialog"
                :header="call_detail"
                :style="{ width: '1250px', height: '810px' }"
                position="center"
                :modal="true"
                :draggable="false"
              >
                <DataTable
                  v-model:expandedRowGroups="expandedRowGroups"
                  :value="details"
                  tableStyle="min-width: 50rem"
                  expandableRowGroups
                  rowGroupMode="subheader"
                  groupRowsBy="type"
                  @rowgroup-expand="onRowGroupExpand"
                  @rowgroup-collapse="onRowGroupCollapse"
                  sortMode="single"
                  sortField="call_name"
                  :sortOrder="1"
                >
                  <template #groupheader="slotProps">
                    <span class="align-middle ml-2 font-bold leading-normal">{{
                      slotProps.data.type
                    }}</span>
                  </template>
                  <Column field="call_name" header="発信者"></Column>
                  <Column field="receive_name" header="着信者"></Column>
                  <Column field="start_time" header="スタート"></Column>
                  <Column field="end_time" header="終わり"></Column>
                  <Column field="period" header="通話時間"></Column>
                </DataTable>
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

.pre-day-btn {
  border-radius: 4px 0 0 4px !important;
  border-color: #10b981 !important;
}
.next-day-btn {
  border-radius: 0 4px 4px 0 !important;
  border-color: #10b981 !important;
}
.no-radius {
  border-radius: 0 !important;
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
