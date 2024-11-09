import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'Modules/Estimation/Resources/assets/js/estimation.js',
      ],
      refresh: true,
    }),
    vue(),
  ],
  resolve: {
    alias: {
      // '@': '/resources/js',
      // '@estimation': path.resolve(__dirname, 'Modules/Estimation/Resources/assets/js/estimation.js'),
    },
  },
});
