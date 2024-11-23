export class DepartmentItem {
    constructor(
        id,
        department_name,
        company_branch_id,
        children
    ) {
        this.id = id;
        this.department_name = department_name;
        this.company_branch_id = company_branch_id;
        this.children = children;
    }
}

