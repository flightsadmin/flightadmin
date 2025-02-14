import './bootstrap';

// Import our custom CSS
import "../sass/app.scss";

// Import Sweet Alert
import Swal from "sweetalert2";
window.Swal = Swal;

// Import Chart.js
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Sweet Alert Pop-up
window.addEventListener("alert", function (event) { 
    Swal.fire({
        text: event.detail.message,
        icon: event.detail.icon,
        showConfirmButton: false,
        timerProgressBar: true,
        position: 'top-end',
        timer: 2000,
        toast: true,
    });     
});
