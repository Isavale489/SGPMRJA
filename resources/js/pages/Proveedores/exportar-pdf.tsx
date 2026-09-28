import { FileDown } from 'lucide-react';
import { useState } from 'react';

import { Campo } from '@/components/app/campo';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

/** Reporte PDF (dompdf, sin cambios en el servidor): abre en otra pestaña con los filtros elegidos. */
export function ExportarPdf({ url }: { url: string }) {
    const [abierto, setAbierto] = useState(false);
    const [tipo, setTipo] = useState('todos');
    const [estatus, setEstatus] = useState('todos');
    const [desde, setDesde] = useState('');
    const [hasta, setHasta] = useState('');

    const generar = () => {
        const p = new URLSearchParams();
        if (tipo !== 'todos') p.set('tipo_proveedor', tipo);
        if (estatus !== 'todos') p.set('estatus', estatus);
        if (desde) p.set('fecha_desde', desde);
        if (hasta) p.set('fecha_hasta', hasta);
        window.open(`${url}${p.size ? `?${p}` : ''}`, '_blank', 'noopener');
        setAbierto(false);
    };

    return (
        <Dialog open={abierto} onOpenChange={setAbierto}>
            <DialogTrigger asChild>
                <Button variant="outline">
                    <FileDown /> Exportar PDF
                </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>Exportar PDF</DialogTitle>
                    <DialogDescription>Elige qué proveedores incluir en el reporte.</DialogDescription>
                </DialogHeader>
                <div className="grid gap-4">
                    <Campo etiqueta="Tipo">
                        {(control) => (
                        <Select value={tipo} onValueChange={setTipo}>
                            <SelectTrigger {...control} className="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="todos">Todos los tipos</SelectItem>
                                <SelectItem value="natural">Natural</SelectItem>
                                <SelectItem value="juridico">Jurídico</SelectItem>
                            </SelectContent>
                        </Select>
                        )}
                    </Campo>
                    <Campo etiqueta="Estatus">
                        {(control) => (
                        <Select value={estatus} onValueChange={setEstatus}>
                            <SelectTrigger {...control} className="w-full"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="todos">Activos</SelectItem>
                                <SelectItem value="0">Inhabilitados</SelectItem>
                            </SelectContent>
                        </Select>
                        )}
                    </Campo>
                    <div className="grid grid-cols-2 gap-3">
                        <Campo etiqueta="Registro desde">
                            <Input type="date" value={desde} onChange={(e) => setDesde(e.target.value)} />
                        </Campo>
                        <Campo etiqueta="Registro hasta">
                            <Input type="date" value={hasta} onChange={(e) => setHasta(e.target.value)} />
                        </Campo>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" onClick={() => setAbierto(false)}>Cancelar</Button>
                    <Button onClick={generar}>Generar PDF</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
