import { FileDown } from 'lucide-react';
import { useState } from 'react';

import { Campo } from '@/components/app/campo';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

/** Un filtro del reporte: su parámetro en la URL y las opciones (la primera, "todos", no se envía). */
export interface FiltroPdf {
    parametro: string;
    etiqueta: string;
    todos: string;
    opciones: { valor: string; etiqueta: string }[];
}

interface Props {
    url: string;
    /** "proveedores", "clientes": para el texto del diálogo. */
    recurso: string;
    /** Los parámetros no son iguales en todos los reportes (tipo_proveedor, estatus, stock…). */
    filtros: FiltroPdf[];
}

const TODOS = 'todos';

/** Reporte PDF (dompdf, sin cambios en el servidor): abre en otra pestaña con los filtros elegidos. */
export function ExportarPdf({ url, recurso, filtros }: Props) {
    const [abierto, setAbierto] = useState(false);
    const [valores, setValores] = useState<Record<string, string>>({});
    const [desde, setDesde] = useState('');
    const [hasta, setHasta] = useState('');

    const generar = () => {
        const p = new URLSearchParams();
        for (const [parametro, valor] of Object.entries(valores)) {
            if (valor !== TODOS) p.set(parametro, valor);
        }
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
                    <DialogDescription>Elige qué {recurso} incluir en el reporte.</DialogDescription>
                </DialogHeader>
                <div className="grid gap-4">
                    {filtros.map((f) => (
                        <Campo key={f.parametro} etiqueta={f.etiqueta}>
                            {(control) => (
                                <Select value={valores[f.parametro] ?? TODOS} onValueChange={(v) => setValores((a) => ({ ...a, [f.parametro]: v }))}>
                                    <SelectTrigger {...control} className="w-full"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={TODOS}>{f.todos}</SelectItem>
                                        {f.opciones.map((o) => (
                                            <SelectItem key={o.valor} value={o.valor}>{o.etiqueta}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            )}
                        </Campo>
                    ))}
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
