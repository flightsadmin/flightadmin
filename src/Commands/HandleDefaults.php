<?php

namespace Flightsadmin\Flightadmin\Commands;

use Illuminate\Support\Str;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\Artisan;

trait HandleDefaults
{
    public function defaultSetting()
    {
        if ($this->confirm('Do you want to scaffold Authentication files? Only skip if you have authentication system on your App', true, true)) {
            Artisan::call('ui:auth', ['bootstrap', '--force' => true], $this->getOutput());
        }

        $this->line('');
        $deleteFiles = [
            'resources/sass',
            'resources/css',
            'resources/js',
            'public/css',
            'public/js',
            'public/build',
            'public/fonts',
            'postcss.config.js',
            'tailwind.config.js',
            'webpack.mix.js',
        ];

        foreach ($deleteFiles as $deleteFile) {
            if ($this->filesystem->exists($deleteFile)) {
                $this->filesystem->delete($deleteFile);
                $this->filesystem->deleteDirectory($deleteFile);
                $this->warn('Deleted file: <info>' . $deleteFile . '</info>');
            }
        }

        $this->crudStubDir = __DIR__ . '/../../resources/install';
        $this->generateCrudFiles();
    }

    public function flightInstall()
    {
        //Spatie Laravel Permission Installation
        if ($this->confirm('Do you want to Install Flights App?', true, true)) {

            //Updating Routes
            $routeFile = base_path('routes/web.php');
            $updatedData = $this->filesystem->get($routeFile);
            $spatieRoutes =
                <<<ROUTES
            // Flights Routes
            Route::middleware(['auth', 'role:super-admin|admin|user'])
                ->prefix(config("flightadmin.adminRoute", "admin"))->group(function () {
                Route::get('/database', function () {
                    Artisan::call('migrate:fresh');
                    Artisan::call('db:seed');
                    return redirect()->back()->with('success', 'Database migrated and seeded successfully!');
                })->name('migrate');

                // Public routes
                Route::get('airlines', App\Livewire\Airline\Index::class)->name('airlines.index');
                Route::get('airlines/{airline}', App\Livewire\Airline\Show::class)->name('airlines.show');
                Route::get('aircraft_types', App\Livewire\AircraftType\Manager::class)->name('aircraft_types.index');
                Route::get('aircraft_types/{aircraft_type}', App\Livewire\AircraftType\Show::class)->name('aircraft_types.show');
                Route::get('flights', App\Livewire\Flight\FlightManager::class)->name('flights.index');
                Route::get('flights/{flight}', App\Livewire\Flight\Show::class)->name('flights.show');
                Route::get('flights/{flight}/containers', App\Livewire\Container\Manager::class)->name('flights.containers');
                Route::get('containers', App\Livewire\Container\Manager::class)->name('containers.index');
                Route::get('crews', App\Livewire\Crew\Manager::class)->name('crews.index');
                Route::get('/schedules', App\Livewire\Flight\Schedules::class)->name('admin.schedules');
            });
            
            ROUTES;

            $fileHook = "//Route Hooks - Do not delete//";

            if (!Str::contains($updatedData, trim($spatieRoutes))) {
                $UserModelContents = str_replace($fileHook, $fileHook . PHP_EOL . $spatieRoutes, $updatedData);
                $this->filesystem->put($routeFile, $UserModelContents);
                $this->warn($routeFile . ' Updated');
            }
        }
    }


    public function correctLayoutExtention($directory, $searchExtends, $replaceExtends)
    {
        $dir = new RecursiveDirectoryIterator($directory);
        $iterator = new RecursiveIteratorIterator($dir);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                $content = file_get_contents($filePath);

                $newContent = str_replace($searchExtends, $replaceExtends, $content);

                if ($newContent !== $content) {
                    file_put_contents($filePath, $newContent);
                    $this->line("Replaced $searchExtends in: $filePath with $replaceExtends");
                }
            }
        }
    }

    public function generateCrudFiles()
    {
        $files = $this->filesystem->allFiles($this->crudStubDir, true);
        foreach ($files as $file) {
            $filePath = $this->replace(Str::replaceLast('.stub', '', $file->getRelativePathname()));
            $fileDir = $this->replace($file->getRelativePath());

            if ($fileDir) {
                $this->filesystem->ensureDirectoryExists($fileDir);
            }
            $this->filesystem->put($filePath, $this->replace($file->getContents()));
            $this->warn('Generated file: <info>' . $filePath . '</info>');
        }
    }
}