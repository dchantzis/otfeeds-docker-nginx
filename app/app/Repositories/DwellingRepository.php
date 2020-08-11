<?php


namespace App\Repositories;


use App\Models\Dwelling;
use App\Models\Rate;
use App\Repositories\Contracts\DwellingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DwellingRepository extends AbstractRepository implements DwellingRepositoryInterface
{

    /**
     * @var Dwelling
     */
    protected $model;

    public function __construct(Dwelling $model)
    {
        $this->model = $model;

        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $related = array())
    {
        $rate = new Rate();
        $rateTable = $rate->getTable();

        return $this
            ->model
            ->with('Currency')
            ->with(array('Rates' => function ($query) use ($id, $rateTable) {
                // Only include rates that are current
                $query->where($rateTable.'.on_sale_from', '>=', function ($query) use ($id, $rateTable) {
                    $today = Carbon::today()->toDateString();
                    $query
                        ->select('current_rates.on_sale_from')
                        ->from($rateTable.' AS current_rates')
                        ->where('current_rates.on_sale_from', '<=', $today)
                        ->where('current_rates.dwelling_id', $id)
                        ->orderBy('current_rates.on_sale_to', 'ASC')
                        ->orderBy('current_rates.on_sale_from', 'DESC')
                        ->limit(1);
                });
            }))
            ->with('TaxBand')
            ->with('AdditionalInformation')
            ->with('AdditionalInformation.DwellingType')
            ->with(array('AdditionalInformation.Extras' => function ($query) {
                $query->orderBy('sorting');
            }))
            ->with(array('AdditionalInformation.Facilities' => function ($query) {
                $query
                    ->orderBy('sorting');
            }))
            ->saleable()
            ->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBasic($id)
    {
        return $this->model
            ->with('AdditionalInformation')
            ->with('AdditionalInformation.DwellingType')
            ->with(array('AdditionalInformation.Extras' => function ($query) {
                $query->orderBy('sorting');
            }))
            ->with(array('AdditionalInformation.Facilities' => function ($query) {
                $query
                    ->whereIn('category', array(1, 5)) // Property amenities and pool
                    ->orderBy('sorting');
            }))
            ->findOrFail($id);
    }

    public function findBasicWithAdditionalInformation($id)
    {
        return $this->model
            ->with('AdditionalInformation')
            ->with('AdditionalInformation.DwellingType')
            ->with(array('AdditionalInformation.Extras' => function ($query) {
                $query->orderBy('sorting');
            }))
            ->with(array('AdditionalInformation.Facilities' => function ($query) {
                $query
                    ->orderBy('sorting');
            }))
            ->findOrFail($id);
    }

    public function findWithAdditionalInformation($id){
        $rate = new Rate();
        $rateTable = $rate->getTable();

        return $this
            ->model
            ->with('Currency')
            ->with(array('Rates' => function ($query) use ($id, $rateTable) {
                // Only include rates that are current
                $query->where($rateTable.'.on_sale_from', '>=', function ($query) use ($id, $rateTable) {
                    $today = Carbon::today()->toDateString();
                    $query
                        ->select('current_rates.on_sale_from')
                        ->from($rateTable.' AS current_rates')
                        ->where('current_rates.on_sale_from', '<=', $today)
                        ->where('current_rates.dwelling_id', $id)
                        ->orderBy('current_rates.on_sale_to', 'ASC')
                        ->orderBy('current_rates.on_sale_from', 'DESC')
                        ->limit(1);
                });
            }))
            ->with('TaxBand')
            ->with('AdditionalInformation')
            ->with('AdditionalInformation.DwellingType')
            ->with(array('AdditionalInformation.Extras' => function ($query) {
                $query->orderBy('sorting');
            }))
            ->with(array('AdditionalInformation.Facilities' => function ($query) {
                $query
                    ->orderBy('sorting');
            }))
            ->saleable()
            ->findOrFail($id);
    }

    public function findAllExcludedFromFeeds()
    {
        return $this->model->where('exclude_feeds', 1)->get();
    }

    /**
     * See if the given dwelling is excluded from the given feed, either through
     * itself, or its owner.
     */
    public function isExcluded($id, $consumerAuth)
    {
        $dwelling = $this->find($id);

        $excluded = DB::select('SELECT COUNT(*) as excluded FROM `excluded_feeds`'
            . ' WHERE `access_key` = ?'
            . ' AND ((`foreign_id` = ? AND `foreign_class` = "Dwelling")'
            . ' OR (`foreign_id` = ? AND `foreign_class` = "Owner"))', array($consumerAuth, $id, $dwelling->owner_id));

        return $excluded[0]->excluded;
    }

}
