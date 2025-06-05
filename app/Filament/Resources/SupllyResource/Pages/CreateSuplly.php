<?php

namespace App\Filament\Resources\SupllyResource\Pages;

use App\Filament\Resources\SupllyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSuplly extends CreateRecord
{
    protected static string $resource = SupllyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
