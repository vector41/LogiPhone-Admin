export class OfficeItem {
    constructor(
        company_id,
        is_main_office,
        branch_name,
        nickname,
        zip_1,
        zip_2,
        pref,
        city,
        town,
        building,
        tel,
        fax,
        id
    ) {
        this.company_id = company_id,
        this.is_main_office = is_main_office;
        this.branch_name = branch_name;
        this.nickname = nickname;
        this.zip_1 = zip_1;
        this.zip_2 = zip_2;
        this.pref = pref;
        this.city=city,
        this.town=town,
        this.building = building;
        this.tel = tel;
        this.fax = fax;
        this.id=id;
    }
}

