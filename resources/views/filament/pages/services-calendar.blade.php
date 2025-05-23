<div> <!-- Root element for Livewire -->
    <x-filament::page>
        <div class="fc-services-calendar" id="calendar" style="height: 700px;"></div>
        
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.10/index.global.min.js"></script>
        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const calendarEl = document.getElementById('calendar');
                
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    firstDay: 1,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    locale: 'es',
                    events: @json($this->getServices()),
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false,
                        hour12: false
                    },
                    eventDidMount: function(info) {
                        // Agregar tooltip con información adicional
                        const props = info.event.extendedProps;
                        
                        tippy(info.el, {
                            content: `
                                <div class="p-2">
                                    <p><strong>Cliente:</strong> ${props.cliente}</p>
                                    <p><strong>Operador:</strong> ${props.operador}</p>
                                    <p><strong>Lugar:</strong> ${props.lugar}</p>
                                    <p><strong>Estado:</strong> ${props.estado}</p>
                                    <p><strong>Generadores:</strong> ${props.generadores}</p>
                                </div>
                            `,
                            allowHTML: true,
                            theme: 'light-border',
                        });
                    }
                });
                
                calendar.render();
            });
        </script>
        
        <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css" />
    </x-filament::page>
</div>