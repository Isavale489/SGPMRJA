import { Building2, Mail, MapPin, Phone, UserRound } from 'lucide-react';

import { Dato } from '@/components/app/dato';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { formatoFecha } from '@/lib/formato';

import type { ProveedorFila } from './tipos';

const TIPO_TEL = { movil: 'Móvil', casa: 'Casa', trabajo: 'Trabajo' } as const;

export function DetalleProveedor({ proveedor, onCerrar }: { proveedor?: ProveedorFila; onCerrar: () => void }) {
    return (
        <Dialog open={Boolean(proveedor)} onOpenChange={(a) => !a && onCerrar()}>
            <DialogContent className="sm:max-w-lg">
                {proveedor && (
                    <>
                        <DialogHeader>
                            <DialogTitle>{proveedor.nombre}</DialogTitle>
                            <DialogDescription className="font-mono">
                                {proveedor.documento} · {proveedor.tipo === 'natural' ? 'Natural' : 'Jurídico'}
                                {proveedor.inhabilitado && ' · Inhabilitado'}
                            </DialogDescription>
                        </DialogHeader>
                        <dl className="grid gap-4">
                            <Dato icono={<Mail />} etiqueta="Correo">{proveedor.email ?? '—'}</Dato>
                            <Dato icono={<Phone />} etiqueta="Teléfonos">
                                {proveedor.telefonos.length
                                    ? proveedor.telefonos.map((t) => (
                                          <span key={t.numero} className="block tabular">
                                              {t.numero} <span className="text-muted-foreground text-xs">({TIPO_TEL[t.tipo]}{t.es_principal ? ', principal' : ''})</span>
                                          </span>
                                      ))
                                    : '—'}
                            </Dato>
                            {proveedor.tipo === 'juridico' && (
                                <Dato icono={<UserRound />} etiqueta="Contacto">
                                    {proveedor.contacto ?? '—'}
                                    {proveedor.telefono_contacto && <span className="text-muted-foreground tabular"> · {proveedor.telefono_contacto}</span>}
                                </Dato>
                            )}
                            <Dato icono={<MapPin />} etiqueta="Dirección">
                                {proveedor.direccion ?? '—'}
                                {proveedor.estado_territorial && (
                                    <span className="text-muted-foreground block text-xs">
                                        {[proveedor.ciudad, proveedor.estado_territorial].filter(Boolean).join(', ')}
                                    </span>
                                )}
                            </Dato>
                            {proveedor.creado && (
                                <Dato icono={<Building2 />} etiqueta="Registrado">{formatoFecha(proveedor.creado)}</Dato>
                            )}
                        </dl>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
