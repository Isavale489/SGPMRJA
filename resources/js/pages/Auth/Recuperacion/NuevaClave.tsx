import { useForm } from '@inertiajs/react';
import { LockKeyhole, Save, ShieldCheck } from 'lucide-react';

import AuthLayout from '@/layouts/auth-layout';
import { POLITICA_CONTRASENA } from '@/lib/contrasena';

import { BotonAcceso, CampoAcceso } from '../piezas';

interface Props {
    token: string;
    urls: { guardar: string };
}

/** Paso 3: identidad verificada, nueva contraseña (el token vence en pocos minutos). */
export default function NuevaClave({ token, urls }: Props) {
    const form = useForm({ token, password: '', password_confirmation: '' });

    return (
        <AuthLayout titulo="Nueva contraseña" icono={LockKeyhole}>
            <div className="border-success/30 bg-success/10 flex items-center gap-3 rounded-lg border p-3">
                <ShieldCheck className="text-success size-6 shrink-0" aria-hidden />
                <div className="text-sm">
                    <p className="font-semibold">Identidad verificada</p>
                    <p className="text-muted-foreground">Establece tu nueva contraseña a continuación.</p>
                </div>
            </div>
            <form
                noValidate
                className="grid gap-4"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(urls.guardar, { onFinish: () => form.reset('password', 'password_confirmation') });
                }}
            >
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
                    icono={LockKeyhole}
                    error={form.errors.password_confirmation}
                    clave
                    name="password_confirmation"
                    autoComplete="new-password"
                    maxLength={72}
                    placeholder="Repite la contraseña"
                    value={form.data.password_confirmation}
                    onChange={(e) => form.setData('password_confirmation', e.target.value)}
                />
                <div className="flex justify-end pt-1">
                    <BotonAcceso id="submitBtn" procesando={form.processing} texto="Guardar nueva contraseña" textoProcesando="Guardando…" icono={Save} />
                </div>
            </form>
        </AuthLayout>
    );
}
