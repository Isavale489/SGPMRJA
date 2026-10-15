import { useForm } from '@inertiajs/react';
import { CircleHelp, CircleUserRound, LockKeyhole, LogIn, Mail } from 'lucide-react';

import AuthLayout from '@/layouts/auth-layout';

import { BotonAcceso, CampoAcceso, EnlaceAcceso } from './piezas';

interface Props {
    urls: { login: string; recuperar: string };
}

export default function Login({ urls }: Props) {
    const form = useForm({ email: '', password: '', remember: false });

    return (
        <AuthLayout titulo="Bienvenido de nuevo" icono={CircleUserRound}>
            <form
                id="loginForm"
                noValidate
                className="grid gap-4"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(urls.login, { onFinish: () => form.reset('password') });
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
                <CampoAcceso
                    etiqueta="Contraseña"
                    icono={LockKeyhole}
                    error={form.errors.password}
                    clave
                    name="password"
                    autoComplete="current-password"
                    placeholder="••••••••"
                    value={form.data.password}
                    onChange={(e) => form.setData('password', e.target.value)}
                />
                <label className="flex w-fit items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" className="accent-primary size-4" checked={form.data.remember} onChange={(e) => form.setData('remember', e.target.checked)} />
                    Recuérdame
                </label>
                <div className="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <EnlaceAcceso href={urls.recuperar} icono={CircleHelp}>
                        ¿Olvidaste tu contraseña?
                    </EnlaceAcceso>
                    <BotonAcceso id="submitBtn" procesando={form.processing} texto="Iniciar sesión" textoProcesando="Ingresando…" icono={LogIn} />
                </div>
            </form>
        </AuthLayout>
    );
}
