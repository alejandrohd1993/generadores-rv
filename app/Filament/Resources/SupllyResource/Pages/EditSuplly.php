<?php

namespace App\Filament\Resources\SupllyResource\Pages;

use App\Filament\Resources\SupllyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuplly extends EditRecord
{
    protected static string $resource = SupllyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
