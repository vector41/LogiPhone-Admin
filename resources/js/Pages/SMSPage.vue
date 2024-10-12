<script setup>
import GuestLayout from "../Layouts/GuestLayout.vue";
import HeaderBar from "@/Components/HeaderBar.vue";
import SideMenu from "@/Components/SideMenu.vue";
import TitleBar from "@/Components/TitleBar.vue";

import { ref, onMounted, watch, defineEmits, computed } from "vue";
import moment from "moment/moment";
import {
  getSmsHistoriesOfDay,
  getSmsHistoriesOfPeriod,
  getSMSDetailsOfDay,
  getSMSDetailsOfPeriod,
  updateUnitSetting,
  getBranches,
  getBranchEmployees,
  getSearchCallOfDay,
  searchSMSOfDay,
  searchSMSOfPeriod,
} from "@/constant/APIManager";

import axios from "axios";
import { sms_data, sms_detail_history } from "@/constant/ConstantConfig";
import { useToast } from "primevue/usetoast";

const toast = useToast();
// header
const selected_item = ref(null);
const is_exchanged = ref(true);

const select_options = ref(["日計", "詳細"]);
const select_type = ref("日計");

const companies = ref([]);
const company = ref();
const branches = ref([]);
const branch = ref();
const employers = ref([]);
const employer = ref();

const filterDate = ref();
const startDate = ref();
const endDate = ref();

// sms data of all based condition
const smsData = ref([]);

// detail control
const show_detail_dialog = ref(false);
const sms_detail_data = ref([]);
const expandedRowGroups = ref();
const sms_detail_header = ref("詳細情報");
const showDetail = async (data) => {
  //   alert(data);
  if (select_type.value == "日計") {
    let res = await getSMSDetailsOfDay(1, data, filterDate);
    sms_detail_data.value = res;
    show_detail_dialog.value = true;
  } else {
    if (startDate.value != null && endDate.value != null) {
      let res = await getSMSDetailsOfPeriod(2, data, startDate.value, endDate.value);
      //   alert(res);
      if (res == "date error") {
        toast.add({
          severity: "warn",
          summary: "Warn Message",
          detail: "period is more than 1 month",
          life: 3000,
        });
      } else {
        sms_detail_data.value = res;
        show_detail_dialog.value = true;
      }
    }
  }
};

const preMonth = () => {
  filterDate.value = moment(filterDate.value).subtract(1, "days").format("yyyy/MM/DD");
  console.log("pre ", filterDate.value);
};

const nextMonth = () => {
  filterDate.value = moment(filterDate.value).add(1, "days").format("yyyy/MM/DD");
  console.log("next ", filterDate.value);
};

const getCompanySettingData = async () => {
  let res = await axios.get("/company/all");
  let new_data = res.data.result == 1 ? res.data.response : [];
  console.log("getCompanySettingData ", res, new_data);
  return new_data;
};

// lifecycle function
onMounted(async () => {
  filterDate.value = moment().format("YYYY/MM/DD");

  // detail control
  smsData.value = sms_data;
  sms_detail_data.value = sms_detail_history;

  let res_data = await getCompanySettingData();
  console.log("filter", res_data);

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

  if (select_type.value == "日計") {
    let res = await getSmsHistoriesOfDay(1, filterDate._value);
    smsData.value = res;
  } else {
    if (startDate.value != null && endDate.value != null) {
      let res = await getSmsHistoriesOfPeriod(2, startDate.value, endDate.value);
      smsData.value = res;
    }
  }
});

watch(startDate, async (newVal, oldVal) => {
  if (startDate.value != null && endDate.value != null && select_type.value == "詳細") {
    let res = await getSmsHistoriesOfPeriod(2, startDate.value, endDate.value);
    if (typeof res == "string") {
      toast.add({
        severity: "The period is more than 1 month",
        summary: "The period is more than 1 month",
        detail: "Value: ",
        life: 3000,
      });
      return;
    }
    smsData.value = res;
  }
});

