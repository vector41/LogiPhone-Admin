<script setup>
import { menu_items } from "@/constant/ConstantConfig";
import { Link } from "@inertiajs/inertia-vue3";
import { computed, ref, onMounted, watch, defineEmits,inject } from "vue";

const props = defineProps({
    items: Array,
    exchanged: Boolean,
});

const logo_img = "images/sitelogo.png";

const items = ref(menu_items);
const is_exchanded = computed(() => props.exchanged)
const sideWidth = ref('220px');

const emits = defineEmits('seleted_menu_item')

is_exchanded.value = inject('is_exchanded')

onMounted(() => {
    console.log('items, ', items)
})

watch(is_exchanded, (newValue, oldValue) => {
    console.log('is_exchanded side ', oldValue, newValue);
    sideWidth.value = newValue ? "220px" : "50px"
    let temp_items = []
    items.value.forEach((item, index)=>{
        item.expand = '';
        temp_items.push(item)
    })
    items.value = temp_items
});



const navigate = (item) => {
    console.log('item', item)
    let temp_items = []
    items.value.forEach((m_item, m_index)=>{
        m_item.items.forEach((s_item, s_index)=>{
            s_item.selected = s_item.label== item.label?true:false
            s_item.background = s_item.label== item.label?'white':'transparent'
            s_item.color = s_item.label== item.label?'#7a93bd':'white'
        })
        temp_items.push(m_item)
    })
    items.value = temp_items
    console.log('navigate', items.value, temp_items)
    emits('seleted_menu_item', item);
}

const showSubMenu = (m_item)=>{
    console.log('m_item11', items);
    let temp_items = []
    items.value.forEach((item, index)=>{
        if(item.label == m_item.label){
            item.expand = item.expand=='show'?'hide':'show'
        }else{
            item.expand = ''
        }
        temp_items.push(item)
    })
    items.value = temp_items
}

const hideSubMenu = () =>{
    let temp_items = []
    items.value.forEach((item, index)=>{
        item.expand = 'hide'
        temp_items.push(item)
    })
    console.log('hideSubMenu',items.value, temp_items)
    items.value = temp_items
}

</script>

<template>
    <div class="h-[calc(100vh-48px)] bg-transparent" :style="{ width: sideWidth }">
        <div class="flex p-2 side-part">
            <div v-show="is_exchanded" class="w-full bg-transparent">
                <ul class="main-item-part">
                    <li class="m-item" v-for="(m_item, m_index) in items" :key="m_index">
                        <div class="lg-div px-3 py-2 gap-2" @click="showSubMenu(m_item)">
                            <i :class="[m_item.icon, '!w-10 !text-white']" style="font-size: 1.3rem"></i>
                            <span class="!text-white">{{ m_item.label }}</span>
                        </div>
                        <ul :class="[m_item.expand, 'lg-item-part']">
                            <Link  class="l-item !pl-8" v-for="(s_item, s_index) in m_item.items" :key="s_index"
                                :href="route(s_item.component)" :style={backgroundColor:s_item.background}>
                                <span class="pl-3" :style={color:s_item.color}>{{ s_item.label }}</span>
                                <span class="pi pi-angle-right text-primary ml-auto px-1" :style={color:s_item.color} />
                            </Link>
                        </ul>
                    </li>
                </ul>
            </div>
            <!-- <div v-show="is_exchanded == false" class="w-full bg-transparent">
                <ul class="main-item-part">
                    <li class="m-item" v-for="(m_item, m_index) in items" :key="m_index">
                        <div class="m-div" @click="showSubMenu(m_item)">
                            <i :class="[m_item.icon, ' !text-white']" style="font-size: 1.3rem"></i>
                        </div>
                        <ul v-show="m_item.expand=='show'" class="sub-item-part">
                            <div class="sub-item-close" @click="hideSubMenu()"><i class="pi pi-times" style="color: white !important"></i></div>
                            <Link class="s-item pl-2" v-for="(s_item, s_index) in m_item.items" :key="s_index"
                                :href="route(s_item.component)" :style={backgroundColor:s_item.background}>
                                <span class="pl-3" :style={color:s_item.color}>{{ s_item.label }}</span>
                                <span class="pi pi-angle-right text-primary ml-auto px-1" :style={color:s_item.color} />
                            </Link>
                        </ul>
                    </li>
                </ul>
            </div> -->
        </div>
    </div>
</template>
<style scoped>
.side-part {
    background-color: #303239;
    height: -webkit-fill-available;
}

.main-item-part {}

.m-item {
    position: relative;
}

.m-div {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 12px;
}

.sub-item-part {
    position: absolute;
    top: 4px;
    width: 200px;
    background-color: #303239;;
    left: 40px;
    padding: 20px 8px 12px 8px;
    z-index: 5;
}

.sub-item-close{
    position: absolute;
    right: 10px;
    top: 10px;
}

.lg-div{
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.lg-div:hover {
    background-color: #a1a1a1;
    border-radius: 4px;
}

.lg-item-part{
    width: -webkit-fill-available;
    background-color: #303239;
    height: 0;
    overflow: hidden;
    padding: 0px 8px 0px 8px;
}

.show {
    height: auto;
    overflow: hidden;
    animation: fadeIn 0.5s ease;
    padding: 12px 8px 8px 8px;
}

.hide {
    height: 0;
    overflow: hidden;
    animation: fadeOut 0.5s ease;
    padding: 0px 8px 0px 8px;
}

@keyframes fadeIn {
  from { height: 0; padding: 0px 8px 0px 8px;}
  to { height: auto; padding: 12px 8px 8px 8px;}
}

@keyframes fadeOut {
  from { height: 110px; padding: 12px 8px 8px 8px;}
  to { height: 0; padding: 0px 8px 0px 8px;}
}

.l-item {
    padding: 10px;
    border-radius: 4px;
    display: inline-block;
}

.l-item:hover{
    background-color: #999 !important;
}

.l-item:hover span{
    color: white !important;
}


.s-item {
    padding: 10px;
    border-radius: 4px;
}

.s-item:hover{
    background-color: #999 !important;
}

.s-item:hover span{
    color: white !important;
}


</style>
