import moment from "moment/moment";
import axios from "axios";


const getAreaDatas = async () => {
    let res = await axios.get("/area");
    console.log('getAreaDatas ', res);
    let new_datas = res.data.result == 1 ? res.data : null;
    return new_datas;
};

const getUnitSetting = async () => {
    let res = await axios.get('/setting');
    console.log('getUnitSetting ', res);
    let new_datas = res.data.result == 1 ? res.data.unit_count : 50;
    return new_datas;
}

const updateUnitSetting = async (_unit_count) => {
    let res = await axios.post('/setting', { unit_count: _unit_count });
    console.log('updateUnitSetting ', res);
    let new_datas = res.data.result == 1 ? res.data.unit_count : 50;
    return new_datas;
}

const getAddressData = async (_zip) => {
    let res = await axios.get("/area/zip", {
        params: {
            zip: _zip
        }
    });
    console.log('getAddressData ', res);
    let new_datas = res.data.result == 1 ? res.data : null;
    return new_datas;
};

const getCompanyDatas = async (_page, _store_pos, _type_cates, _keyword, _pref, _address) => {

    let res = await axios.get("/suppliers/company/all", {
        params: {
            page: _page,
            store_pos: _store_pos,
            type_cates: _type_cates,
            keyword: _keyword,
            pref: _pref,
            address: _address
        }
    });

    console.log('getCompanyDatas ', res);
    let new_datas = res.data.result == 1 ? res.data : null;
    return new_datas;
};

const removeBranchData = async (_ids) => {
    let res = await axios.post("/suppliers/company/remove", {
        ids: _ids
    });
    console.log('removeBranchData ', res);
    let new_datas = res.data.result == 1 ? 'ok' : null;
    return new_datas;
}

const getEmployeerData = async (store_pos, _id) => {
    let res = await axios.get("/suppliers/employees/get", {
        params: {
            store_pos: store_pos,
            _id, _id
        }
    });
    console.log('getEmployeerData ', res);
    let new_datas = res.data.result == 1 ? res.data.response : null;
    return new_datas;
}

const getEmployeerDatas = async (_page, _store_pos, _type_cates, _keyword, _pref, _address) => {
    let res = await axios.get("/suppliers/employees/all", {
        params: {
            page: _page,
            store_pos: _store_pos,
            type_cates: _type_cates,
            keyword: _keyword,
            pref: _pref,
            address: _address
        }
    });
    console.log('getEmployeerDatas ', res);
    let new_datas = res.data.result == 1 ? res.data : null;
    return new_datas;
};

const removeEmployeerData = async (_ids) => {
    let res = await axios.post("/suppliers/employees/remove", {
        ids: _ids
    });
    console.log('removeEmployeerData ', res);
    let new_datas = res.data.result == 1 ? 'ok' : null;
    return new_datas;
}

const getCompanyEmployeerDatas = async (_page) => {
    let res = await axios.get("/companies/employees/all", {
        params: {
            page: _page
        }
    });
    console.log('getCompanyEmployeerDatas ', res);
    let new_datas = res.data.result == 1 ? res.data : null;
    return new_datas;
};

const getCompanySettingDatas = async () => {

    let res = await axios.get("/company/all");
    let new_datas = res.data.result == 1 ? res.data.response : [];
    console.log('getCompanySettingDatas ', res, new_datas);
    return new_datas;
};

const saveCompanyLPData = async (_company) => {
    let res = await axios.post("/suppliers/company/savelp", { company: _company });
    let new_datas = res.data.result == 1 ? res.data.id : null;
    console.log('saveCompanyLPData ', res, new_datas);
    return new_datas;
};

const getBranchData = async (_type, _id) => {
    let res = await axios.get("/suppliers/branch/get", {
        params: {
            type: _type,
            _id, _id
        }
    });
    let new_datas = res.data.result == 1 ? res.data.id : null;
    console.log('getBranchData ', res, new_datas);
    return new_datas;
}

const getLPBranchDatas = async (_branch_id) => {
    let res = await axios.get("/suppliers/branch/list_lp");
    console.log('getLPBranchDatas ', res);
    let new_datas = res.data.result == 1 ? res.data : null;
    return new_datas;
};

const getCompanyFromBranchData = async (_branch_id) => {
    let res = await axios.get("/suppliers/company/get_from_branch", {
        params: {
            branch_id: _branch_id
        }
    });
    console.log('getCompanyFromBranchData ', res);
    let new_datas = res.data.result == 1 ? res.data.id : null;
    return new_datas;
};

const updateBranchData = async (_branch) => {
    let res = await axios.post("/suppliers/branch/save", { branch: _branch });
    console.log('updateBranchData ', res);
    let new_datas = res.data.result == 1 ? res.data.id : null;
    return new_datas;
}

const saveEmployeeLPData = async (_employee) => {
    let res = await axios.post("/suppliers/employees/save_lp", { employee: _employee });
    console.log('saveEmployeeLPData ', res);
    let new_datas = res.data.result == 1 ? res.data.id : null;
    return new_datas;
};

const getEmployyFromBranch = async (_type, _id) => {
    let res = await axios.get("/suppliers/employees/get_from_branch", {
        params: {
            type: _type,
            id: _id
        }
    });
    console.log('getEmployyFromBranch ', res);
    let new_datas = res.data.result == 1 ? res.data.response : [];
    return new_datas;
}

const updateEmployeerData = async (_employeer) => {
    let res = await axios.post("/suppliers/employees/update", { employeer: _employeer });
    console.log('updateEmployeerData ', res);
    let new_datas = res.data.result == 1 ? res.data.id : null;
    return new_datas;
}

