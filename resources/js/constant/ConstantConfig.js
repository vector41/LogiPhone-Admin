export const DETAIL_ADD = 0;
export const DETAIL_CALCULATE_1 = 1;
export const DETAIL_SAVE_1 = 2;
export const DETAIL_CALCULATE_2 = 3;
export const DETAIL_SAVE_2 = 4;

export const DETAIL_ORIGIN = "origin";
export const DETAIL_CONTINUE = "continue";
export const DETAIL_UPDATE = "update";

export const DETAIL_CALL = "call";
export const DETAIL_PUT = "put";

export const IMAGE_ROOT_PATH = "http://localhost:8000/";

export const prefectures = [
    { id: 1, value: "北海道" },
    { id: 2, value: "青森県" },
    { id: 3, value: "岩手県" },
    { id: 4, value: "宮城県" },
    { id: 5, value: "秋田県" },
    { id: 6, value: "山形県" },
    { id: 7, value: "福島県" },
    { id: 8, value: "茨城県" },
    { id: 9, value: "栃木県" },
    { id: 10, value: "群馬県" },
    { id: 11, value: "埼玉県" },
    { id: 12, value: "千葉県" },
    { id: 13, value: "東京都" },
    { id: 14, value: "神奈川県" },
    { id: 15, value: "新潟県" },
    { id: 16, value: "富山県" },
    { id: 17, value: "石川県" },
    { id: 18, value: "福井県" },
    { id: 19, value: "山梨県" },
    { id: 20, value: "長野県" },
    { id: 21, value: "岐阜県" },
    { id: 22, value: "静岡県" },
    { id: 23, value: "愛知県" },
    { id: 24, value: "三重県" },
    { id: 25, value: "滋賀県" },
    { id: 26, value: "京都府" },
    { id: 27, value: "大阪府" },
    { id: 28, value: "兵庫県" },
    { id: 29, value: "奈良県" },
    { id: 30, value: "和歌山県" },
    { id: 31, value: "鳥取県" },
    { id: 32, value: "島根県" },
    { id: 33, value: "岡山県" },
    { id: 34, value: "広島県" },
    { id: 35, value: "山口県" },
    { id: 36, value: "徳島県" },
    { id: 37, value: "香川県" },
    { id: 38, value: "愛媛県" },
    { id: 39, value: "高知県" },
    { id: 40, value: "福岡県" },
    { id: 41, value: "佐賀県" },
    { id: 42, value: "長崎県" },
    { id: 43, value: "熊本県" },
    { id: 44, value: "大分県" },
    { id: 45, value: "宮崎県" },
    { id: 46, value: "鹿児島県" },
    { id: 47, value: "沖縄県" },
    { id: 52, value: "海外" },
];

export const employeer_role_names = [
    { id: 1, name: "作業員" },
    { id: 2, name: "乗務員" },
    { id: 3, name: "事務員" },
    { id: 4, name: "配車" },
    { id: 5, name: "管理者" }
];

export const genders = [
    { id: 1, name: "男性" },
    { id: 2, name: "女性" },
    { id: 3, name: "不明" }
];

export const legal_personality = [
    { id: 1, name: "株式会社" },
    { id: 2, name: "有限会社" },
    { id: 3, name: "協同組合" },
    { id: 4, name: "合同会社" },
    { id: 5, name: "上記以外" }
];

export const legal_personality_position = [
    { id: 1, name: "会社名の前" },
    { id: 2, name: "会社名の後" }
];

export const regist_site = [
    { id: 1, name: "WebKIT" },
    { id: 2, name: "ローカルネット" },
    { id: 3, name: "トラボックス" }
];

export const store_postions = [
    { id: 0, name: "全て表示" },
    { id: 2, name: "ロジスコープ表示" },
    { id: 1, name: "ロジフォン表示" }
];

export const call_data = [
    // {
    //     id:1,
    //     full_name:"全て",
    //     outgoing_call:2,
    //     call_time_outgoing:10,
    //     incoming_call:3,
    //     call_time_incoming:20,
    // },
    // {
    //     id:2,
    //     full_name:"全て",
    //     outgoing_call:2,
    //     call_time_outgoing:10,
    //     incoming_call:3,
    //     call_time_incoming:20,
    // },
    // {
    //     id:3,
    //     full_name:"全て",
    //     outgoing_call:2,
    //     call_time_outgoing:10,
    //     incoming_call:3,
    //     call_time_incoming:20,
    // },
    // {
    //     id:4,
    //     full_name:"全て",
    //     outgoing_call:2,
    //     call_time_outgoing:10,
    //     incoming_call:3,
    //     call_time_incoming:20,
    // },
    // {
    //     id:5,
    //     full_name:"全て",
    //     outgoing_call:2,
    //     call_time_outgoing:10,
    //     incoming_call:3,
    //     call_time_incoming:20,
    // },
]