watch(endDate, async (newVal, oldVal) => {
  if (startDate.value != null && endDate.value != null && select_type.value == "詳細") {
    let res = await getSmsHistoriesOfPeriod(2, startDate.value, endDate.value);
    if (typeof res == "string") {
      toast.add({
        severity: "The period is more than 1 month",
        summary: "The period is more than 1 month",
        detail: "Value: ",
        life: 3000,
      });
      return;
    }
    smsData.value = res;
  }
});

watch(filterDate, async (newVal, oldVal) => {
  if (filterDate._value.length > 0 && select_type.value == "日計") {
    // alert(filterDate._value);
    let res = await getSmsHistoriesOfDay(1, filterDate._value);
    smsData.value = res;
    // alert(res);
  }
});

watch(company, async (newVal, oldVal) => {
  if (company._value.company_name.length > 0) {
    let res = await getBranches(company._value.id);
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

watch(employer, async (newVal, oldVal) => {
  if (employer._value.name.length) {
    if (select_type.value == "日計") {
      let res = await searchSMSOfDay(1, employer._value.name, filterDate._value);
      smsData.value = res;
    } else {
      if ((startDate.value != null) & (endDate.value != null)) {
        let res = await searchSMSOfPeriod(
          2,
          employer._value.name,
          startDate.value,
          endDate.value
        );
        smsData.value = res;
      }
    }
  }
});

// header
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
    // table_rows.value = table_row_counts.filter((t) => t.count == update_res)[0].count;
    // await gettingComDatas(1);
  }
};
</script>

<template>
  <div>
    <!-- <Toast /> -->
    <GuestLayout>
      <div class="flex items-center mx-10 gap-5">
        <div v-if="select_type == '日計'" class="flex items-center">
          <Button
            icon="pi pi-chevron-left"
            class="h-[34px] pre-day-btn"
            outlined
            @click="preMonth"
          />
          <div>
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
          </div>

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

      <div
        class="flex justify-center mx-auto my-2 w-full overflow-x-auto rounded-sm bg-white overflow-y-auto"
      >
        <DataTable
          :value="smsData"
          paginator
          :paginatorPosition="both"
          :rows="50"
          tableStyle="min-width: 50rem"
          style="width: -webkit-fill-available"
        >
          <Column field="fullName" header="氏名" class="col-data">
            <template #body="{ data }">
              <a
                class="text-center w-auto cursor-pointer text-blue-400 underline underline-offset-2"
                @click="showDetail(data.id)"
              >
                {{ data.fullName }}
              </a>
            </template>
          </Column>
          <Column field="sendCount" header="発信" class="col-data"></Column>
          <Column field="receiveCount" header="着信" class="col-data"></Column>
        </DataTable>
      </div>

      <div>
        <Dialog
          v-model:visible="show_detail_dialog"
          :header="sms_detail_header"
          :style="{ width: '1250px', height: '810px' }"
          position="center"
          :modal="true"
          :draggable="false"
        >
          <DataTable
            v-model:expandedRowGroups="expandedRowGroups"
            :value="sms_detail_data"
            tableStyle="min-width: 50rem"
            expandableRowGroups
            rowGroupMode="subheader"
            groupRowsBy="type"
            @rowgroup-expand="onRowGroupExpand"
            @rowgroup-collapse="onRowGroupCollapse"
            sortMode="single"
            sortField="name"
            :sortOrder="1"
          >
            <template #groupheader="slotProps">
              <span class="align-middle ml-2 font-bold leading-normal">{{
                slotProps.data.type
              }}</span>
            </template>
            <Column field="date" header="日付"></Column>
            <Column field="name" header="名前"></Column>
            <Column field="phone_number" header="電話番号"></Column>
          </DataTable>
        </Dialog>
      </div>
    </GuestLayout>
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
