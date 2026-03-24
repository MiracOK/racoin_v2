<?php

namespace controller;

use model\Departement;

class getDepartment {

    protected $departments = [];

    public function getAllDepartments() {
        return Departement::orderBy('nom_departement')->get()->toArray();
    }
}