import { useForm } from '@inertiajs/react';
import { ArrowLeft, LockKeyhole, LockKeyholeOpen, Mail, Save } from 'lucide-react';

import AuthLayout from '@/layouts/auth-layout';
import { POLITICA_CONTRASENA } from '@/lib/contrasena';

import { BotonAcceso, CampoAcceso, EnlaceAcceso } from './piezas';

interface Props {
    token: string;
    email: string;
    urls: { guardar: string; login: string };
}

/** Nueva contraseña desde el enlace que llegó por correo. */
export default function RestablecerClave({ token, email, urls }: Props) {
    const form = useForm({ token, email, password: '', password_confirmation: '' });

    return (
        <AuthLayout titulo="Nueva contraseña" icono={LockKeyhole}>
            <form
                noValidate
                className="grid gap-4"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(urls.guardar, { onFinish: () => form.reset('password', 'password_confirmation') });
                }}
            >
                <CampoAcceso
                    etiqueta="Correo electrónico"
                    icono={Mail}
                    error={form.errors.email}
                    name="email"
                    type="email"
                    autoComplete="username"
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                />
                <CampoAcceso
                    etiqueta="Nueva contraseña"
                    icono={LockKeyhole}
                    error={form.errors.password}
                    ayuda={POLITICA_CONTRASENA}
                    clave
                    name="password"
                    autoComplete="new-password"
                    maxLength={72}
                    autoFocus
                    value={form.data.password}
                    onChange={(e) => form.setData('password', e.target.value)}
                />
                <CampoAcceso
                    etiqueta="Confirmar contraseña"
                    icono={LockKeyholeOpen}
                    error={form.errors.password_confirmation}
                    clave
                    name="password_confirmation"
                    autoComplete="new-password"
                    maxLength={72}
                    value={form.data.password_confirmation}
                    onChange={(e) => form.setData('password_confirmation', e.target.value)}
                />
                <div className="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <EnlaceAcceso href={urls.login} icono={ArrowLeft}>
                        Volver al inicio
                    </EnlaceAcceso>
                    <BotonAcceso id="submitBtn" procesando={form.processing} texto="Restablecer contraseña" textoProcesando="Guardando…" icono={Save} />
                </div>
            </form>
        </AuthLayout>
    );
}
