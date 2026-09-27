#!/usr/bin/env node
/**
 * Agrega componentes de shadcn/ui a resources/js/components/ui y corrige lo que
 * el CLI hace mal en este repo:
 *   - importa `cn` desde el paquete npm "cn" (ajeno) en vez de @/lib/utils;
 *   - agrega ese paquete "cn" (y next-themes) a las dependencias.
 *
 *   npm run ui:add -- sheet popover
 */
import { execFileSync } from 'node:child_process';
import { readdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const componentes = process.argv.slice(2);
if (componentes.length === 0) {
    console.error('Uso: npm run ui:add -- <componente> [<componente>...]');
    process.exit(1);
}

execFileSync('npx', ['-y', 'shadcn@latest', 'add', ...componentes, '--yes'], { stdio: 'inherit' });

const dir = 'resources/js/components/ui';
for (const archivo of readdirSync(dir).filter((f) => f.endsWith('.tsx'))) {
    const ruta = join(dir, archivo);
    const antes = readFileSync(ruta, 'utf8');
    const despues = antes
        .replace(/from ["']cn["']/g, 'from "@/lib/utils"')
        .replace(/import \{ useTheme \} from ["']next-themes["']/g, 'import { useTema as useTheme } from "@/hooks/use-tema"');
    if (despues !== antes) {
        writeFileSync(ruta, despues);
        console.log(`corregido: ${ruta}`);
    }
}

const pkg = JSON.parse(readFileSync('package.json', 'utf8'));
const sobrantes = ['cn', 'next-themes'].filter((d) => pkg.dependencies?.[d]);
if (sobrantes.length) {
    execFileSync('npm', ['uninstall', ...sobrantes], { stdio: 'inherit' });
}
console.log('Listo. Revisa el diff antes de commitear.');
