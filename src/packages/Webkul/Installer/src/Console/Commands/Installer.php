<?php

namespace Webkul\Installer\Console\Commands;

use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Webkul\Installer\Database\Seeders\DatabaseSeeder as BagistoDatabaseSeeder;
use Webkul\Installer\Events\ComposerEvents;
use Webkul\Installer\Helpers\DatabaseManager;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

class Installer extends Command
{
    /**
     * Contain locales anb currencies details.
     *
     * @var string
     */
    protected $applicationDetails;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bagisto:install
        { --skip-env-check : Skip env check. }
        { --skip-admin-creation : Skip admin creation. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bagisto installer.';

    /**
     * Locales list.
     *
     * @var array
     */
    protected $locales = [
        'ar'    => 'Arabic',
        'bn'    => 'Bengali',
        'de'    => 'German',
        'en'    => 'English',
        'es'    => 'Spanish',
        'fa'    => 'Persian',
        'fr'    => 'French',
        'he'    => 'Hebrew',
        'hi_IN' => 'Hindi',
        'it'    => 'Italian',
        'ja'    => 'Japanese',
        'nl'    => 'Dutch',
        'pl'    => 'Polish',
        'pt_BR' => 'Brazilian Portuguese',
        'ru'    => 'Russian',
        'sin'   => 'Sinhala',
        'tr'    => 'Turkish',
        'uk'    => 'Ukrainian',
        'zh_CN' => 'Chinese',
    ];

    /**
     * Currencies list.
     *
     * @var array
     */
    protected $currencies = [
        'AED' => 'United Arab Emirates Dirham',
        'ARS' => 'Argentine Peso',
        'AUD' => 'Australian Dollar',
        'BDT' => 'Bangladeshi Taka',
        'BHD' => 'Bahraini Dinar',
        'BRL' => 'Brazilian Real',
        'CAD' => 'Canadian Dollar',
        'CHF' => 'Swiss Franc',
        'CLP' => 'Chilean Peso',
        'CNY' => 'Chinese Yuan',
        'COP' => 'Colombian Peso',
        'CZK' => 'Czech Koruna',
        'DKK' => 'Danish Krone',
        'DZD' => 'Algerian Dinar',
        'EGP' => 'Egyptian Pound',
        'EUR' => 'Euro',
        'FJD' => 'Fijian Dollar',
        'GBP' => 'British Pound Sterling',
        'HKD' => 'Hong Kong Dollar',
        'HUF' => 'Hungarian Forint',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'Israeli New Shekel',
        'INR' => 'Indian Rupee',
        'JOD' => 'Jordanian Dinar',
        'JPY' => 'Japanese Yen',
        'KRW' => 'South Korean Won',
        'KWD' => 'Kuwaiti Dinar',
        'KZT' => 'Kazakhstani Tenge',
        'LBP' => 'Lebanese Pound',
        'LKR' => 'Sri Lankan Rupee',
        'LYD' => 'Libyan Dinar',
        'MAD' => 'Moroccan Dirham',
        'MUR' => 'Mauritian Rupee',
        'MXN' => 'Mexican Peso',
        'MYR' => 'Malaysian Ringgit',
        'NGN' => 'Nigerian Naira',
        'NOK' => 'Norwegian Krone',
        'NPR' => 'Nepalese Rupee',
        'NZD' => 'New Zealand Dollar',
        'OMR' => 'Omani Rial',
        'PAB' => 'Panamanian Balboa',
        'PEN' => 'Peruvian Nuevo Sol',
        'PHP' => 'Philippine Peso',
        'PKR' => 'Pakistani Rupee',
        'PLN' => 'Polish Zloty',
        'PYG' => 'Paraguayan Guarani',
        'QAR' => 'Qatari Rial',
        'RON' => 'Romanian Leu',
        'RUB' => 'Russian Ruble',
        'SAR' => 'Saudi Riyal',
        'SEK' => 'Swedish Krona',
        'SGD' => 'Singapore Dollar',
        'THB' => 'Thai Baht',
        'TND' => 'Tunisian Dinar',
        'TRY' => 'Turkish Lira',
        'TWD' => 'New Taiwan Dollar',
        'UAH' => 'Ukrainian Hryvnia',
        'USD' => 'United States Dollar',
        'UZS' => 'Uzbekistani Som',
        'VEF' => 'Venezuelan Bolívar',
        'VND' => 'Vietnamese Dong',
        'XAF' => 'CFA Franc BEAC',
        'XOF' => 'CFA Franc BCEAO',
        'ZAR' => 'South African Rand',
        'ZMW' => 'Zambian Kwacha',
    ];

    /**
     * Install and configure bagisto.
     */
    public function handle()
    {
        $applicationDetails = ! $this->option('skip-env-check')
            ? $this->checkForEnvFile()
            : [];

        $this->loadEnvConfigAtRuntime();

        $this->warn('Step: Generating key...');
        $this->call('key:generate');

        $this->warn('Step: Migrating all tables...');
        $this->call('migrate:fresh');

        $this->warn('Step: Seeding basic data for Bagisto kickstart...');
        $this->info(app(BagistoDatabaseSeeder::class)->run([
            'default_locale'     => $applicationDetails['default_locale'] ?? 'en',
            'allowed_locales'    => $applicationDetails['allowed_locales'] ?? ['en'],
            'default_currency'   => $applicationDetails['default_currency'] ?? 'USD',
            'allowed_currencies' => $applicationDetails['allowed_currencies'] ?? ['USD'],
        ]));

        $this->warn('Step: Linking storage directory...');
        $this->call('storage:link');

        $this->warn('Step: Clearing cached bootstrap files...');
        $this->call('optimize:clear');

        if (! $this->option('skip-admin-creation')) {
            $this->warn('Step: Create admin credentials...');
            $this->createAdminCredentials();
        }

        ComposerEvents::postCreateProject();
    }

    /**
     *  Checking .env file and if not found then create .env file.
     *
     * @return ?array
     */
    protected function checkForEnvFile()
    {
        if (! file_exists(base_path('.env'))) {
            $this->info('Creating the environment configuration file.');

            File::copy('.env.example', '.env');
        } else {
            $this->info('Great! your environment configuration file already exists.');
        }

        return $this->createEnvFile();
    }

    /**
     * Create a new .env file. Afterwards, request environment configuration details and set them
     * in the .env file to facilitate the migration to our database.
     *
     * @return ?array
     */
    protected function createEnvFile()
    {
        try {
            $applicationDetails = $this->askForApplicationDetails();

            $this->askForDatabaseDetails();

            return $applicationDetails;
        } catch (\Exception $e) {
            $this->error('Error in creating .env file, please create it manually and then run `php artisan migrate` again.');
        }
    }

    /**
     * Ask for application details.
     *
     * @return array
     */
    protected function askForApplicationDetails()
    {
        $this->updateEnvVariable(
            'APP_NAME',
            'Please enter the application name',
            env('APP_NAME', 'Bagisto')
        );

        $this->updateEnvVariable(
            'APP_URL',
            'Please enter the application URL',
            env('APP_URL', 'http://localhost:8000')
        );

        $timezones = $this->getTimezones();

        $this->updateEnvChoice(
            'APP_TIMEZONE',
            'Please select the application timezone',
            $timezones,
            true
        );

        $defaultLocale = $this->updateEnvChoice(
            'APP_LOCALE',
            'Please select the default application locale',
            $this->locales
        );

        $defaultCurrency = $this->updateEnvChoice(
            'APP_CURRENCY',
            'Please select the default currency',
            $this->currencies
        );

        $allowedLocales = $this->allowedChoice(
            'Please choose the allowed locales for your channels',
            array_merge(['all' => 'All'], $this->locales)
        );

        $allowedCurrencies = $this->allowedChoice(
            'Please choose the allowed currencies for your channels',
            array_merge(['all' => 'All'], $this->currencies)
        );

        $allowedLocales = array_key_exists('all', $allowedLocales)
                            ? array_values(array_unique(array_merge(
                                [$defaultLocale],
                                array_diff(array_keys($this->locales), [$defaultLocale])
                            )))
                            : array_values(array_unique(array_merge(
                                [$defaultLocale],
                                array_diff(array_keys($allowedLocales), [$defaultLocale])
                            )));

        $allowedCurrencies = array_key_exists('all', $allowedCurrencies)
                            ? array_values(array_unique(array_merge(
                                [$defaultCurrency],
                                array_diff(array_keys($this->currencies), [$defaultCurrency])
                            )))
                            : array_values(array_unique(array_merge(
                                [$defaultCurrency],
                                array_diff(array_keys($allowedCurrencies), [$defaultCurrency])
                            )));

        return $this->applicationDetails = [
            'default_locale'     => $defaultLocale,
            'allowed_locales'    => $allowedLocales,
            'default_currency'   => $defaultCurrency,
            'allowed_currencies' => $allowedCurrencies,
        ];
    }

    /**
     * Add the database credentials to the .env file.
     *
     * @return mixed
     */
    protected function askForDatabaseDetails()
    {
        $databaseDetails = [
            'DB_CONNECTION' => select(
                label   : 'Please select the database connection',
                options : ['mysql'],
                default : 'mysql',
            ),

            'DB_HOST' => text(
                label    : 'Please enter the database host',
                default  : env('DB_HOST', '127.0.0.1'),
                required : true
            ),

            'DB_PORT' => text(
                label    : 'Please enter the database port',
                default  : env('DB_PORT', '3306'),
                required : true
            ),

            'DB_DATABASE' => text(
                label    : 'Please enter the database name',
                default  : env('DB_DATABASE', ''),
                required : true
            ),

            'DB_PREFIX' => text(
                label   : 'Please enter the database prefix',
                default : env('DB_PREFIX', ''),
                hint    : 'or press enter to continue'
            ),

            'DB_USERNAME' => text(
                label    : 'Please enter your database username',
                default  : env('DB_USERNAME', ''),
                required : true
            ),

            'DB_PASSWORD' => password(
                label    : 'Please enter your database password',
                required : false
            ),
        ];

        if (
            ! $databaseDetails['DB_DATABASE']
            || ! $databaseDetails['DB_USERNAME']
        ) {
            return $this->error('Please enter the database credentials.');
        }

        foreach ($databaseDetails as $key => $value) {
            if ($value) {
                $this->envUpdate($key, $value, true);
            }
        }
    }

    /**
     * Create a admin credentials.
     *
     * @return mixed
     */
    protected function createAdminCredentials()
    {
        $adminName = text(
            label    : 'Enter the name of the admin user',
            default  : 'Example',
            required : true
        );

        $adminEmail = text(
            label    : 'Enter the email address of the admin user',
            default  : 'admin@admin.com',
            validate : fn (string $value) => match (true) {
                ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'The email address you entered is not valid please try again.',
                default                                     => null
            }
        );

        $adminPassword = text(
            label    : 'Configure the password for the admin user',
            default  : 'password',
            required : true
        );

        $sampleProduct = select(
            label   : 'Please select if you want some sample products after installation.',
            options : ['true', 'false'],
            default : 'false',
            hint    : 'The action will create products after installation.',
        );

        $password = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 10]);

