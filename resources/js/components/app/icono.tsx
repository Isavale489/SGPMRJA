import {
    Archive, ArrowLeftRight, Boxes, Briefcase, Building, CalendarCheck, ChartColumn, ChartLine, Circle, Database,
    Factory, FileChartColumn, FileText, Gauge, Hammer, Layers, LayoutDashboard, ListChecks, Palette, Repeat,
    Settings, ShieldCheck, ShieldUser, Shirt, ShoppingBag, ShoppingCart, SlidersHorizontal, Truck, UserCog, UserRound, Users,
    type LucideIcon, type LucideProps,
} from 'lucide-react';

/**
 * Íconos disponibles para config/navegacion.php y config/reportes.php (por nombre de lucide).
 * Import explícito, no `icons` completo: mantiene el bundle chico.
 * Un ícono nuevo en la config debe agregarse aquí (lo verifica un test).
 */
export const ICONOS: Record<string, LucideIcon> = {
    Archive, ArrowLeftRight, Boxes, Briefcase, Building, CalendarCheck, ChartColumn, ChartLine, Database, Factory,
    FileChartColumn, FileText, Gauge, Hammer, Layers, LayoutDashboard, ListChecks, Palette, Repeat, Settings,
    ShieldCheck, ShieldUser, Shirt, ShoppingBag, ShoppingCart, SlidersHorizontal, Truck, UserCog, UserRound, Users,
};

export function Icono({ nombre, ...props }: { nombre: string } & LucideProps) {
    const Componente = ICONOS[nombre] ?? Circle;
    return <Componente aria-hidden {...props} />;
}
