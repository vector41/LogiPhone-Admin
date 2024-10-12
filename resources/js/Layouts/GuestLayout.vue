<script setup>
import { ref, onMounted } from "vue";
import SideMenu from "@/Components/SideMenu.vue";
import HeaderBar from "@/Components/HeaderBar.vue";
import TitleBar from "@/Components/TitleBar.vue";
import { updateUnitSetting } from "@/constant/APIManager";
import { table_row_counts } from "@/constant/ConstantConfig";

defineProps({
    page_unit_count: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
const logo_img = "images/castlee_logo.png";

const selected_item = ref(null)
const is_exchanged = ref(true);
const loading = ref(true);
const page_unit_count = ref();

onMounted(() => {
    loading.value=false;
});

const settingMenuItem = (item) => {
    selected_item.value = item;
}

const settingExchanged = (val)=>{
    is_exchanged.value = val;
}

const settingUnitCount = async (val)=>{
    console.log('first_unit_count ', val);
    let update_res = await updateUnitSetting(val);
    if(update_res){
        page_unit_count.value = table_row_counts.filter(t=>t.count==update_res)[0].count;
        console.log('dfd ',page_unit_count.value)
    }
}

</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col h-screen overflow-hidden">
        <HeaderBar :item="selected_item" :exchanged="is_exchanged" @setting_exchanged = "settingExchanged"/>
        <div class="flex w-full h-[calc(100vh-48px)]">
            <div class="shrink items-center">
                <SideMenu @seleted_menu_item="settingMenuItem" :exchanged = "is_exchanged"/>
            </div>
            <div class="flex flex-col items-center content-part">
                <TitleBar :item="selected_item" @setting_unit_count = "settingUnitCount"/>
                <Toast position="center"/>
                <div class="w-fill px-10 min-h-[500px]">
                    <slot :page_unit_count="page_unit_count" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.content-part {
    width: inherit;
}

.bg-gray-100 {
    background-color: #f7fafc;
    background-color: rgba(247, 250, 252, var(--tw-bg-opacity));
}

.border-gray-200 {
    border-color: #edf2f7;
    border-color: rgba(237, 242, 247, var(--tw-border-opacity));
}

.text-gray-400 {
    color: #cbd5e0;
    color: rgba(203, 213, 224, var(--tw-text-opacity));
}

.text-gray-500 {
    color: #a0aec0;
    color: rgba(160, 174, 192, var(--tw-text-opacity));
}

.text-gray-600 {
    color: #718096;
    color: rgba(113, 128, 150, var(--tw-text-opacity));
}

.text-gray-700 {
    color: #4a5568;
    color: rgba(74, 85, 104, var(--tw-text-opacity));
}

.text-gray-900 {
    color: #1a202c;
    color: rgba(26, 32, 44, var(--tw-text-opacity));
}

</style>
