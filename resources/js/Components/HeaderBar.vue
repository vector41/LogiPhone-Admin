<script setup>
import { computed, ref, onMounted, watch, defineEmits, provide } from "vue";

const props = defineProps({
    item: Object,
    exchanged:Boolean,
});

const logo_img = ref("images/sitelogo.png");
const category = ref(null);
const subcategory = ref(null);
const is_exchanded = ref(props.exchanged);

const sideWidth = ref('220px');
const maxWidth = ref('85%');

const selected_item = computed(() => props.item);
const emits = defineEmits('seleted_menu_item')

onMounted(() => {
    if (selected_item) {
        console.log('mount ', selected_item.value, is_exchanded.value)
        category.value = selected_item.value
        subcategory.value = selected_item.value
    }
})

watch(selected_item, (newValue, oldValue) => {
    console.log('selected_item ', oldValue, newValue);
    category.value = newValue.label;
    subcategory.value = newValue.label;
});

const toggleExchaned=()=>{
    is_exchanded.value = !is_exchanded.value
    logo_img.value = is_exchanded.value?"images/sitelogo.png":"images/close_logo.png"
    sideWidth.value = is_exchanded.value?"220px":"50px"
    maxWidth.value = is_exchanded.value?"95%":"70%"
    console.log('toggleExchaned',is_exchanded.value)
    emits('setting_exchanged', is_exchanded.value);
}

</script>

<template>
    <div class="w-full h-12 flex bg-transparent sticky top-0">
        <div class="flex justify-center items-center bg-gray-100" :style="{width:sideWidth}">
            <img :style="{maxWidth:maxWidth}" :src="logo_img" />
        </div>
        <div class="flex justify-start items-center pl-4 gap-3">
            <Button icon="pi pi-bars" @click="toggleExchaned" :pt="{
                icon: { class: 'text-[20px]' }
            }" link />
            <div v-if="selected_item" class="flex items-center gap-1">
                <i class="pi pi-home" style="font-size: 1.5rem"></i>
                <label class="text-[14px]">HOME</label>
            </div>
            <div v-if="selected_item" class="flex items-center gap-1">
                <i class="pi pi-angle-right" style="font-size: 1.5rem"></i>
                <label class="text-[14px]">{{ subcategory }}</label>
            </div>
        </div>
    </div>
</template>
<style scoped></style>
