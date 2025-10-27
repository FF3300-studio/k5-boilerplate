import { defineConfig } from 'vite';
import liveReload from 'vite-plugin-live-reload';
import { spawn } from 'child_process';

export default defineConfig(({ command }) => {
  const isProduction = command === 'build';

  if (command === 'serve') {
    // Avvia il server PHP
    const phpServer = spawn('php', ['-S', '127.0.0.1:8000'], {
      stdio: 'inherit', // Mostra gli output del server PHP
    });

    // Ferma il server PHP al termine di Vite
    process.on('exit', () => phpServer.kill());
    process.on('SIGINT', () => phpServer.kill());
    process.on('SIGTERM', () => phpServer.kill());
  }

  return {
    root: 'assets/src', // Set root folder to 'src'
    base: '/', // Base URL for development
    build: {
      outDir: '../build', // Compiled output folder
      emptyOutDir: false,
      rollupOptions: {
        input: {
          css: 'assets/src/sass/style.scss', // Main SCSS entry
          js: 'assets/src/js/scripts.js', // Main JS entry
        },
        output: {
          entryFileNames: 'js/[name].js', // Custom name for JS files
          assetFileNames: (assetInfo) => {
            if (assetInfo.name.endsWith('.css')) {
              return 'css/[name][extname]'; // Custom name for CSS files
            }
          },
        },
      },
    },
    plugins: [
      liveReload(['site//', 'content//', 'assets/src/*/']), // Watch files for live reload in Kirby
    ],
    css: {
      preprocessorOptions: {
        scss: {
          // additionalData: @import "sass/variables/colors.scss"; @import "sass/variables/typography.scss"; @import "sass/variables/structure.scss";, // Automatically import variables
        },
      },
    },
    server: {
      host: '127.0.0.1',
      port: 3004, // Port for development
      strictPort: true,
      proxy: {
        // Proxy tutte le richieste verso il server PHP
        '/': {
          target: 'http://127.0.0.1:8000', // Server PHP locale
          changeOrigin: true,
        },
      },
    },
  };
});