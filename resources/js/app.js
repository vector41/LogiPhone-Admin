import './bootstrap';
import '../css/app.css';
import '../scss/app.scss';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

import Button from 'primevue/button';
import Primevue from "primevue/config";
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Row from 'primevue/row';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import ConfirmDialog from 'primevue/confirmdialog';
import ConfirmationService from 'primevue/confirmationservice';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import InputSwitch from 'primevue/inputswitch';
import PanelMenu from 'primevue/panelmenu';
import RadioButton  from 'primevue/radiobutton';
import InputText from 'primevue/inputtext';
import Textarea  from 'primevue/textarea';
import TabMenu  from 'primevue/tabmenu';
import SelectButton from 'primevue/selectbutton';
import ProgressSpinner from 'primevue/progressspinner';
import Tag from 'primevue/tag';
import Paginator from 'primevue/paginator';
import Tree from 'primevue/tree';


import "primevue/resources/themes/aura-light-green/theme.css";
import "primevue/resources/primevue.min.css";
import 'primeicons/primeicons.css'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, app, props, plugin }) {
        return createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(ToastService)
            .use(ConfirmationService)
            .use(Primevue)
            .use(InputNumber, Checkbox,DataTable,Column,Row,Dropdown,Dialog,ConfirmDialog,InputSwitch,
                 Toast, Button,PanelMenu,RadioButton,InputText,Textarea,TabMenu,SelectButton,VueDatePicker,ProgressSpinner,Tag, Paginator )
            .component("InputNumber", InputNumber)
            .component("Checkbox", Checkbox)
            .component("DataTable", DataTable)
            .component("Column", Column)
            .component("Row", Row)
            .component("Dropdown", Dropdown)
            .component("Dialog", Dialog)
            .component("ConfirmDialog", ConfirmDialog)
            .component("Toast", Toast)
            .component("Button", Button)
            .component("InputSwitch", InputSwitch)
            .component("PanelMenu", PanelMenu)
            .component("RadioButton", RadioButton)
            .component("InputText", InputText)
            .component("Textarea", Textarea)
            .component("TabMenu", TabMenu)
            .component("ProgressSpinner", ProgressSpinner)
            .component("SelectButton", SelectButton)
            .component("Tag", Tag)
            .component("Paginator", Paginator)
            .component('VueDatePicker', VueDatePicker )
            .component('Tree', Tree)
            .mount(el);
    },
    progress:{
        color: '#4B5563'
    }
});

