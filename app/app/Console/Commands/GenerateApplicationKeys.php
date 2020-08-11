<?php

namespace Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * Class GenerateApplicationKeys
 * @package Commands
 */
class GenerateApplicationKeys extends Command
{

    /**
     * {@inheritdoc}
     */
    protected $signature = 'ot:generate-app-keys';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate application keys.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function handle()
    {

        // Generate the key to store in APP_KEY
        $appKey = Str::random(32);

        $appPrivateKey = base64_encode(
            random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES)
        );

        $appIndexKey = base64_encode(
            random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES)
        );

        $this->info('Copy these values in your .env file');
        $this->line(sprintf(
            '<comment>APP_KEY</comment>: %s', $appKey
        ));
        $this->line(sprintf(
            '<comment>APP_PRIVATE_KEY for encryption</comment>: %s', $appPrivateKey
        ));
        $this->line(sprintf(
           '<comment>APP_INDEX_KEY for blind index key</comment>: %s', $appIndexKey
        ));

        Log::info(sprintf(
            '[Generate Application Keys] New Application Keys for .env [%s][%s][%s] have been created.',
            'APP_KEY', 'APP_PRIVATE_KEY', 'APP_INDEX_KEY'
        ));

    }

}
