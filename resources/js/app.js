import { createApp } from 'vue';
import EstimationApp from '../../Modules/Estimation/Resources/assets/js/app.js';

const app = createApp({});

EstimationApp(app);

app.mount('#app');

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('[name="csrf-token"]').attr('content'),
    },
}); 