import Alpine from 'alpinejs'
window.Alpine = Alpine


document.addEventListener('DOMContentLoaded', () => {
    import('../../Modules/Estimation/Resources/assets/js/estimation.show')
        .then(() => {
            Alpine.start()
        });
});

// import { createApp } from 'vue';
// import EstimationApp from '../../Modules/Estimation/Resources/assets/js/app.js';

// const app = createApp({});

// EstimationApp(app);

// app.mount('#app');

// $.ajaxSetup({
//     headers: {
//         "X-CSRF-TOKEN": $('[name="csrf-token"]').attr('content'),
//     },
// }); 