import type { DatosCompartidos } from '@/types';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: DatosCompartidos;
        errorValueType: string;
    }
}
