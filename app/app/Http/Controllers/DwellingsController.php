<?php


namespace App\Http\Controllers;

use App\Auth\AccessTokenGuard;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Repositories\Contracts\DwellingAvailabilityRepositoryInterface;
use App\Services\SerializedResponseBuilder;
use App\Repositories\Contracts\DwellingSummaryRepositoryInterface;
use App\Repositories\Contracts\DwellingRepositoryInterface;
use League\Period\Period;

class DwellingsController extends Controller
{

    /**
     * @var DwellingSummaryRepositoryInterface
     */
    private $summaries;

    /**
     * @var DwellingRepositoryInterface
     */
    private $dwellings;

    /**
     * @var SerializedResponseBuilder
     */
    private $serializedResponseContentBuilder;

    /**
     * @var DwellingAvailabilityRepositoryInterface
     */
    private $availability;

    public function __construct(
        DwellingSummaryRepositoryInterface $summaries,
        DwellingRepositoryInterface $dwellings,
        DwellingAvailabilityRepositoryInterface $availability,
        SerializedResponseBuilder $serializedResponseContentBuilder
    ) {
        $this->summaries = $summaries;
        $this->dwellings = $dwellings;
        $this->serializedResponseContentBuilder = $serializedResponseContentBuilder;
        $this->availability = $availability;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $dwellings = $this->summaries->listAll($request->header(AccessTokenGuard::AUTH_HEADER));
        $serializedData = $this->serializedResponseContentBuilder->build($dwellings);

        return response()->api($serializedData);
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        $dwelling = $this->dwellings->find($id);
        $serializedData = [];

        if (!$this->dwellings->isExcluded($id, $request->header(AccessTokenGuard::AUTH_HEADER))) {
            $serializedData = $this->serializedResponseContentBuilder->build($dwelling);
        }

        // To maintain the behaviour of the legacy ot-feeds system, we will not send any response if there are no results
        if (!empty($serializedData)) {
            return response()->api($serializedData);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \League\Period\Exception
     */
    public function availability(Request $request, $id)
    {
        $serializedData = [];

        if (!$this->dwellings->isExcluded($id, $request->header(AccessTokenGuard::AUTH_HEADER))) {
            // Number of days to search for availability
            $days = config('app.availability_days');

            // End period that the above covers
            $toPeriod = sprintf('%d days', $days);

            $period = new Period('now', $toPeriod);

            $availability = $this->availability
                ->findForDwelling($id, $days)
                ->fillGapsWithAvailability($period, true)
                ->isolatePeriods($period)
                ->splitByWeeklyBlocksOnly($id);

            $serializedData = $this->serializedResponseContentBuilder->build($availability);
        }

        // To maintain the behaviour of the legacy ot-feeds system, we will not send any response if there are no results
        if (!empty($serializedData)) {
            return response()->api($serializedData);
        }
    }

}
