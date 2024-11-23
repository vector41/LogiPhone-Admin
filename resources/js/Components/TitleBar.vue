<script setup>
import { getUnitSetting } from "@/constant/APIManager";
import { table_row_counts } from "@/constant/ConstantConfig";
import { Link } from "@inertiajs/inertia-vue3";
import { computed, ref, onMounted, watch, defineEmits } from "vue";

const props = defineProps({
  item: Object,
  title: String,
});

// const logo_img = "images/sitelogo.png";
const emit = defineEmits("setting_unit_count");

const selected_item = computed(() => props.item);
const unit_counts = ref(table_row_counts);
const unit_count = ref();
const show_setting_dialog = ref(false);

onMounted(async () => {
  let unit_res = await getUnitSetting();
  if (unit_res) {
    unit_count.value = table_row_counts.filter((u) => u.count == unit_res)[0];
    console.log("init setting... ", unit_res);
    emit("setting_unit_count", unit_res);
  }
});

const updateSetting = () => {
  let setting_val = unit_count.value.count;
  console.log('setting_unit_count ', setting_val);

  emit("setting_unit_count", setting_val);
  show_setting_dialog.value = false;
};
</script>

<template>
  <div class="w-full flex !h-12 !min-h-12 justify-between items-center px-6 bg-gray-100">
    <label class="text-[24px] text-gray-600 font-bold">{{ props.title }}</label>
    <div class="flex justify-center items-center">
      <Button
        class="float"
        text
        severity="secondary"
        icon="pi pi-cog"
        label="表示設定"
        aria-label="Filter"
        @click="show_setting_dialog = true"
      />
      <Link :href="route('logout')" method="post">
        <a class="flex items-center justify-center mx-2 duration-150">
          <i class="pi pi-sign-out"></i>
          <span class="hidden ls:flex text-center">ログアウト</span>
        </a>
      </Link>
    </div>
  </div>
  <Dialog
    v-model:visible="show_setting_dialog"
    header="表示設定"
    position="center"
    :modal="true"
    :draggable="false"
  >
    <div class="w-fill flex items-center gap-5">
      <label>1ページあたりの表示設定</label>
      <Dropdown
        v-model="unit_count"
        :options="unit_counts"
        option-label="count"
        placeholder="50"
        class="w-40 mx-auto"
      />
    </div>
    <div class="flex justify-center gap-2 pt-5">
      <Button
        type="button"
        label="更新"
        size="small"
        class="text-white w-32"
        @click="updateSetting"
      />
    </div>
  </Dialog>
</template>
<style scoped></style>
