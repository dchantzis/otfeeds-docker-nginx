<?php

namespace App\Repositories\Contracts;

use App\Models\Dwelling;

interface DwellingRepositoryInterface extends RepositoryInterface
{

    /**
     * Retrieve a full dwelling from the repository.
     *
     * @param  mixed $id Primary key of entity to retrieve
     * @return Dwelling|bool Found entity, or false if not found
     */
    public function find($id);

}
