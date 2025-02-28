

import './bootstrap';
import { createApp } from 'vue';

import { createApp } from 'vue';
import DragDrop from './components/DragDrop.vue';

const app = createApp({});
app.component('drag-drop', DragDrop);
app.mount('#app');


import ExampleComponent from './components/ExampleComponent.vue';
app.component('example-component', ExampleComponent);



app.mount('#app');
