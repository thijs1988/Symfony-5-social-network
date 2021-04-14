import Vue from 'vue';
import VueRouter from 'vue-router';
import store from "./store/store";

import App from "./components/App";
import Blank from "./components/Right/Blank";
import Right from "./components/Right/Right";

Vue.use(VueRouter)

const routes = [
    {
    name: 'blank',
    path: '/index_conversation',
    component: Blank
    },
    {
        name: 'conversation',
        path: '/conversation/:id',
        component: Right
    }
];

const router = new VueRouter({
    mode: "abstract",
    routes
})

store.commit("SET_USERNAME", document.querySelector('#app').dataset.username);

new Vue ({
    store,
    router,
    render: h => h(App)
}).$mount('#app');

router.replace('/index_conversation')

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

