<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Mail\ServicioCreado;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        foreach ($data['generators_data'] as $generatorData) {
            $this->record->generators()->attach(
                $generatorData['generators'], 
                [
                    'horometro_inicio' => $generatorData['horometro_inicio'],
                    'horometro_fin' => $generatorData['horometro_fin'],
                    'horas_trabajadas' => $generatorData['horas_trabajadas'],
                ]
            );
        }

        // // Enviar notificación a la base de datos
        // Notification::make()
        //     ->title('Servicio creado')
        //     ->body("El servicio {$this->record->nombre} ha sido creado exitosamente.")
        //     ->success()
        //     ->actions([
        //         Action::make('ver_servicio')
        //             ->button()
        //             ->url("/admin/services/{$this->record->id}")
        //     ])
        //     ->sendToDatabase(Auth::user());

        // Enviar correo electrónico al usuario asignado
        if (isset($this->record->user_id) && $this->record->user_id) {
            $usuario = $this->record->user;
            if ($usuario && $usuario->email) {
                Mail::to($usuario->email)
                    ->send(new ServicioCreado($this->record));
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}