const getImageFiles = async (store_pos, directory) => {
    let res = await axios.get("/image/get", {
        params: {
            store_pos: store_pos,
            directory: directory
        }
    });
    console.log('getImageFiles ', res.data);
    return res.data;
}

const getUpdatingBranchLP = async () => {
    let res = await axios.get("/proccess/ls_branch");
    console.log('getUpdatingBranchLP ', res.data);
    return res.data;
}

const getUpdatingEmployeeLP = async () => {
    let res = await axios.get("/proccess/ls_employee");
    console.log('getUpdatingBranchLP ', res.data);
    return res.data;
}


// call api
const getCallHistoriesOfDay = async (role, page, date) => {
    console.log('callHistory', role, page, date._value);
    let res = await axios.get('/call/getCallHistories', {
        params: {
            role, page,
            date: date._value
        }
    });

    console.log('callHistories', res);
    if (res.status == 200) {
        return res.data;
    }
}

const getCallHistoriesOfPeriod = async (role, page, startDate, endDate) => {
    console.log('period', startDate);
    let res = await axios.get('/call/getCallHistories', {
        params: {
            role, page,
            startDate, endDate
        }
    });

    console.log('callHistoriesPeriod', res);
    if (res.result == 200)
        return res.data;
}

const getCallDetailsOfDay = async (userID, date) => {
    console.log('user', userID);

    let res = await axios.post('/call/getCallDetailsOfDay', {
        userID,
        date: date._value
    });

    console.log('detailOfDay', res);
    if (res.status == 200) {
        return res.data;
    }
    // if(res.result == 1)  return
    // let res = await axios.get()
}

const getCallDetailOfPeriod = async (userID, startDate, endDate) => {
    let res = await axios.post('/call/getCallDetailsOfPeriod', {
        userID, startDate, endDate
    });

    console.log(res, 'detail of period');
    if (res.status == 200) {
        return res.data;
    }
}

const getSearchCallOfDay = async (role, userName, date) => {
    let res = await axios.post('/call/getSearchCallOfDay', {
        role, userName, date
    })

    if (res.status == 200) return res.data;
}

const getSearchCallOfPeriod = async (role, userName, startDate, endDate) => {
    let res = await axios.post('/call/getSearchCallOfPeriod', {
        role, userName, startDate, endDate
    })

    if (res.status == 200) return res.data;
}


// sms control
const getSmsHistoriesOfDay = async (role, date) => {
    let res = await axios.get('/sms/getSmsHistories', {
        role, date
    })

    if (res.status == 200) {
        // alert(res.data)
        console.log('SmsHistoryOfDay', res);
        return res.data;
    }

}

const getSmsHistoriesOfPeriod = async (role, startDate, endDate) => {
    let res = await axios.get('/sms/getSmsHistories', {
        role, startDate, endDate
    })

    if (res.status == 200) {
        // alert(res.data)
        console.log('SmsHistoryOfPeriod', res);
        return res.data;
    }
}

const getSMSDetailsOfDay = async (role, userID, date) => {
    let res = await axios.post('/sms/getSMSDetailsOfDay', {
        role, date: date._value, userID
    });

    if (res.status == 200) {
        console.log('smsDetailsOfDay', res.data);
        return res.data;
    }
}

const getSMSDetailsOfPeriod = async (role, userID, startDate, endDate) => {
    let res = await axios.post('/sms/getSMSDetailsOfPeriod', {
        role, startDate, endDate, userID
    })

    if (res.status == 200) {
        console.log('smsDetailsOfPeriod', res.data);
        return res.data;
    }
}

const searchSMSOfDay = async (role, userName, date) => {
    let res = await axios.post('/sms/searchSMSOfDay', {
        role, userName, date
    });

    if (res.status == 200) return res.data;
}

const searchSMSOfPeriod = async (role, userName, startDate, endDate) => {
    let res = await axios.post('/sms/searchSMSOfPeriod', {
        role, userName, startDate, endDate
    });

    if (res.status == 200) return res.data;
}

// company branches
const getBranches = async (companyID) => {
    let res = await axios.post('/company/branches', {
        companyID
    });

    if (res.status == 200)
        return res.data;
}

// branch employees
const getBranchEmployees = async (id) => {
    let res = await axios.post('/branch/employees', {
        id
    });

    if (res.status == 200)
        return res.data;
}

const callControl = () => {
    console.log('callControl');
    axios.get('/call/callControl');
}

export {
    getCompanyDatas,
    getAddressData,
    getAreaDatas,
    getEmployeerData,
    getEmployeerDatas,
    getCompanySettingDatas,
    getCompanyEmployeerDatas,
    saveCompanyLPData,
    getCompanyFromBranchData,
    getLPBranchDatas,
    saveEmployeeLPData,
    getUnitSetting,
    updateUnitSetting,
    getEmployyFromBranch,
    getBranchData,
    updateBranchData,
    updateEmployeerData,
    removeBranchData,
    removeEmployeerData,
    getImageFiles,
    getUpdatingBranchLP,
    getUpdatingEmployeeLP,

    getCallHistoriesOfDay,
    getCallHistoriesOfPeriod,
    getCallDetailsOfDay,
    getCallDetailOfPeriod,
    getSearchCallOfDay,
    getSearchCallOfPeriod,

    getSmsHistoriesOfDay,
    getSmsHistoriesOfPeriod,
    getSMSDetailsOfDay,
    getSMSDetailsOfPeriod,
    searchSMSOfDay,
    searchSMSOfPeriod,

    getBranches,
    getBranchEmployees,

    callControl
};
