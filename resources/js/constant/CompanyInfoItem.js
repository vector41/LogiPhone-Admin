export class CompanyInfoItem {
    constructor(
        created_id,
        updated_id,
        company_base_id,
        personality,
        personality_position,
        company_name,
        is_company_branch,
        departments,
        zip_1,
        zip_2,
        pref,
        city,
        town,
        building,
        children,
        id
    ) {
        this.created_id = created_id;
        this.updated_id = updated_id;
        this.company_base_id = company_base_id;
        this.personality = personality;
        this.personality_position = personality_position;
        this.company_name = company_name;
        this.is_company_branch = is_company_branch,
        this.departments=departments;
        this.zip_1 = zip_1;
        this.zip_2 = zip_2;
        this.pref = pref;
        this.city=city,
        this.town=town,
        this.building = building;
        this.children = children;
        this.id=id;
    }
}

