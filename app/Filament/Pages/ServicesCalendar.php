<?php

namespace App\Filament\Pages;

use App\Models\Service;
use Filament\Pages\Page;

class ServicesCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static string $view = 'filament.pages.services-calendar';
    
    protected static ?string $navigationLabel = 'Calendario de Servicios';
    
    protected static ?string $title = 'Calendario de Servicios';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationGroup = 'Operaciones';
    
    public function getServices()
    {
        $services = Service::with(['generators', 'user', 'customer'])
            ->get()
            ->map(function ($service) {
                $generatorsInfo = $service->generators->map(function ($generator) {
                    return $generator->codigo;
                })->join(', ');
                
                return [
                    'id' => $service->id,
                    'title' => $service->nombre,
                    'start' => $service->date_start,
                    'end' => $service->date_final,
                    'url' => route('filament.admin.resources.services.edit', $service),
                    'extendedProps' => [
                        'cliente' => $service->customer->nombre,
                        'operador' => $service->user->name,
                        'lugar' => $service->lugar,
                        'estado' => $service->estado,
                        'generadores' => $generatorsInfo,
                    ],
                    'backgroundColor' => $this->getStatusColor($service->estado),
                    'borderColor' => $this->getStatusColor($service->estado),
                ];
            });
            
        return $services;
    }
    
    private function getStatusColor($status)
    {
        return match($status) {
            'Pendiente' => '#FFA500',    // Naranja
            'En proceso' => '#3498DB',   // Azul
            'Completado' => '#2ECC71',   // Verde
            'Cancelado' => '#E74C3C',    // Rojo
            default => '#95A5A6',        // Gris
        };
    }
}