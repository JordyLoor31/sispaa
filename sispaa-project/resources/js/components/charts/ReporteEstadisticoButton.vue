<script setup lang="ts">
/**
 * Botón que arma un REPORTE ESTADÍSTICO COMPLETO del apartado en un solo PDF:
 * portada institucional + indicadores clave + todos los gráficos del módulo,
 * uno debajo de otro y paginados. A diferencia del botón "Descargar" de cada
 * ApexChartCard (que baja la imagen de UN gráfico), esto consolida toda la
 * sección en un documento tipo informe.
 *
 * Toma la imagen de cada gráfico ya renderizado en pantalla vía
 * ApexCharts.exec(id, 'dataURI'), así que no vuelve a pedir datos al servidor.
 */
import ApexCharts from 'apexcharts';
import jsPDF from 'jspdf';
import { FileText, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import logoUleamUrl from '@/assets/uleam.png';

const props = defineProps<{
    titulo: string;
    subtitulo?: string;
    kpis?: { label: string; value: string | number }[];
    charts: { id: string; title: string }[];
}>();

const generando = ref(false);

// Paleta de marca (hex fijos: el PDF es un documento, no sigue el tema).
const PRIMARY: [number, number, number] = [60, 110, 113]; // #3c6e71
const TEXT: [number, number, number] = [40, 40, 40];
const MUTED: [number, number, number] = [120, 128, 138];
const LINE: [number, number, number] = [220, 223, 227];

function loadImage(uri: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = uri;
    });
}

/**
 * Descarga la imagen como blob y la convierte a dataURL con FileReader (en
 * vez de pasar por un <canvas>.toDataURL(), que puede fallar en silencio si
 * el canvas queda "tainted"). Pasarle a jsPDF un HTMLImageElement directo
 * (en vez de un dataURL string) también hacía que a veces ignorara el alto
 * indicado y dibujara el logo mucho más grande de lo pedido, invadiendo el
 * título de abajo -- por eso se normaliza siempre a dataURL, igual que ya
 * se hace con las imágenes de los gráficos (ApexCharts.exec(..., 'dataURI')).
 */
async function loadImageAsDataUrl(uri: string): Promise<{ dataUrl: string; width: number; height: number }> {
    const response = await fetch(uri);
    if (!response.ok) throw new Error(`No se pudo descargar la imagen: ${response.status}`);
    const blob = await response.blob();

    const dataUrl = await new Promise<string>((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result as string);
        reader.onerror = () => reject(reader.error);
        reader.readAsDataURL(blob);
    });

    const { width, height } = await loadImage(dataUrl).then((img) => ({
        width: img.naturalWidth || img.width,
        height: img.naturalHeight || img.height,
    }));

    return { dataUrl, width, height };
}

