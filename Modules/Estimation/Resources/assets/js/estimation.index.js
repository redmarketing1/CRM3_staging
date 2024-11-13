export default function (app) {
    app.component('estimation-index', {
        template: '<slot v-bind="{ title, description }"/>',
        props: {
            title: {
                type: String,
                required: true
            },
            description: {
                type: String,
                required: true
            }
        }
    });
}