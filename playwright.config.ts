import { defineConfig, devices } from '@playwright/test';

/**
 * Smoke E2E (paso 0 de docs/auditoria_migracion_frontend.html).
 * Levanta su propio servidor contra la BD sistema_atlantico_e2e (reconstruida
 * con E2eSeeder en cada corrida); nunca toca la BD de desarrollo.
 *
 *   npm run test:e2e
 */
const PORT = Number(process.env.E2E_PORT ?? 8010);

export default defineConfig({
  testDir: 'tests/e2e',
  // Un solo worker: los flujos comparten la BD sembrada y cambian su estado.
  workers: 1,
  fullyParallel: false,
  retries: process.env.CI ? 1 : 0,
  timeout: 60_000,
  reporter: process.env.CI ? [['list'], ['html', { open: 'never' }]] : 'list',
  use: {
    baseURL: `http://127.0.0.1:${PORT}`,
    locale: 'es-VE',
    timezoneId: 'America/Caracas',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'setup', testMatch: /auth\.setup\.ts/ },
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'], storageState: 'tests/e2e/.auth/admin.json' },
      dependencies: ['setup'],
    },
  ],
  webServer: {
    command: 'bash tests/e2e/servidor.sh',
    url: `http://127.0.0.1:${PORT}/login`,
    timeout: 180_000,
    reuseExistingServer: false,
    stdout: 'ignore',
    stderr: 'pipe',
  },
});
