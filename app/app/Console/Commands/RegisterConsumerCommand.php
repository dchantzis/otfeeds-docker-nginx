<?php

namespace Commands;

use Illuminate\Console\Command;
use App\Models\Consumer;
use App\Repositories\Contracts\DwellingRepositoryInterface;
use App\Repositories\Contracts\ConsumerRepositoryInterface;
use Illuminate\Support\Facades\Log;

class RegisterConsumerCommand extends Command
{

    const CONSUMER_TYPE_INTERNAL = '3ev Internal Consumer';
    const CONSUMER_TYPE_EXTERNAL = '3rd-Party External Consumer';

    /**
     * {@inheritdoc}
     */
    protected $name = 'ot:consumers:register';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Register a new consumer and generate an access key.';

    /**
     * Dwelling repository.
     *
     * @var DwellingRepositoryInterface
     */
    protected $dwellingRepo;

    /**
     * Consumer repository.
     *
     * @var ConsumerRepositoryInterface
     */
    protected $consumerRepo;

    /**
     * RegisterConsumerCommand constructor.
     * @param DwellingRepositoryInterface $dwellingRepo
     * @param ConsumerRepositoryInterface $consumerRepo
     */
    public function __construct(DwellingRepositoryInterface $dwellingRepo, ConsumerRepositoryInterface $consumerRepo)
    {
        $this->dwellingRepo = $dwellingRepo;
        $this->consumerRepo = $consumerRepo;

        parent::__construct();
    }

    public function handle()
    {

        $consumerType = $this->choice('Select API consumer type',
            [
                self::CONSUMER_TYPE_EXTERNAL,
                self::CONSUMER_TYPE_INTERNAL
            ], self::CONSUMER_TYPE_EXTERNAL
        );

        $this->line(
            sprintf('Consumer type: <comment>%s</comment> selected.', $consumerType)
        );
        $this->line("\nTechnical contact details:");
        $contactName = $this->ask('Name');
        $contactEmail = $this->ask('Email');
        $contactPhone = $this->ask('Phone');

        if ($consumerType === self::CONSUMER_TYPE_EXTERNAL) {
            $companyName = $this->ask('Company name');
            $this->line("\nURL tracking");
            $source = $this->ask('Source', '-');
            $medium = $this->ask('Medium', '-');
            $campaign = $this->ask('Campaign', '-');

        } else {
            $companyName = '3ev';
            $this->comment("\nURL tracking utm_source, utm_medium and utm_ampaign attributes are set to <info>'-'</info> for 3ev Internal Consumers.");
            $source = '-';
            $medium = '-';
            $campaign = '-';
        }

        $consumer = new Consumer(array(
            'company_name' => $companyName,
            'contact_name' => $contactName,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'utm_source' => $source,
            'utm_medium' => $medium,
            'utm_campaign' => $campaign,
        ));

        $this->line('');

        if (! $consumer->save()) {
            $this->error('The consumer was not saved to the database.');
        }

        $excluded = $this->dwellingRepo->findAllExcludedFromFeeds();

        if ($excluded->count()) {
            foreach ($excluded as $dwelling) {
                $this->consumerRepo->disable($consumer->id, $dwelling->id, 'Dwelling', $consumer->access_key);
            }
        }

        Log::info(sprintf(
            '[Register Consumer Command] New consumer [%s][%s][%s] has been added',
            $consumer->company_name,
            $consumer->id,
            $consumer->contact_name
        ));

        $this->outputDetails($consumer);

        $this->comment("\nExiting.");
    }

    protected function outputDetails(Consumer $consumer)
    {
        $this->line(
            sprintf(
                'The consumer was successfully registered. Their access key is [<info>%s</info>].',
                $consumer->access_key
            )
        );

        $this->info('Consumer successfully registered.');

        $this->info(sprintf(
            '<comment>Company name</comment>: %s', $consumer->company_name
        ));

        $this->info("Technical contact details:");
        $this->info(sprintf(
            '<comment>Contact Name</comment>: %s', $consumer->contact_name
        ));
        $this->info(sprintf(
            '<comment>Contact E-Mail</comment>: %s', $consumer->contact_email
        ));
        $this->info(sprintf(
            '<comment>Contact Phone</comment>: %s', $consumer->contact_phone
        ));

        $this->info("URL tracking:");
        $this->info(sprintf(
            '<comment>utm_source</comment>: %s', $consumer->utm_source
        ));
        $this->info(sprintf(
            '<comment>Contact Phone</comment>: %s', $consumer->utm_medium
        ));
        $this->info(sprintf(
            '<comment>Contact Phone</comment>: %s', $consumer->utm_campaign
        ));
        $this->info(sprintf(
            '<comment>API Access Key</comment>: %s', $consumer->access_key
        ));
    }

}
