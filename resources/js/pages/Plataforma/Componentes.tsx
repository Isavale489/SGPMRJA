import { Plus, Trash2 } from 'lucide-react';
import { useState, type ReactNode } from 'react';
import { toast } from 'sonner';

import { Campo } from '@/components/app/campo';
import { ConfirmarPeligro } from '@/components/app/confirmar-peligro';
import { EstadoBadge } from '@/components/app/estado-badge';
import { Monto } from '@/components/app/monto';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';

/**
 * Catálogo vivo de la plataforma: tokens, componentes base y patrones de
 * dominio. Referencia para quien migre un módulo (ver docs/conventions/frontend.md)
 * y objetivo del smoke E2E. Solo administradores.
 */
export default function Componentes() {
    return (
        <AppLayout
            titulo="Componentes de la plataforma"
            acciones={
                <Button onClick={() => toast.success('Guardado correctamente.')}>
                    <Plus /> Acción principal
                </Button>
            }
        >
            <div className="grid gap-6">
                <Seccion titulo="Colores" descripcion="Tokens de resources/css/plataforma.css. Cambian con el tema.">
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
                        {['primary', 'secondary', 'accent', 'muted', 'success', 'warning', 'destructive', 'border'].map((t) => (
                            <div key={t} className="grid gap-1.5">
                                <div className="border-border h-12 rounded-md border" style={{ background: `var(--${t})` }} />
                                <code className="text-muted-foreground font-mono text-xs">--{t}</code>
                            </div>
                        ))}
                    </div>
                </Seccion>

                <Seccion titulo="Botones" descripcion="Una acción principal por vista; el resto, secundarias o fantasma.">
                    <div className="flex flex-wrap gap-2">
                        <Button>Principal</Button>
                        <Button variant="secondary">Secundario</Button>
                        <Button variant="outline">Contorno</Button>
                        <Button variant="ghost">Fantasma</Button>
                        <Button variant="destructive">Destructivo</Button>
                        <Button disabled>Deshabilitado</Button>
                    </div>
                </Seccion>

                <Seccion titulo="Estados" descripcion="EstadoBadge: el mismo tono para el mismo estado en todo el sistema.">
                    <div className="flex flex-wrap gap-2">
                        {['Pendiente', 'Aprobada', 'En Proceso', 'Finalizado', 'Convertida', 'borrador', 'Cancelado', 'Vencida'].map((e) => (
                            <EstadoBadge key={e} estado={e} />
                        ))}
                    </div>
                </Seccion>

                <Seccion titulo="Montos" descripcion="Todo monto en USD va con su equivalente en Bs y la tasa (con fecha).">
                    <div className="flex flex-wrap gap-8">
                        <Monto usd={180} />
                        <Monto usd={4820.5} tasa={{ valor: 38.5, fecha: '2026-08-25' }} />
                    </div>
                </Seccion>

                <Seccion titulo="Formularios" descripcion="Campo muestra el error que devuelve Laravel; no se duplica la validación.">
                    <div className="grid max-w-xl gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Razón social" requerido>
                            <Input defaultValue="Textiles del Llano C.A." />
                        </Campo>
                        <Campo etiqueta="RIF" requerido error="Este documento ya está registrado como proveedor.">
                            <Input defaultValue="J-12345678" />
                        </Campo>
                        <Campo etiqueta="Tipo" ayuda="Se deriva del prefijo del documento.">
                            {(control) => (
                            <Select defaultValue="juridico">
                                <SelectTrigger {...control} className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="natural">Natural</SelectItem>
                                    <SelectItem value="juridico">Jurídico</SelectItem>
                                    <SelectItem value="gubernamental">Gubernamental</SelectItem>
                                </SelectContent>
                            </Select>
                            )}
                        </Campo>
                    </div>
                </Seccion>

                <Seccion titulo="Capas flotantes" descripcion="Se renderizan en un portal: ningún overflow de un contenedor las recorta.">
                    <div className="flex flex-wrap gap-2">
                        <DialogoEjemplo />
                        <ConfirmarPeligro
                            titulo="¿Eliminar este proveedor?"
                            descripcion="Se inhabilita y deja de aparecer en compras nuevas. Las compras existentes no cambian."
                            onConfirmar={() => toast.success('Proveedor eliminado.')}
                        >
                            <Button variant="outline">
                                <Trash2 /> Confirmación destructiva
                            </Button>
                        </ConfirmarPeligro>
                    </div>
                </Seccion>

                <Seccion titulo="Tabla" descripcion="Base visual; la tabla con datos del servidor (paginación, búsqueda) se agrega con el piloto.">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Código</TableHead>
                                <TableHead>Cliente</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead className="text-right">Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {[
                                ['COT-0184', 'Confecciones Portuguesa', 'Aprobada', 4820],
                                ['COT-0185', 'Uniformes Araure', 'Pendiente', 1260],
                                ['COT-0186', 'Liceo Juan Pablo II', 'Vencida', 930],
                            ].map(([codigo, cliente, estado, total]) => (
                                <TableRow key={String(codigo)}>
                                    <TableCell className="font-mono text-xs">{codigo}</TableCell>
                                    <TableCell>{cliente}</TableCell>
                                    <TableCell>
                                        <EstadoBadge estado={String(estado)} />
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <Monto usd={Number(total)} className="items-end" />
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Seccion>
            </div>
        </AppLayout>
    );
}

function Seccion({ titulo, descripcion, children }: { titulo: string; descripcion: string; children: ReactNode }) {
    return (
        <Card className="min-w-0">
            <CardHeader>
                <CardTitle>{titulo}</CardTitle>
                <CardDescription>{descripcion}</CardDescription>
            </CardHeader>
            <CardContent>{children}</CardContent>
        </Card>
    );
}

function DialogoEjemplo() {
    const [abierto, setAbierto] = useState(false);

    return (
        <Dialog open={abierto} onOpenChange={setAbierto}>
            <DialogTrigger asChild>
                <Button variant="outline">Abrir diálogo</Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Nuevo proveedor</DialogTitle>
                    <DialogDescription>Los diálogos anidados y los menús dentro de ellos no se pisan.</DialogDescription>
                </DialogHeader>
                <Campo etiqueta="Banco" ayuda="Este menú antes se recortaba con el footer del modal.">
                    {(control) => (
                    <Select>
                        <SelectTrigger {...control} className="w-full">
                            <SelectValue placeholder="Selecciona un banco" />
                        </SelectTrigger>
                        <SelectContent>
                            {['Banco de Venezuela', 'Banesco', 'Mercantil', 'Provincial', 'BNC'].map((b) => (
                                <SelectItem key={b} value={b}>{b}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    )}
                </Campo>
                <DialogFooter>
                    <Button variant="outline" onClick={() => setAbierto(false)}>Cancelar</Button>
                    <Button onClick={() => { setAbierto(false); toast.success('Proveedor creado.'); }}>Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