export const call_data_detail = [
    // {
    //     call_name: "全て",
    //     receive_name: "取引先",
    //     start_time: "2024-02-17 09:10:10",
    //     end_time: "2024-02-17 09:12:10",
    //     period: "2",
    //     type: "発信" // outgoing , balsin
    // },
    // {
    //     call_name: "全て",
    //     receive_name: "取引先",
    //     start_time: "2024-02-17 09:10:10",
    //     end_time: "2024-02-17 09:12:10",
    //     period: "2",
    //     type: "着信" // incoming, chaksin
    // }
]

export const sms_data = [
    {
        id: 1,
        fullName: "小前田　孝",
        sendCount: "10回",
        receiveCount: "20回",
    },
    {
        id: 2,
        fullName: "真田 幸広",
        sendCount: "5回",
        receiveCount: "10回",
    },
    {
        id: 3,
        fullName: "松井 善行",
        sendCount: "3回",
        receiveCount: "5回",
    }
]

export const sms_detail_history = [
    {
        date: "2024-01-10",
        name: "取引先",
        phone_number: "029-29-1920",
        type: "送信"
    },
    {
        date: "2024-01-12",
        name: "取引",
        phone_number: "029-29-1920",
        type: "受け取った"
    }
]

export const sms_histories = [

]

export const table_row_counts = [
    { id: 1, count: 50 },
    { id: 2, count: 100 },
    { id: 3, count: 300 },
    { id: 4, count: 500 },
    { id: 5, count: 1000 }
];

export const menu_items = [
    {
        label: '取引先',
        icon: 'pi pi-envelope',
        expand: '',
        items: [
            {
                label: '取引先一覧',
                icon: 'pi pi-bars',
                selected: false,
                background: 'transparent',
                color: 'white',
                component: 'companies'
            },
            {
                label: '取引先追加',
                icon: 'pi pi-plus',
                selected: false,
                background: 'transparent',
                color: 'white',
                component: 'companyreg'
            }
        ]
    },
    {
        label: '担当者',
        icon: 'pi pi-user',
        expand: '',
        items: [
            {
                label: '担当者一覧',
                icon: 'pi pi-bars',
                selected: false,
                background: 'transparent',
                color: 'white',
                component: 'emplyeers'
            },
            {
                label: '担当者追加',
                icon: 'pi pi-user-plus',
                selected: false,
                background: 'transparent',
                color: 'white',
                component: 'addemplyeer'
            }
        ]
    },

    {
        label: '通話履歴',
        icon: 'pi pi-phone',
        expand: '',
        items: [
            {
                label: '発信/着信',
                icon: 'pi pi-phone',
                selected: false,
                background: 'transparent',
                color: 'white',
                component: 'callhistories'
            }
        ]
    },
    {
        label: 'SMS履歴',
        icon: 'pi pi-comments',
        expand: '',
        items: [
            {
                label: '発信/着信',
                icon: 'pi pi-comments',
                selected: false,
                background: 'transparent',
                color: 'white',
                component: 'sendsms'
            }
        ]
    },
    {
        label: '会社設定',
        icon: 'pi pi-cog',
        expand: '',
        items: [
            {
                label: '会社設定',
                icon: 'pi pi-user-plus',
                selected: false,
                background: 'transparent',
                color: 'white',
                component: 'companyinfo'
            },
            {
                label: '社員一覧',
                icon: 'fa-solid fa-list',
                selected: false,
                background: 'transparent',
                color: 'white',
                component: 'members'
            },
            {
                label: '電話帳設定',
                icon: 'fa-solid fa-list',
                selected: false,
                background: 'transparent',
                color: 'white',
                component: 'phoneBook'
            }
        ]
    }
];

export const typesCategories = [
    { id: 1, name: "【運送】運送会社", checked: false },
    { id: 2, name: "【直荷】直荷主会社", checked: true },
    { id: 3, name: "【倉庫】倉庫会社", checked: false },
    { id: 4, name: "【取扱】情報会社", checked: true },
    { id: 5, name: "トラック協会・組合", checked: false },
    { id: 6, name: "自動車会社", checked: false },
    { id: 7, name: "異業種会社", checked: true },
];
