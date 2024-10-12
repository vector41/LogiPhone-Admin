export class EmployeerItem {
    constructor(
        created_id,
        updated_id,
        company_id,
        company_branch_id,
        company_department_id,
        department,
        company_department_child_id,
        person_name_second,
        person_name_first,
        person_name_second_kana,
        person_name_first_kana,
        position,
        is_representative,
        is_board_member,
        tel1,
        tel2,
        tel3,
        gender,
        roles,
        email,
        note,
        cardImageObjs,
        licenseImageObjs,
        id,
        store_pos,
    ) {
        this.created_id = created_id;
        this.updated_id = updated_id;
        this.company_id = company_id;
        this.company_branch_id = company_branch_id;
        this.company_department_id = company_department_id,
        this.department = department,
        this.company_department_child_id = company_department_child_id;
        this.person_name_second = person_name_second;
        this.person_name_first = person_name_first;
        this.person_name_second_kana = person_name_second_kana;
        this.person_name_first_kana = person_name_first_kana;
        this.position = position;
        this.is_representative = is_representative;
        this.is_board_member = is_board_member;
        this.tel1=tel1,
        this.tel2 = tel2;
        this.tel3 = tel3;
        this.gender = gender;
        this.roles = roles;
        this.email = email;
        this.note = note;
        this.cardImageObjs = cardImageObjs;
        this.licenseImageObjs = licenseImageObjs;
        this.id=id;
        this.store_pos=store_pos;
    }
}

