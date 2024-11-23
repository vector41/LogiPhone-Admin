export class CompanyItem {
    constructor(
        personality,
        personality_position,
        company_name,
        company_name_kana,
        keyword,
        is_company_branch,
        phone_1,
        phone_2,
        phone_3,
        fax_1,
        fax_2,
        fax_3,
        zip_1,
        zip_2,
        pref,
        city,
        town,
        building,
        memo,
        imageObjs,
        id
    ) {
        this.personality = personality;
        this.personality_position = personality_position;
        this.company_name = company_name;
        this.company_name_kana = company_name_kana;
        this.keyword = keyword,
        this.is_company_branch = is_company_branch,
        this.phone_1 = phone_1;
        this.phone_2 = phone_2;
        this.phone_3 = phone_3;
        this.fax_1 = fax_1;
        this.fax_2 = fax_2;
        this.fax_3 = fax_3;
        this.zip_1 = zip_1;
        this.zip_2 = zip_2;
        this.pref = pref;
        this.city=city,
        this.town=town,
        this.building = building;
        this.memo = memo;
        this.imageObjs = imageObjs;
        this.id=id;
    }
}

