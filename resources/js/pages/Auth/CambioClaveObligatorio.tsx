import { useForm } from '@inertiajs/react';
import { KeyRound, LogOut, TriangleAlert } from 'lucide-react';

import { Campo } from '@/components/app/campo';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import SimpleLayout from '@/layouts/simple-layout';
import { POLITICA_CONTRASENA } from '@/lib/contrasena';

interface Props {
    aviso: string | null;
    urls: { guardar: string; salir: string };
}

const csrf = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

/** Cambio de la contraseña temporal que dio un administrador (el middleware no deja pasar sin esto). */
export default function CambioClaveObligatorio({ aviso, urls }: Props) {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });

    return (
        <SimpleLayout titulo="Cambiar contraseña">
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <KeyRound className="text-warning size-5" /> Cambio de contraseña obligatorio
                    </CardTitle>
                    <CardDescription>Un administrador te asignó una contraseña temporal. Por seguridad, elige una propia antes de continuar.</CardDescription>
                </CardHeader>
                <CardContent className="grid gap-4">
                    {aviso && (
                        <p className="border-warning/30 bg-warning/10 flex items-start gap-2 rounded-md border p-3 text-sm" role="status">
                            <TriangleAlert className="text-warning mt-0.5 size-4 shrink-0" /> {aviso}
                        </p>
                    )}
                    <form
                        noValidate
                        className="grid gap-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            form.post(urls.guardar, { onFinish: () => form.reset('current_password', 'password', 'password_confirmation') });
                        }}
                    >
                        <Campo etiqueta="Contraseña temporal" requerido error={form.errors.current_password}>
                            <Input type="password" autoComplete="current-password" value={form.data.current_password} onChange={(e) => form.setData('current_password', e.target.value)} autoFocus />
                        </Campo>
                        <Campo etiqueta="Contraseña nueva" requerido error={form.errors.password} ayuda={POLITICA_CONTRASENA}>
                            <Input type="password" autoComplete="new-password" maxLength={72} value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} />
                        </Campo>
                        <Campo etiqueta="Confirma la contraseña nueva" requerido error={form.errors.password_confirmation}>
                            <Input type="password" autoComplete="new-password" maxLength={72} value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} />
                        </Campo>
                        <Button type="submit" disabled={form.processing}>
                            Guardar y continuar
                        </Button>
                    </form>
                    {/* Form clásico: /logout redirige a una página Blade. */}
                    <form method="post" action={urls.salir}>
                        <input type="hidden" name="_token" value={csrf()} />
                        <Button type="submit" variant="ghost" className="w-full">
                            <LogOut /> Cerrar sesión
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </SimpleLayout>
    );
}
