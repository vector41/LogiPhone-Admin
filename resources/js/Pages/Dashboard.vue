<script setup>
import { ref, onMounted, provide } from "vue";
import { getAreaDatas, getUpdatingBranchLP, getUpdatingEmployeeLP, updateUnitSetting } from "@/constant/APIManager";
import { table_row_counts } from "@/constant/ConstantConfig";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
const logo_img = "images/castlee_logo.png";

const selected_item = ref(null)
const is_exchanged = ref(true);
const loading = ref(true);

const company_datas = ref(null)

const page_unit_count = ref();

const total_count = ref(null);


provide('company_datas', company_datas);

onMounted(async () => {
    let area_datas = await getAreaDatas();

    console.log('company_datas ',company_datas.value)

    if (area_datas) {
        let prefectures = area_datas.prefectures;
        let cities = area_datas.cities;
        localStorage.setItem('prefectures', JSON.stringify(prefectures));
        localStorage.setItem('cities', JSON.stringify(cities));
    }
    loading.value = false;
});

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
        page_unit_count.value = table_row_counts.filter(t=>t.id==update_res)[0].count;
        console.log('dfd ',page_unit_count.value)
    }
}

const fetchingData = async() =>{
    let res_data;
    do{
        res_data = await getUpdatingBranchLP();
        console.log('res_data', res_data);
        if(res_data.status=='continue'){
            total_count.value = res_data.max_id;
        }
    }while(res_data.status=='continue');
    total_count.value = res_data.max_id;
}

</script>

<template>
    <GuestLayout>
        <div v-show="loading" class="flex justify-center items-center fixed w-fill h-screen z-50 bg-slate-50 bg-opacity-5">
            <span class="loader"></span>
            <h2 class="text-[28px] font-bold !text-gray-700">データロード中</h2>
        </div>
        <!-- <Button @click="fetchingData">Feaching</Button>
        <div>
            {{ total_count }}
        </div> -->
    </GuestLayout>
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

.loader {
        transform: rotateZ(45deg);
        perspective: 1000px;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        color: #1f73b1;
        position: fixed;
        top: 40%;
        scale: 2;
      }
        .loader:before,
        .loader:after {
          content: '';
          display: block;
          position: absolute;
          top: 0;
          left: 0;
          width: inherit;
          height: inherit;
          border-radius: 50%;
          transform: rotateX(70deg);
          animation: 1s spin linear infinite;
        }
        .loader:after {
          color: #FF3D00;
          transform: rotateY(70deg);
          animation-delay: .4s;
        }

      @keyframes rotate {
        0% {
          transform: translate(-50%, -50%) rotateZ(0deg);
        }
        100% {
          transform: translate(-50%, -50%) rotateZ(360deg);
        }
      }

      @keyframes rotateccw {
        0% {
          transform: translate(-50%, -50%) rotate(0deg);
        }
        100% {
          transform: translate(-50%, -50%) rotate(-360deg);
        }
      }

      @keyframes spin {
        0%,
        100% {
          box-shadow: .2em 0px 0 0px currentcolor;
        }
        12% {
          box-shadow: .2em .2em 0 0 currentcolor;
        }
        25% {
          box-shadow: 0 .2em 0 0px currentcolor;
        }
        37% {
          box-shadow: -.2em .2em 0 0 currentcolor;
        }
        50% {
          box-shadow: -.2em 0 0 0 currentcolor;
        }
        62% {
          box-shadow: -.2em -.2em 0 0 currentcolor;
        }
        75% {
          box-shadow: 0px -.2em 0 0 currentcolor;
        }
        87% {
          box-shadow: .2em -.2em 0 0 currentcolor;
        }
      }

</style>
