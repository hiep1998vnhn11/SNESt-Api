require("./bootstrap");
import App from "./App.vue";
import Vue from "vue";
import vuetify from "./plugin/vuetify";
import axios from "axios";
import router from "./router";
import store from "./store";
import i18n from "./plugins/i18n";
import VueSweetalert2 from "vue-sweetalert2";

axios.defaults.baseURL = "/api";
Vue.use(VueSweetalert2);

const app = document.getElementById("app");

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!store.getters["user/isLoggedIn"]) {
            next({
                name: "Login"
            });
        } else {
            next();
        }
    } else if (to.matched.some(record => record.meta.requiresVisitor)) {
        if (store.getters["user/isLoggedIn"]) {
            next({
                name: "Dashboard"
            });
        } else {
            next();
        }
    } else {
        next();
    }
});

new Vue({
    router,
    vuetify,
    store,
    i18n,
    render: function(createElement) {
        return createElement(App);
    }
}).$mount(app);
