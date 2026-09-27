import {
    Archive, ArrowLeftRight, Boxes, Briefcase, Building, CalendarCheck, ChartColumn, Circle, Database,
    Factory, FileChartColumn, FileText, Gauge, Layers, LayoutDashboard, ListChecks, Palette, Repeat,
    ShieldCheck, Shirt, ShoppingBag, ShoppingCart, SlidersHorizontal, Truck, UserCog, UserRound, Users,
    type LucideIcon, type LucideProps,
} from 'lucide-react';

/**
 * Íconos disponibles para config/navegacion.php (por nombre de lucide).
 * Import explícito, no `icons` completo: mantiene el bundle chico.
 * Un ícono nuevo en la config debe agregarse aquí (lo verifica un test).
 */
export const ICONOS: Record<string, LucideIcon> = {
    Archive, ArrowLeftRight, Boxes, Briefcase, Building, CalendarCheck, ChartColumn, Database, Factory,
    FileChartColumn, FileText, Gauge, Layers, LayoutDashboard, ListChecks, Palette, Repeat, ShieldCheck,
    Shirt, ShoppingBag, ShoppingCart, SlidersHorizontal, Truck, UserCog, UserRound, Users,
};

export function Icono({ nombre, ...props }: { nombre: string } & LucideProps) {
    const Componente = ICONOS[nombre] ?? Circle;
    return <Componente aria-hidden {...props} />;
}
