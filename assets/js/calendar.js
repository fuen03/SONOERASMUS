document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('calendar');

  const cal = new FullCalendar.Calendar(el, {
    initialView: 'dayGridMonth',
    locale: 'it',
    firstDay: 1,            // Lunes
    height: 'auto',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: ''             // Solo vista mensual
    },
    // Carga desde PHP (ajusta la ruta si tu app está en subcarpeta)
    events: {
      url: '/app/events.php',
      failure() { console.error('No se pudieron cargar los eventos'); }
    },
    // Marca la celda del día que tenga evento
    eventDidMount(info) {
      const dayCell = info.el.closest('.fc-daygrid-day');
      if (dayCell) dayCell.classList.add('has-event');
      // Tooltip simple
      info.el.title = info.event.title || '';
    },
    dayMaxEventRows: true
  });

  cal.render();
});
