// vite.config.ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';
import { fileURLToPath } from 'url';
import { dirname } from 'path';

// ESM 環境で __dirname を定義
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'index.html'),
        login: resolve(__dirname, 'login.html'),
      },
      output: {
        // Remove hash from entry file names
        entryFileNames: '[name].js',
        // Remove hash from chunk file names (shared dependencies)
        chunkFileNames: '[name].js',
        // Keep CSS in assets folder with original names
        assetFileNames: 'assets/[name].[ext]',
        // Prevent automatic code splitting as much as possible
        manualChunks: undefined,
      },
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
});
