<?php


namespace App\Repositories\Contracts;

use App\Models\DwellingSummary;

interface DwellingSummaryRepositoryInterface
{

    /**
     * Returns a list of dwelling summaries (the ID, and the date and time the model was last modified.).
     *
     * @param $header
     * @return DwellingSummary[]
     */
    public function listAll($header);

}
