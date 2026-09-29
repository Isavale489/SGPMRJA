import { useForm } from '@inertiajs/react';
import { ArrowLeft, CircleAlert, CircleCheck, Info, MessageCircleQuestion, ShieldQuestion } from 'lucide-react';

import { Input } from '@/components/ui/input';
import AuthLayout from '@/layouts/auth-layout';

import { BotonAcceso, EnlaceAcceso } from '../piezas';

interface Pregunta {
    id: number;
    orden: number;
    pregunta: string;
}

interface Props {
    questions: Pregunta[];
    urls: { validar: string; cancelar: string };
}

// Absorben el autocompletado del navegador: sin esto rellena las respuestas con el usuario y la clave guardados.
const SENUELO = 'pointer-events-none absolute size-0 opacity-0';

/** Paso 2: las 3 preguntas de seguridad del usuario. */
export default function Preguntas({ questions, urls }: Props) {
    const form = useForm<{ respuestas: Record<number, string> }>({ respuestas: Object.fromEntries(questions.map((q) => [q.id, ''])) });
    const error = form.errors.respuestas ?? Object.entries(form.errors).find(([k]) => k.startsWith('respuestas'))?.[1];

    return (
        <AuthLayout titulo="Verificar identidad" icono={ShieldQuestion}>
            <div className="bg-muted/50 grid gap-2 rounded-lg p-3 text-sm">
                <p className="flex items-center gap-2">
                    <Info className="text-primary size-4 shrink-0" aria-hidden /> Escribe las respuestas que registraste en tu perfil.
                </p>
                {/* Espejo de RecoveryQuestionController::normalizeAnswer(): mayúsculas y espacios sí; las tildes NO se ignoran. */}
                <p className="text-muted-foreground text-xs">No importan las mayúsculas ni los espacios de más.</p>
            </div>

            {error && (
                <p role="alert" className="border-destructive/30 bg-destructive/10 text-destructive flex items-start gap-2 rounded-lg border p-3 text-sm">
                    <CircleAlert className="mt-0.5 size-4 shrink-0" aria-hidden /> {error}
                </p>
            )}

            <form
                noValidate
                autoComplete="off"
                className="relative grid gap-4"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(urls.validar, { onFinish: () => form.reset() });
                }}
            >
                <input type="text" name="autofill_decoy_user" autoComplete="username" tabIndex={-1} aria-hidden className={SENUELO} />
                <input type="password" name="autofill_decoy_pass" autoComplete="current-password" tabIndex={-1} aria-hidden className={SENUELO} />
                {questions.map((q) => (
                    <div key={q.id} className="grid gap-2 rounded-lg border p-3">
                        <label htmlFor={`respuesta_${q.id}`} className="flex items-start gap-2 text-sm font-medium">
                            <span className="bg-primary text-primary-foreground grid size-5 shrink-0 place-items-center rounded-full text-xs">{q.orden}</span>
                            {q.pregunta}
                        </label>
                        <div className="relative">
                            <MessageCircleQuestion className="text-primary pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2" aria-hidden />
                            <Input
                                id={`respuesta_${q.id}`}
                                name={`respuestas[${q.id}]`}
                                className="h-11 pl-9"
                                autoComplete="new-password"
                                autoCorrect="off"
                                autoCapitalize="off"
                                spellCheck={false}
                                maxLength={255}
                                placeholder="Tu respuesta…"
                                value={form.data.respuestas[q.id] ?? ''}
                                onChange={(e) => form.setData('respuestas', { ...form.data.respuestas, [q.id]: e.target.value })}
                            />
                        </div>
                    </div>
                ))}
                <div className="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <EnlaceAcceso href={urls.cancelar} icono={ArrowLeft}>
                        Cancelar
                    </EnlaceAcceso>
                    <BotonAcceso id="submitBtn" procesando={form.processing} texto="Verificar respuestas" textoProcesando="Verificando…" icono={CircleCheck} />
                </div>
            </form>
        </AuthLayout>
    );
}