async function generar() {
    if (generando.value) return;
    generando.value = true;

    try {
        const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const pageW = 210;
        const pageH = 297;
        const margin = 15;
        const contentW = pageW - margin * 2;
        const bottomLimit = pageH - 18; // deja espacio para el pie de página

        // ── Portada / encabezado institucional ──────────────────────────────
        pdf.setFillColor(...PRIMARY);
        pdf.rect(0, 0, pageW, 6, 'F'); // franja superior de marca

        // Logo ULEAM (arriba a la izquierda) + Facultad (arriba a la derecha),
        // igual al membrete institucional usado en los documentos oficiales.
        // Se fija el ALTO (no el ancho) porque el PNG real es mucho más
        // ancho que alto; escalar por ancho fijo dejaba una altura
        // impredecible que a veces invadía la línea/título de abajo. Con
        // alto fijo el resto del encabezado siempre tiene el mismo espacio
        // libre, sin importar la proporción real de la imagen.
        try {
            const { dataUrl, width, height } = await loadImageAsDataUrl(logoUleamUrl);
            const logoH = 17;
            const logoW = Math.min(logoH * (width / height), 65);
            pdf.addImage(dataUrl, 'PNG', margin, 6, logoW, logoH);
        } catch (e) {
            // Si el logo no carga, se continúa sin él (no debe romper la
            // descarga); se deja un aviso en consola para poder depurarlo.
            console.warn('No se pudo cargar el logo institucional para el PDF:', e);
        }

        pdf.setTextColor(...PRIMARY);
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(11);
        pdf.text('Facultad de Ciencias de la Vida y Tecnologías', pageW - margin, 16, { align: 'right' });

        pdf.setDrawColor(...LINE);
        pdf.line(margin, 26, pageW - margin, 26);

        pdf.setTextColor(...TEXT);
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(18);
        pdf.text(props.titulo, margin, 37);

        if (props.subtitulo) {
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(10);
            pdf.setTextColor(...MUTED);
            pdf.text(props.subtitulo, margin, 44);
        }

        const fecha = new Date().toLocaleString('es-EC', { dateStyle: 'long', timeStyle: 'short' });
        pdf.setFontSize(9);
        pdf.setTextColor(...MUTED);
        pdf.text(`Generado el ${fecha}`, margin, props.subtitulo ? 50 : 45);

        pdf.setDrawColor(...LINE);
        pdf.line(margin, 57, pageW - margin, 57);

        let y = 66;

        // ── Indicadores clave ─────────────────────────────────────────────
        if (props.kpis?.length) {
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(12);
            pdf.setTextColor(...TEXT);
            pdf.text('Indicadores clave', margin, y);
            y += 6;

            const cols = 2;
            const gap = 5;
            const boxW = (contentW - gap * (cols - 1)) / cols;
            const boxH = 20;

            props.kpis.forEach((kpi, i) => {
                const col = i % cols;
                const row = Math.floor(i / cols);
                const bx = margin + col * (boxW + gap);
                const by = y + row * (boxH + gap);

                pdf.setDrawColor(...LINE);
                pdf.setFillColor(248, 249, 250);
                pdf.roundedRect(bx, by, boxW, boxH, 2, 2, 'FD');

                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(8);
                pdf.setTextColor(...MUTED);
                pdf.text(String(kpi.label).toUpperCase(), bx + 5, by + 7);

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(16);
                pdf.setTextColor(...PRIMARY);
                pdf.text(String(kpi.value), bx + 5, by + 16);
            });

            const rows = Math.ceil(props.kpis.length / cols);
            y += rows * (boxH + gap) + 6;
        }

        // ── Gráficos estadísticos ─────────────────────────────────────────
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(12);
        pdf.setTextColor(...TEXT);
        pdf.text('Gráficos estadísticos', margin, y);
        y += 8;

        for (const chart of props.charts) {
            // Título del gráfico (asegura espacio, si no salta de página)
            if (y + 12 > bottomLimit) {
                pdf.addPage();
                y = 20;
            }
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(11);
            pdf.setTextColor(...TEXT);
            pdf.text(chart.title, margin, y);
            y += 5;

            // Imagen del gráfico (o aviso si no tiene datos / no está montado)
            let imgURI: string | null = null;
            try {
                const res = await ApexCharts.exec(chart.id, 'dataURI', { scale: 2 });
                imgURI = res?.imgURI ?? null;
            } catch {
                imgURI = null;
            }

            if (!imgURI) {
                pdf.setFont('helvetica', 'italic');
                pdf.setFontSize(9);
                pdf.setTextColor(...MUTED);
                pdf.text('Sin datos suficientes para graficar.', margin, y + 4);
                y += 12;
                continue;
            }

            const img = await loadImage(imgURI);
            const maxW = contentW;
            const maxH = 95;
            const scale = Math.min(maxW / img.width, maxH / img.height);
            const w = img.width * scale;
            const h = img.height * scale;

            if (y + h > bottomLimit) {
                pdf.addPage();
                y = 20;
            }

            const x = margin + (contentW - w) / 2; // centrado
            pdf.addImage(imgURI, 'PNG', x, y, w, h);
            y += h + 8;
        }

        // ── Pie de página con numeración ──────────────────────────────────
        const total = pdf.getNumberOfPages();
        for (let p = 1; p <= total; p++) {
            pdf.setPage(p);
            pdf.setDrawColor(...LINE);
            pdf.line(margin, pageH - 12, pageW - margin, pageH - 12);
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(8);
            pdf.setTextColor(...MUTED);
            pdf.text('Documento descargado del Sistema SISPAA', margin, pageH - 7);
            pdf.text(`Página ${p} de ${total}`, pageW - margin, pageH - 7, { align: 'right' });
        }

        const slug = props.titulo
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '') // quita acentos
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
        pdf.save(`reporte_${slug}_${new Date().toISOString().slice(0, 10)}.pdf`);
    } finally {
        generando.value = false;
    }
}
</script>

<template>
    <Button
        type="button"
        :disabled="generando"
        class="inline-flex items-center gap-1.5 rounded-lg font-semibold text-white shadow-md shadow-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)] transition-all bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] hover:shadow-lg"
        @click="generar"
    >
        <LoaderCircle v-if="generando" class="h-4 w-4 animate-spin" />
        <FileText v-else class="h-4 w-4" />
        {{ generando ? 'Generando…' : 'Descargar reporte (PDF)' }}
    </Button>
</template>
