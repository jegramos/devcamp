import tsEslint from 'typescript-eslint'
import eslint from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import eslintConfigPrettier from 'eslint-config-prettier'
import globalsOptions from 'globals'

export default tsEslint.config(
  eslint.configs.recommended,
  ...tsEslint.configs.recommended,
  ...pluginVue.configs['flat/recommended'],
  {
    plugins: { 'typescript-eslint': tsEslint.plugin },
    languageOptions: {
      globals: {
        ...globalsOptions.browser,
        ...globalsOptions.node,
        ...globalsOptions.es2021,
      },
      parserOptions: {
        parser: tsEslint.parser,
        project: './tsconfig.json',
        extraFileExtensions: ['.vue'],
        sourceType: 'module',
      },
    },
  },
  eslintConfigPrettier
)
