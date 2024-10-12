<script setup>
import { IMAGE_ROOT_PATH } from '@/constant/ConstantConfig';
import { computed, onMounted, defineEmits, inject, ref, watch } from 'vue';

const props = defineProps({
    field_id: Number,
    one_status: Boolean,
    img_file:Object,
});

const imageUrl = ref(null);
const file = ref(null);
const _id = ref(props.field_id);
const removable = ref(props.one_status);
const one_status = computed(() => props.one_status)

onMounted(async() => {
    if(props.img_file) {
        const file_res = await fetch(props.img_file);
        const blob = await file_res.blob();
        file.value = new File([blob], 'test.png', { type: blob.type });
        imageUrl.value = URL.createObjectURL(file.value);
    }
});

const emit = defineEmits(['add_obj', 'remove_obj', 'assign_file'])

const handleFileChange = (event) => {
    var temp_file = event.target.files[0];
    console.log('file', temp_file)
    if (temp_file) {
        const reader = new FileReader();
        reader.onload=()=>{
            const img = new Image();
            img.onload=()=>{
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const targetRatio = 224 / 208;
                let newWidth, newHeight;
                if (img.width / img.height > targetRatio) {
                    newWidth = img.height;
                    newHeight = img.height * targetRatio;
                } else {
                    newWidth = img.width / targetRatio;
                    newHeight = img.width;
                }
                canvas.width = newWidth;
                canvas.height = newHeight;
                ctx.drawImage(img, 0, 0, newWidth, newHeight);
                canvas.toBlob((blob) => {
                    const rotatedFile = new File([blob], temp_file.name, { type: temp_file.type });
                    file.value = rotatedFile;
                    imageUrl.value = URL.createObjectURL(file.value);
                }, file.type);
            }
            img.src = reader.result;
        };
        reader.readAsDataURL(temp_file);
        emit('assign_file', { id: _id.value, file: file.value });
    } else {
        imageUrl.value = null;
    }
}

watch(one_status, (newValue, oldValue) => {
    console.log('props.one_status ', newValue, oldValue)
    removable.value = newValue;
})

const addImgObj = () => {
    emit('add_obj', null);
}

const removeImgObj = () => {
    console.log('remove_obj', _id.value);
    emit('remove_obj', _id.value);
}

const ratatedImage = ref(null);

const rotateLeft = () =>{
   const reader = new FileReader();
   reader.onload=()=>{
      const img = new Image();
      img.onload=()=>{
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const targetRatio = 224 / 208;
        let newWidth, newHeight;
        if (img.width / img.height > targetRatio) {
            newWidth = img.height;
            newHeight = img.height * targetRatio;
        } else {
            newWidth = img.width / targetRatio;
            newHeight = img.width;
        }
        canvas.width = newHeight ;
        canvas.height = newWidth ;
        ctx.rotate(Math.PI / 2);
        ctx.drawImage(img, 0, -newHeight, newWidth, newHeight);
        canvas.toBlob((blob) => {
            const rotatedFile = new File([blob], file.name, { type: file.type });
            file.value = rotatedFile;
            imageUrl.value = URL.createObjectURL(file.value);
          }, file.type);
    }
    img.src = reader.result;
   };
   reader.readAsDataURL(file.value);
}

const rotateRight = () =>{
    const reader = new FileReader();
    reader.onload=()=>{
        const img = new Image();
        img.onload=()=>{
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const targetRatio = 224 / 208;
            let newWidth, newHeight;
            if (img.width / img.height > targetRatio) {
                newWidth = img.height;
                newHeight = img.height * targetRatio;
            } else {
                newWidth = img.width / targetRatio;
                newHeight = img.width;
            }
            canvas.width = newHeight;
            canvas.height =newWidth ;
            ctx.rotate(-Math.PI / 2);
            ctx.drawImage(img, -newWidth, 0, newWidth, newHeight);
            canvas.toBlob((blob) => {
                const rotatedFile = new File([blob], file.name, { type: file.type });
                file.value = rotatedFile;
                imageUrl.value = URL.createObjectURL(file.value);
            }, file.type);
        }
    img.src = reader.result;
   };
   reader.readAsDataURL(file.value);
}

</script>

<template>
    <div class="w-56 min-h-32 h-max bg-gray-200 relative">
        <div class="absolute w-56 p-2 z-10">
            <button v-if="removable == true" @click="removeImgObj"
                class="bg-amber-700 w-6 h-6 flex items-center justify-center rounded-full float-left cursor-pointer">
                <i class="pi pi-minus text-white text-[16px]" />
            </button>
            <button @click="addImgObj"
                class="bg-green-500 w-6 h-6 flex items-center justify-center rounded-full float-right cursor-pointer">
                <i class="pi pi-plus text-white text-[16px]" />
            </button>
        </div>
        <section class="h-fill">
            <label class="relative min-h-32">
                <input type="file" style="display: none;" @change="handleFileChange">
                <canvas ref="canvas" style="display: none;"></canvas>
                <img v-show="file" class="h-52 w-fill" :src="imageUrl" alt="Uploaded Image" />
                <div v-show="file" class="absolute w-56 p-2 flex justify-center bottom-4 gap-2">
                    <button class="bg-amber-700 flex items-center justify-center h-[26px] px-4 rounded-md  text-white" @click="rotateLeft">
                        <i class="pi pi-sync text-white text-[16px]" />右回転
                    </button>
                    <button class="bg-amber-700  flex items-center justify-center h-[26px] px-4 rounded-md  text-white" @click="rotateRight">
                        <i class="pi pi-sync text-white text-[16px] -scale-x-100" />左回転
                    </button>
                </div>
                <div v-show="file == null" class="p-4 flex flex-col justify-center">
                    <p class="text-[36px] mx-auto">＋</p>
                    <p class="pt-2 text-[13px]">ここからファイルを選択して登録<br>ここにファイルをドロップして登録</p>
                </div>
            </label>
        </section>
    </div>
</template>

<style scoped></style>
