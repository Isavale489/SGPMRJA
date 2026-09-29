import { useForm } from '@inertiajs/react';
import { ArrowLeft, KeyRound, Mail, Send } from 'lucide-react';

import AuthLayout from '@/layouts/auth-layout';

import { BotonAcceso, CampoAcceso, EnlaceAcceso, Intro } from './piezas';

interface Props {
    urls: { enviar: string; volver: string };
}

/** Recuperación por correo: envía el enlace para restablecer la contraseña. */
export default function OlvideClave({ urls }: Props) {
    const form = useForm({ email: '' });

    return (
        <AuthLayout titulo="Recuperar por correo" icono={KeyRound}>
            <Intro>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</Intro>
            <form
                noValidate
                className="grid gap-4"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(urls.enviar);
                }}
            >
                <CampoAcceso
                    etiqueta="Correo electrónico"
                    icono={Mail}
                    error={form.errors.email}
                    name="email"
                    type="email"
                    autoComplete="username"
                    placeholder="correo@empresa.com"
                    autoFocus
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                />
                <div className="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <EnlaceAcceso href={urls.volver} icono={ArrowLeft}>
                        Volver al inicio
                    </EnlaceAcceso>
                    <BotonAcceso id="submitBtn" procesando={form.processing} texto="Enviar enlace" textoProcesando="Enviando…" icono={Send} />
                </div>
            </form>
        </AuthLayout>
    );
}
