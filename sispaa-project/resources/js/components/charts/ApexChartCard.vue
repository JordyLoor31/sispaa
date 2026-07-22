<script setup lang="ts">
// Tarjeta reutilizable para gráficos estadísticos (ApexCharts) usada en todo
// el módulo de Reportes. Incluye botón de descarga del gráfico en PNG, JPG
// o PDF, generado a partir del propio canvas del gráfico (sin pedir datos
// nuevos al servidor).
import { computed } from 'vue';
import ApexChart from 'vue3-apexcharts';
import ApexCharts from 'apexcharts';
import jsPDF from 'jspdf';
import type { ApexOptions } from 'apexcharts';
import { Download, BarChart3 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

const props = withDefaults(
    defineProps<{
        chartId: string;
        title: string;
        subtitle?: string;
        type: 'bar' | 'pie' | 'donut' | 'line' | 'area';
        series: unknown;
        options?: ApexOptions;
        height?: number | string;
        empty?: boolean;
    }>(),
    {
        height: 320,
        empty: false,
    },
);

const mergedOptions = computed<ApexOptions>(() => ({
    ...(props.options ?? {}),
    chart: {
        ...(props.options?.chart ?? {}),
        id: props.chartId,
        toolbar: { show: false },
        background: 'transparent',
    },
}));

function triggerDownload(uri: string, filename: string) {
    const a = document.createElement('a');
    a.href = uri;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
}

function loadImage(uri: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = uri;
    });
}

async function toJpgDataUri(pngUri: string): Promise<string> {
    const img = await loadImage(pngUri);
    const canvas = document.createElement('canvas');
    canvas.width = img.width;
    canvas.height = img.height;
    const ctx = canvas.getContext('2d');
    if (!ctx) return pngUri;
    // Fondo blanco: los PNG de ApexCharts son transparentes y un JPG no soporta alpha.
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(img, 0, 0);
    return canvas.toDataURL('image/jpeg', 0.95);
}

const descargar = async (formato: 'png' | 'jpg' | 'pdf') => {
    const { imgURI } = await ApexCharts.exec(props.chartId, 'dataURI', { scale: 2 });
    const nombreBase = props.chartId;

    if (formato === 'png') {
        triggerDownload(imgURI, `${nombreBase}.png`);
        return;
    }

    if (formato === 'jpg') {
        const jpgUri = await toJpgDataUri(imgURI);
        triggerDownload(jpgUri, `${nombreBase}.jpg`);
        return;
    }

    // PDF: incrusta la imagen del gráfico en una página del tamaño exacto de la imagen.
    const img = await loadImage(imgURI);
    const orientation = img.width >= img.height ? 'landscape' : 'portrait';
    const pdf = new jsPDF({ orientation, unit: 'px', format: [img.width, img.height] });
    pdf.addImage(imgURI, 'PNG', 0, 0, img.width, img.height);
    pdf.save(`${nombreBase}.pdf`);
};
</script>

<template>
    <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-[var(--sispaa-text)]">{{ title }}</h3>
                <p v-if="subtitle" class="mt-0.5 text-xs opacity-60 text-[var(--sispaa-text)]">{{ subtitle }}</p>
            </div>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" size="sm" :disabled="empty" class="inline-flex items-center gap-1.5 shrink-0">
                        <Download class="h-3.5 w-3.5" /> Descargar
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="descargar('png')">Imagen PNG</DropdownMenuItem>
                    <DropdownMenuItem @click="descargar('jpg')">Imagen JPG</DropdownMenuItem>
                    <DropdownMenuItem @click="descargar('pdf')">Documento PDF</DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <div v-if="empty" class="flex flex-col items-center justify-center py-16 text-center">
            <BarChart3 class="mb-2 h-10 w-10 opacity-30 text-[var(--sispaa-text)]" />
            <p class="text-sm opacity-50 text-[var(--sispaa-text)]">No hay datos suficientes para graficar.</p>
        </div>
        <ApexChart v-else :type="type" :series="series" :options="mergedOptions" :height="height" />
    </div>
</template>