        try {
            DB::table('admins')->updateOrInsert(
                ['id' => 1],
                [
                    'name'     => $adminName,
                    'email'    => $adminEmail,
                    'password' => $password,
                    'role_id'  => 1,
                    'status'   => 1,
                ]
            );

            if ($sampleProduct === 'true') {
                $this->warn('Step: Seeding sample product data. Please Wait...');

                app(DatabaseManager::class)->seedSampleProducts($this->applicationDetails);

                $this->info('Product Creation Completed...');
            }

            $filePath = storage_path('installed');

            File::put($filePath, 'Bagisto is successfully installed');

            $this->info('-----------------------------');
            $this->info('Congratulations!');
            $this->info('The installation has been finished and you can now use Bagisto.');
            $this->info('Go to '.env('APP_URL').'/admin'.' and authenticate with:');
            $this->info('Email: '.$adminEmail);
            $this->info('Password: '.$adminPassword);
            $this->info('Cheers!');

            Event::dispatch('bagisto.installed');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Loaded Env variables for config files.
     */
    protected function loadEnvConfigAtRuntime(): void
    {
        $this->warn('Loading configs...');

        /**
         * Setting application environment.
         */
        app()['env'] = $this->getEnvAtRuntime('APP_ENV');

        /**
         * Setting application configuration.
         */
        config([
            'app.env'      => $this->getEnvAtRuntime('APP_ENV'),
            'app.name'     => $this->getEnvAtRuntime('APP_NAME'),
            'app.url'      => $this->getEnvAtRuntime('APP_URL'),
            'app.timezone' => $this->getEnvAtRuntime('APP_TIMEZONE'),
            'app.locale'   => $this->getEnvAtRuntime('APP_LOCALE'),
            'app.currency' => $this->getEnvAtRuntime('APP_CURRENCY'),
        ]);

        /**
         * Setting database configurations.
         */
        $databaseConnection = $this->getEnvAtRuntime('DB_CONNECTION');

        config([
            "database.connections.{$databaseConnection}.host"     => $this->getEnvAtRuntime('DB_HOST'),
            "database.connections.{$databaseConnection}.port"     => $this->getEnvAtRuntime('DB_PORT'),
            "database.connections.{$databaseConnection}.database" => $this->getEnvAtRuntime('DB_DATABASE'),
            "database.connections.{$databaseConnection}.username" => $this->getEnvAtRuntime('DB_USERNAME'),
            "database.connections.{$databaseConnection}.password" => $this->getEnvAtRuntime('DB_PASSWORD'),
            "database.connections.{$databaseConnection}.prefix"   => $this->getEnvAtRuntime('DB_PREFIX'),
        ]);

        DB::purge($databaseConnection);

        $this->info('Configuration loaded...');
    }

    /**
     * Method for asking the details of .env files
     */
    protected function updateEnvVariable(string $key, string $question, string $defaultValue): void
    {
        $input = text(
            label    : $question,
            default  : $defaultValue,
            required : true
        );

        $this->envUpdate($key, $input ?: $defaultValue);
    }

    /**
     * Method for asking choice based on the list of options.
     *
     * @return string
     */
    protected function updateEnvChoice(string $key, string $question, array $choices, bool $useSuggest = false)
    {
        if ($useSuggest) {
            $choice = suggest(
                label: $question,
                options: $choices,
                default: env($key)
            );
        } else {
            $choice = select(
                label: $question,
                options: $choices,
                default: env($key)
            );
        }

        $this->envUpdate($key, $choice);

        return $choice;
    }

    /**
     * Function for getting allowed choices based on the list of options.
     */
    protected function allowedChoice(string $question, array $choices)
    {
        $selectedValues = multiselect(
            label: $question,
            options: array_values($choices),
        );

        $selectedChoices = [];

        foreach ($selectedValues as $selectedValue) {
            foreach ($choices as $key => $value) {
                if ($selectedValue === $value) {
                    $selectedChoices[$key] = $value;
                    break;
                }
            }
        }

        return $selectedChoices;
    }

    /**
     * Update the .env values.
     */
    protected function envUpdate(string $key, string $value, bool $addQuotes = false): void
    {
        $data = file_get_contents(base_path('.env'));

        // Check if $value contains spaces, and if so, add double quotes or if $addQuotes is true
        if ($addQuotes || preg_match('/\s/', $value)) {
            $value = '"'.$value.'"';
        }

        $data = preg_replace("/$key=(.*)/", "$key=$value", $data);

        file_put_contents(base_path('.env'), $data);
    }

    /**
     * Check key in `.env` file because it will help to find values at runtime.
     */
    protected static function getEnvAtRuntime(string $key): string|bool
    {
        if ($data = file(base_path('.env'))) {
            foreach ($data as $line) {
                $line = preg_replace('/\s+/', '', $line);

                $rowValues = explode('=', $line);

                if (strlen($line) !== 0) {
                    if (strpos($key, $rowValues[0]) !== false) {
                        return $rowValues[1];
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get sorted list of timezone abbreviations.
     *
     * @return array
     */
    private function getTimezones()
    {
        $timezoneAbbreviations = DateTimeZone::listAbbreviations();
        $timezones = [];

        foreach ($timezoneAbbreviations as $zones) {
            foreach ($zones as $zone) {
                if (! empty($zone['timezone_id'])) {
                    $timezones[$zone['timezone_id']] = $zone['timezone_id'];
                }
            }
        }

        asort($timezones);

        return $timezones;
    }
}
