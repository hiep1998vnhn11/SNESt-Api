import Router from 'vue-router'
import Vue from 'vue'
import HelloWorld from './pages/HelloWorld'
import Layout from './pages/Main/Layout'
import Login from './pages/Auth/Login.vue'
import Dashboard from './pages/Main/Dashboard.vue'
import User from './pages/Main/User.vue'
import Pub from './pages/Main/Pub.vue'
import Rating from './pages/Main/Rating.vue'
import Comment from './pages/Main/Comment.vue'
import Report from './pages/Main/Report.vue'
import Dish from './pages/Main/Dish.vue'
import DishRequest from './pages/Main/Request/DishRequest.vue'
import PubRequest from './pages/Main/Request/PubRequest.vue'
import ParamUser from './pages/Param/User.vue'
import ParamPub from './pages/Param/Pub.vue'

Vue.use(Router)

export default new Router({
    mode: 'history',
    routes: [
        {
            path: '/admin/login',
            component: Login,
            name: 'Login',
            meta: {
                requiresVisitor: true
            }
        },
        {
            path: '/admin',
            component: Layout,
            meta: {
                requiresAuth: true
            },
            children: [
                {
                    path: 'dashboard',
                    name: 'Dashboard',
                    component: Dashboard
                },
                {
                    path: 'pub',
                    name: 'Pub',
                    component: Pub
                },
                {
                    path: 'pub/:pub_id',
                    name: 'ParamPub',
                    component: ParamPub
                },
                {
                    path: 'comment',
                    name: 'Comment',
                    component: Comment
                },
                {
                    path: 'report',
                    name: 'Report',
                    component: Report
                },
                {
                    path: 'rating',
                    name: 'Rating',
                    component: Rating
                },
                {
                    path: 'user',
                    name: 'User',
                    component: User
                },
                {
                    path: 'user/:user_id',
                    name: 'ParamUser',
                    component: ParamUser
                },
                {
                    path: 'dish',
                    name: 'Dish',
                    component: Dish
                },
                {
                    path: 'dish-request',
                    name: 'DishRequest',
                    component: DishRequest
                },
                {
                    path: 'pub-request',
                    name: 'PubRequest',
                    component: PubRequest
                },
                {
                    path: '*',
                    redirect: 'dashboard'
                }
            ]
        }
    ]
})
