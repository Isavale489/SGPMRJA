import { Building2, Mail, MapPin, Phone, UserRoundCheck } from 'lucide-react';

import { Dato } from '@/components/app/dato';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { formatoFecha } from '@/lib/formato';

import { ETIQUETA_TIPO, type ClienteFila } from './tipos';

const TIPO_TEL = { movil: 'Móvil', casa: 'Casa', trabajo: 'Trabajo' } as const;

export function DetalleCliente({ cliente, onCerrar }: { cliente?: ClienteFila; onCerrar: () => void }) {
    return (
        <Dialog open={Boolean(cliente)} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="sm:max-w-lg">
                {cliente && (
                    <>
                        <DialogHeader>
                            <DialogTitle>{cliente.nombre}</DialogTitle>
                            <DialogDescription className="font-mono">
                                {cliente.documento} · {ETIQUETA_TIPO[cliente.tipo]}
                                {cliente.inhabilitado && ' · Inhabilitado'}
                            </DialogDescription>
                        </DialogHeader>
                        <dl className="grid gap-4">
                            <Dato icono={<Mail />} etiqueta="Correo">{cliente.email ?? '—'}</Dato>
                            <Dato icono={<Phone />} etiqueta="Teléfonos">
                                {cliente.telefonos.length
                                    ? cliente.telefonos.map((t) => (
                                          <span key={t.numero} className="block tabular">
                                              {t.numero} <span className="text-muted-foreground text-xs">({TIPO_TEL[t.tipo]}{t.es_principal ? ', principal' : ''})</span>
                                          </span>
                                      ))
                                    : '—'}
                            </Dato>
                            <Dato icono={<MapPin />} etiqueta="Dirección">
                                {cliente.direccion ?? '—'}
                                {cliente.estado_territorial && (
                                    <span className="text-muted-foreground block text-xs">
                                        {[cliente.ciudad, cliente.estado_territorial].filter(Boolean).join(', ')}
                                    </span>
                                )}
                            </Dato>
                            {cliente.otros_roles.length > 0 && (
                                <Dato icono={<UserRoundCheck />} etiqueta="También registrado como">
                                    <span className="capitalize">{cliente.otros_roles.join(', ')}</span>
                                </Dato>
                            )}
                            {cliente.creado && (
                                <Dato icono={<Building2 />} etiqueta="Registrado">{formatoFecha(cliente.creado)}</Dato>
                            )}
                        </dl>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
