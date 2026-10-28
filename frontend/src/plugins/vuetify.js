import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { es } from 'vuetify/locale'

const customTheme = {
  dark: false,
  colors: {
    background: '#F5F5F5',
    surface: '#FFFFFF',
    primary: '#1A237E',
    secondary: '#0D47A1',
    accent: '#FF6F00',
    error: '#D32F2F',
    info: '#1976D2',
    success: '#388E3C',
    warning: '#F57C00',
  },
}

export default createVuetify({
  components,
  directives,
  locale: {
    locale: 'es',
    messages: { es },
  },
  theme: {
    defaultTheme: 'customTheme',
    themes: { customTheme },
  },
  defaults: {
    VCard: { elevation: 2 },
    VBtn: { rounded: 'lg' },
    VTextField: { variant: 'outlined', density: 'comfortable' },
    VSelect: { variant: 'outlined', density: 'comfortable' },
    VTextarea: { variant: 'outlined', density: 'comfortable' },
  },
})
