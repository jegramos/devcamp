import '../css/app.css'
import { createApp, type DefineComponent, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { primeVue } from './Plugins/primevue'
import ToastService from 'primevue/toastservice'
import Tooltip from 'primevue/tooltip'
import ConfirmationService from 'primevue/confirmationservice'

createInertiaApp({
  title: (title) => (title ? `${title} - DevCamp` : 'DevCamp'),
  resolve: (name) => {
    const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(primeVue.options, primeVue.config)
      .use(ToastService)
      .use(ConfirmationService)

    app.directive('tooltip', Tooltip)
    app.mount(el)
  },
  progress: {
    color: '#f5940b',
    showSpinner: true,
  },
}).then(() => {})
