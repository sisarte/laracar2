<?php

namespace App\Console\Commands;

use App\Models\Make;
use App\Models\ModelVersion;
use App\Models\VehicleModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FipeSyncCommand extends Command
{
    protected $signature = 'fipe:sync
                            {--only-makes : Sincroniza apenas as marcas}
                            {--make= : ID da marca para sincronizar modelos e versões}';

    protected $description = 'Sincroniza dados de veículos da API FIPE';

    private const BASE_URL = 'https://parallelum.com.br/fipe/api/v1/carros';

    private const COMPOUND_MODELS = [
        'Land Rover',
        'Land Cruiser',
        'Grand Cherokee',
        'Grand Vitara',
        'Santa Fe',
        'Rio Grande',
        'New Beetle',
        'New Fiesta',
        'New Civic',
        'Town Country',
        'Gran Siena',
        'Grand Livina',
        'Gran Turismo',
        'Grand Blazer',
        'Punto Evo',
        'Uno Mille',
        'Idea Adventure',
        'Palio Weekend',
        'Fiat Uno',
    ];

    public function handle(): int
    {
        if ($this->option('only-makes')) {
            $this->syncMakes();

            return self::SUCCESS;
        }

        if ($makeId = $this->option('make')) {
            $make = Make::query()->find($makeId);

            if (! $make) {
                $this->error("Marca com ID {$makeId} não encontrada.");

                return self::FAILURE;
            }

            $this->syncModelsForMake($make);

            return self::SUCCESS;
        }

        $this->syncAll();

        return self::SUCCESS;
    }

    private function syncMakes(): void
    {
        $this->info('Buscando marcas da API FIPE...');

        $response = Http::get(self::BASE_URL.'/marcas');

        if (! $response->successful()) {
            $this->error('Falha ao buscar marcas: '.$response->status());

            return;
        }

        $makes = $response->json();
        $bar = $this->output->createProgressBar(count($makes));
        $bar->start();

        foreach ($makes as $makeData) {
            Make::query()->updateOrCreate(
                ['fipe_code' => $makeData['codigo']],
                ['name' => $makeData['nome']]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Marcas sincronizadas: '.count($makes));
    }

    private function syncModelsForMake(Make $make): void
    {
        $this->info("Sincronizando modelos para: {$make->name}");

        $response = Http::get(self::BASE_URL."/marcas/{$make->fipe_code}/modelos");

        if (! $response->successful()) {
            $this->error("Falha ao buscar modelos para {$make->name}: ".$response->status());

            return;
        }

        $data = $response->json();
        $models = $data['modelos'] ?? [];

        $bar = $this->output->createProgressBar(count($models));
        $bar->start();

        foreach ($models as $modelData) {
            $parsed = $this->parseModelName($modelData['nome']);

            $vehicleModel = VehicleModel::query()->firstOrCreate(
                [
                    'make_id' => $make->id,
                    'name' => $parsed['model'],
                ]
            );

            if ($parsed['version']) {
                $this->syncVersionsForModel($make, $vehicleModel, $modelData['codigo'], $parsed['version']);
            }

            $bar->advance();
            usleep(100000);
        }

        $bar->finish();
        $this->newLine();
    }

    private function syncVersionsForModel(Make $make, VehicleModel $vehicleModel, string $fipeModelCode, string $versionName): void
    {
        $response = Http::get(self::BASE_URL."/marcas/{$make->fipe_code}/modelos/{$fipeModelCode}/anos");

        if (! $response->successful()) {
            return;
        }

        $years = $response->json();

        foreach ($years as $yearData) {
            ModelVersion::query()->updateOrCreate(
                [
                    'vehicle_model_id' => $vehicleModel->id,
                    'fipe_code' => $yearData['codigo'],
                ],
                ['name' => $versionName.' '.$yearData['nome']]
            );
        }

        usleep(100000);
    }

    private function syncAll(): void
    {
        $this->syncMakes();

        $makes = Make::all();
        $this->newLine();
        $this->info('Sincronizando modelos e versões para todas as marcas...');

        foreach ($makes as $make) {
            $this->syncModelsForMake($make);
            sleep(1);
        }

        $this->info('Sincronização completa!');
    }

    /**
     * @return array{model: string, version: string}
     */
    private function parseModelName(string $fullName): array
    {
        foreach (self::COMPOUND_MODELS as $compound) {
            if (str_starts_with(strtoupper($fullName), strtoupper($compound))) {
                return [
                    'model' => $compound,
                    'version' => trim(substr($fullName, strlen($compound))),
                ];
            }
        }

        $parts = explode(' ', $fullName, 2);

        return [
            'model' => $parts[0],
            'version' => $parts[1] ?? '',
        ];
    }
}
