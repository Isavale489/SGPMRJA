import { useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, AtSign, Info, SearchCheck } from 'lucide-react';

import AuthLayout from '@/layouts/auth-layout';

import { BotonAcceso, CampoAcceso, EnlaceAcceso, Intro } from '../piezas';

interface Props {
    urls: { continuar: string; volver: string };
}

/** Paso 1 de la recuperación por preguntas: el correo de la cuenta. */
export default function Correo({ urls }: Props) {
    const form = useForm({ email: '' });

    return (
        <AuthLayout titulo="Buscar cuenta" icono={SearchCheck}>
            <Intro>Ingresa tu correo electrónico. Si tienes preguntas de seguridad configuradas, podrás responderlas para recuperar tu contraseña.</Intro>
            <form
                noValidate
                className="grid gap-4"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(urls.continuar);
                }}
            >
                <CampoAcceso
                    etiqueta="Correo electrónico"
                    icono={AtSign}
                    name="email"
                    type="email"
                    autoComplete="username"
                    placeholder="correo@empresa.com"
                    autoFocus
                    aria-invalid={form.errors.email ? true : undefined}
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                />
                {/* Mensaje genérico a propósito (no revela si la cuenta existe): se muestra como aviso, no como error del campo. */}
                {form.errors.email && (
                    <p role="alert" className="border-primary/20 bg-primary/5 flex items-start gap-2 rounded-lg border p-3 text-sm">
                        <Info className="text-primary mt-0.5 size-4 shrink-0" aria-hidden /> {form.errors.email}
                    </p>
                )}
                <div className="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <EnlaceAcceso href={urls.volver} icono={ArrowLeft}>
                        Volver
                    </EnlaceAcceso>
                    <BotonAcceso id="submitBtn" procesando={form.processing} texto="Continuar" textoProcesando="Verificando…" icono={ArrowRight} />
                </div>
            </form>
        </AuthLayout>
    );
}
