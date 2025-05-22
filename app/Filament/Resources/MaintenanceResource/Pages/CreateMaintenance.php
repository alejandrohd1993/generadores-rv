<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use App\Mail\MantenimientoCreado;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateMaintenance extends CreateRecord
{
    protected static string $resource = MaintenanceResource::class;

    protected function afterCreate(): void
    {
        // Obtener el usuario asignado al mantenimiento
        $usuario = $this->record->user;

        // Enviar correo electrónico al usuario asignado
        if ($usuario && $usuario->email) {
            Mail::to($usuario->email)
                ->send(new MantenimientoCreado($this->record));
        }
        

        // // Enviar notificación en el sistema
        // Notification::make()
        //     ->title('Mantenimiento creado')
        //     ->body("Se ha creado un nuevo mantenimiento: {$this->record->nombre}")
        //     ->actions([
        //         Action::make('ver')
        //             ->button()
        //             ->url(MaintenanceResource::getUrl('edit', ['record' => $this->record->id]))
        //     ])
        //     ->sendToDatabase(Auth::user());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}