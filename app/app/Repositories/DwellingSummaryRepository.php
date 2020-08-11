<?php


namespace App\Repositories;


use App\Models\Dwelling;
use App\Models\DwellingSummary;
use App\Repositories\Contracts\DwellingSummaryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DwellingSummaryRepository implements DwellingSummaryRepositoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function listAll($accessKey = null)
    {
        $excludedDwellingIdArray = array();
        $excludedOwnerIdArray = array();

        $summaries = \DB::table('dwellings')
            ->join(
                $this->getAdditionalInformationDatabaseName(),
                'dwellings.id',
                '=',
                sprintf('%s.%s', $this->getAdditionalInformationDatabaseName(), 'booking_system_id')
            )
            ->select('id', 'updated_at', 'tstamp')
            ->where('dwellings.sale_state', Dwelling::SALE_STATE_ON_SALE)
            ->where(sprintf('%s.%s', $this->getAdditionalInformationDatabaseName(), 'deleted'), 0)
            ->where(sprintf('%s.%s', $this->getAdditionalInformationDatabaseName(), 'hidden'), 0);

        if ($accessKey) {
            $excludedDwellingIds = \DB::select('SELECT foreign_id FROM `excluded_feeds` WHERE foreign_class="Dwelling" AND access_key= ?', array($accessKey));
            $excludedOwnerIds = \DB::select('SELECT foreign_id FROM `excluded_feeds` WHERE foreign_class="Owner" AND access_key= ?', array($accessKey));

            foreach ($excludedDwellingIds as $excludedDwellingId) {
                $excludedDwellingIdArray[] = $excludedDwellingId->foreign_id;
            }

            foreach ($excludedOwnerIds as $excludedOwnerId) {
                $excludedOwnerIdArray[] = $excludedOwnerId->foreign_id;
            }
        }

        if ($excludedDwellingIdArray) {
            $summaries = $summaries->whereNotIn('dwellings.id', $excludedDwellingIdArray);
        }

        if ($excludedOwnerIdArray) {
            $summaries = $summaries->whereNotIn('dwellings.owner_id', $excludedOwnerIdArray);
        }

        $summaries = $summaries->orderBy('dwellings.id')
            ->get();

        return new \ArrayObject(array_map(function ($row) {
            return new DwellingSummary(
                $row->id,
                new Carbon($row->updated_at),
                Carbon::createFromTimestamp($row->tstamp)
            );
        }, $summaries));
    }

    private function getAdditionalInformationDatabaseName()
    {
        $dwelling = new Dwelling();

        return $dwelling->addDatabasePrefix($dwelling->getTable());
    }

}
