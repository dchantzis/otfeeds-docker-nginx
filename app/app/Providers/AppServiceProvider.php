<?php

namespace App\Providers;

use App\Availability\AvailabilityPeriod;
use App\Models\DwellingSummary;
use App\Repositories\Caching\DwellingAvailabilityRepository;
use App\Repositories\ConsumerRepository;
use App\Repositories\Contracts\ConsumerRepositoryInterface;
use App\Repositories\Contracts\DwellingAvailabilityRepositoryInterface;
use App\Repositories\Contracts\DwellingRepositoryInterface;
use App\Repositories\Contracts\DwellingSummaryRepositoryInterface;
use App\Repositories\Contracts\ExchangeRateRepositoryInterface;
use App\Repositories\Caching\DwellingRepository;
use App\Repositories\ExchangeRateRepository;
use App\Serialization\Serializers\DwellingAvailabilityXmlSerializer;
use App\Serialization\Serializers\DwellingSummaryXmlSerializer;
use App\Serialization\Serializers\DwellingXmlSerializer;
use App\Serialization\Serializers\Factories\SerializerFactory;
use App\Serialization\Serializers\JsonSerializer;
use App\Serialization\Transformers\DwellingAvailabilityTransformer;
use App\Serialization\Transformers\DwellingSummaryTransformer;
use App\Serialization\Transformers\DwellingTransformer;
use App\Serialization\Transformers\Factories\TransformerFactory;
use App\Services\CurrencyConverter\ExchangeRateLoader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Caching\DwellingSummaryRepository;
use App\Repositories\DwellingSummaryRepository as BaseDwellingSummaryRepository;
use App\Repositories\DwellingRepository as BaseDwellingRepository;
use App\Models\Dwelling;
use App\Repositories\DwellingAvailabilityRepository as BaseDwellingAvailabilityRepository;
use League\Fractal\Manager;
use League\Fractal\Serializer\DataArraySerializer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configureRepositories();

        $this->configureSerialization();

        $this->configureExchangeRateCalculation();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    public function configureRepositories()
    {

        $this->app->bind(
          ExchangeRateRepositoryInterface::class,
          ExchangeRateRepository::class
        );

        $this->app->bind(
            DwellingSummaryRepositoryInterface::class,
            function ($app) {
                return new DwellingSummaryRepository(new BaseDwellingSummaryRepository(), config('app.cache.ttl'));
            }
        );

        $this->app->bind(
            DwellingRepositoryInterface::class,
            function () {
                return new DwellingRepository(new BaseDwellingRepository(new Dwelling()), config('app.cache.ttl'));
            }
        );

        $this->app->bind(
            ConsumerRepositoryInterface::class,
            ConsumerRepository::class
        );

        $this->app->bind(
          DwellingAvailabilityRepositoryInterface::class,
          function () {
              return new DwellingAvailabilityRepository(new BaseDwellingAvailabilityRepository(), config('app.cache.ttl'));
          }
        );

    }

    /**
     * Configure the supporting classes that deal with data serialization.
     */
    public function configureSerialization()
    {

        $this->app->singleton(DwellingTransformer::class, function ($app) {
            return new DwellingTransformer(
                new \App\Services\RateCalculator(),
                $app->make(ExchangeRateLoader::class),
                $app->make(Auth::class)
            );
        });

        $this->app->bind(TransformerFactory::class, function ($app) {
            $factory = new TransformerFactory();
            $factory
                ->registerTransformer(
                    DwellingSummary::class,
                    new DwellingSummaryTransformer()
                )
                ->registerTransformer(
                    Dwelling::class,
                    $app->make(DwellingTransformer::class)
                )
                ->registerTransformer(
                    AvailabilityPeriod::class,
                    new DwellingAvailabilityTransformer()
                );

            return $factory;
        });

        $this->app->bind(SerializerFactory::class, function ($app) {
            $factory = new SerializerFactory();

            $factory
                ->registerFallbackSerializer(
                    'json',
//                    new JsonSerializer(new Manager(), new JsonApiSerializer())
                    new JsonSerializer(new Manager(), new DataArraySerializer())
                )
                ->registerSerializer(
                    'xml',
                    DwellingSummary::class,
                    new DwellingSummaryXmlSerializer()
                )
                ->registerSerializer(
                    'xml',
                    Dwelling::class,
                    new DwellingXmlSerializer(
                        $app->make(DwellingTransformer::class)
                    )
                )
                ->registerSerializer(
                    'xml',
                    AvailabilityPeriod::class,
                    new DwellingAvailabilityXmlSerializer()
                );

            return $factory;
        });
    }

    private function configureExchangeRateCalculation()
    {

        $this->app->singleton(
            ExchangeRateLoader::class, function ($app) {
            return new ExchangeRateLoader(
                $app->make(ExchangeRateRepositoryInterface::class)
            );
        });

    }

}
