<script setup>
import GuestLayout from "../Layouts/GuestLayout.vue";
import { ref, onMounted, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { NodeService } from "@/service/NodeService";

const nodes = ref(null);
const selectedKey = ref(null);
const toast = useToast();

onMounted(() => {
  NodeService.getTreeNodes().then((data) => (nodes.value = data));
});

const onNodeSelect = (node) => {
  toast.add({
    severity: "success",
    summary: "Node Selected",
    detail: node.label,
    life: 3000,
  });
};

const onNodeUnselect = (node) => {
  toast.add({
    severity: "success",
    summary: "Node Unselected",
    detail: node.label,
    life: 3000,
  });
};

const set = () => {

}
</script>

<template>
  <div>
    <GuestLayout>
      <div class="card flex justify-center">
        <Tree
          v-model:selectionKeys="selectedKey"
          :value="nodes"
          selectionMode="checkbox"
          class="w-full md:w-[30rem]"
        ></Tree>
      </div>
      <div class="card flex justify-center mt-5">
        <Button
          label="確認"
          severity="ok"
          :class="[
            '!h-10',
            branch_show ? 'border !bg-white' : 'text-white',
          ]"
          :outlined="branch_show"
          @click="set"
        />
      </div>
    </GuestLayout>
  </div>
</template>
