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
        ];

        foreach ($deleteFiles as $deleteFile) {
            if ($this->filesystem->exists($deleteFile)) {
                $this->filesystem->delete($deleteFile);
                $this->filesystem->deleteDirectory($deleteFile);
                $this->warn('Deleted file: <info>' . $deleteFile . '</info>');
            }
        }

        $this->crudStubDir = __DIR__ . '/../../resources/install/deafaultsFiles';
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
            Route::middleware(['auth', 'role:super-admin|admin|user'])->prefix(config("admin.adminRoute", "admin"))->group(function () {
                Route::get('/', App\Livewire\Flight\Flights::class)->name(config("admin.adminRoute", "admin"));
                Route::get('/flights', App\Livewire\Flight\Flights::class)->name('admin.flights');
                Route::get('/airlines', App\Livewire\Flight\Airlines::class)->name('admin.airlines');
                Route::get('/delays', App\Livewire\Flight\Delays::class)->name('admin.delays');
                Route::get('/services', App\Livewire\Flight\Services::class)->name('admin.services');
                Route::get('/registrations', App\Livewire\Flight\Registrations::class)->name('admin.registrations');
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