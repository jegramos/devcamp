import '../css/app.css'
import { createApp, type DefineComponent, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { primeVue } from './Plugins/primevue'
import ToastService from 'primevue/toastservice'
import Tooltip from 'primevue/tooltip'

createInertiaApp({
  title: (title) => (title ? `${title} - DevFolio` : 'DevFolio'),
  resolve: (name) => {
    const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(primeVue.options, primeVue.config)
      .use(ToastService)

    app.directive('tooltip', Tooltip)
    app.mount(el)
  },
  progress: {
    color: '#f5940b',
    showSpinner: true,
  },
}).then(() => {})
