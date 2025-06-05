<?php

namespace App\Filament\Resources\SupllyResource\Pages;

use App\Filament\Resources\SupllyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupllies extends ListRecords
{
    protected static string $resource = SupllyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
